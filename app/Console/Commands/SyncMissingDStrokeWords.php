<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;
use App\Services\FirestoreService;

class SyncMissingDStrokeWords extends Command
{
    protected $signature = 'firestore:sync-missing-d-stroke
                            {--execute : Thực sự ghi dữ liệu vào Firestore}';

    protected $description = 'Đồng bộ 35 từ còn thiếu vào Firestore';

    public function handle(FirestoreService $firestore): int
    {
        $manifest = storage_path(
            'app/audit_reports/35_upload_manifest.csv'
        );

        if (!is_file($manifest)) {
            $this->error("Không tìm thấy manifest: {$manifest}");
            return self::FAILURE;
        }

        $handle = fopen($manifest, 'r');

        if ($handle === false) {
            $this->error('Không thể đọc manifest.');
            return self::FAILURE;
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            $this->error('Manifest không có tiêu đề.');
            return self::FAILURE;
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                $this->error('Một dòng CSV có số cột không hợp lệ.');
                return self::FAILURE;
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        if (count($rows) !== 35) {
            $this->error(
                'Manifest phải có đúng 35 dòng. Hiện tại: ' . count($rows)
            );
            return self::FAILURE;
        }

        // Đọc dữ liệu Firestore đã xuất trước đó để đối chiếu tại máy.
        $auditPath = storage_path('app/firestore_words_audit.json');

        if (!is_file($auditPath)) {
            $this->error("Không tìm thấy file audit: {$auditPath}");
            return self::FAILURE;
        }

        $auditData = json_decode(
            file_get_contents($auditPath),
            true
        );

        if (!is_array($auditData)) {
            $this->error('File audit JSON không hợp lệ.');
            return self::FAILURE;
        }

        $existingPairs = [];

        $prepared = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $word = trim($row['word'] ?? '');
            $fileName = trim($row['fileName'] ?? '');
            $localPath = trim($row['local_path'] ?? '');
            $exists = strtolower(trim($row['exists'] ?? ''));

            if (
                $word === '' ||
                $fileName === '' ||
                $exists !== 'true' ||
                basename($fileName) !== $fileName ||
                !is_file($localPath)
            ) {
                $this->error(
                    'Dòng ' . ($index + 2) . ' không hợp lệ: ' . $word
                );

                return self::FAILURE;
            }

            $pairKey = mb_strtolower(
                $word . '|' . $fileName,
                'UTF-8'
            );

            if (isset($seen[$pairKey])) {
                $this->error("Dữ liệu bị trùng trong manifest: {$word}");
                return self::FAILURE;
            }

            $seen[$pairKey] = true;

            $id = 'word_sync_' . substr(
                hash('sha256', $pairKey . '|D_STROKE'),
                0,
                24
            );

            $prepared[] = [
                'id' => $id,
                'word' => $word,
                'letter' => 'Đ',
                'fileName' => $fileName,
                'storagePath' => 'D_STROKE/' . $fileName,
                'already_exists' => isset($existingPairs[$pairKey]),
            ];
        }

        $this->info('===== KIỂM TRA 35 TỪ =====');

        $newCount = 0;
        $skipCount = 0;

        foreach ($prepared as $item) {
            if ($item['already_exists']) {
                $this->line(
                    "SKIP: {$item['word']} - {$item['fileName']} (đã có)"
                );
                $skipCount++;
            } else {
                $this->line(
                    "NEW: {$item['word']} - {$item['fileName']} - {$item['id']}"
                );
                $newCount++;
            }
        }

        $this->newLine();

        $this->info("Tổng số dòng: " . count($prepared));
        $this->info("Đã có: {$skipCount}");
        $this->info("Chưa có: {$newCount}");

        if (!$this->option('execute')) {
            $this->warn('CHẾ ĐỘ KIỂM TRA: Chưa ghi dữ liệu vào Firestore.');
            $this->info(
                'Nếu kết quả chính xác, chạy lại với tùy chọn --execute.'
            );

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($prepared as $item) {
            if ($item['already_exists']) {
                continue;
            }

            try {
                $firestore->createWord(
                    $item['id'],
                    $item['word'],
                    $item['letter'],
                    $item['fileName'],
                    $item['storagePath']
                );

                $this->info(
                    "CREATED: {$item['word']} - {$item['fileName']}"
                );

                $success++;
            } catch (Throwable $e) {
                $this->error(
                    "FAILED: {$item['word']} - {$e->getMessage()}"
                );

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Tạo thành công: {$success}");
        $this->info("Bỏ qua: {$skipCount}");
        $this->error("Thất bại: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
