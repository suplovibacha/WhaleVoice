<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseStorageService;
use RuntimeException;
use Throwable;

class UploadMissingDStrokeWavs extends Command
{
    protected $signature = 'supabase:upload-d-stroke';

    protected $description = 'Upload 35 missing D_STROKE WAV files';

    public function handle(SupabaseStorageService $supabase): int
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

        // Loại bỏ BOM nếu file CSV có BOM.
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $required = ['word', 'fileName', 'local_path', 'exists'];

        if (array_diff($required, $headers)) {
            fclose($handle);
            $this->error('Manifest thiếu cột bắt buộc.');
            return self::FAILURE;
        }

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        if (count($rows) !== 35) {
            $this->error(
                'Dừng upload: manifest phải có đúng 35 dòng, hiện có '
                . count($rows)
            );

            return self::FAILURE;
        }

        $this->info('Đã đọc 35 dòng manifest.');
        $this->info('Bắt đầu upload vào D_STROKE/...');

        $success = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $word = $row['word'];
            $fileName = $row['fileName'];
            $localPath = $row['local_path'];

            if (
                strtolower(trim($row['exists'])) !== 'true'
                || basename($fileName) !== $fileName
                || !is_file($localPath)
            ) {
                $this->error("Bỏ qua: {$word} - {$fileName}");
                $failed++;
                continue;
            }

            $storagePath = 'D_STROKE/' . $fileName;

            try {
                $result = $supabase->uploadFile(
                    $localPath,
                    $storagePath
                );

                if ($result['success']) {
                    $this->info("UPLOAD OK: {$word} - {$fileName}");
                    $success++;
                } else {
                    $this->error(
                        "UPLOAD FAILED: {$fileName} | HTTP "
                        . $result['status']
                        . ' | '
                        . $result['body']
                    );

                    $failed++;
                }
            } catch (Throwable $e) {
                $this->error(
                    "ERROR: {$fileName} | " . $e->getMessage()
                );

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Upload thành công: {$success}");
        $this->info("Upload thất bại: {$failed}");

        return $failed === 0
            ? self::SUCCESS
            : self::FAILURE;
    }
}
