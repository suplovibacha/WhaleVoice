<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirestoreService;
use Throwable;

class Sync50MissingWords extends Command
{
    protected $signature = 'firestore:sync-50-missing
                            {--execute : Ghi du lieu vao Firestore}';

    protected $description = 'Dong bo 50 tu con thieu vao Firestore';

    public function handle(FirestoreService $firestore): int
    {
        $manifest = storage_path(
            'app/audit_reports/50_upload_results.csv'
        );

        $auditPath = storage_path(
            'app/firestore_words_audit.json'
        );

        if (!is_file($manifest) || !is_file($auditPath)) {
            $this->error('Khong tim thay file manifest hoac audit.');
            return self::FAILURE;
        }

        // Doc bao cao upload
        $handle = fopen($manifest, 'r');
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            $this->error('Manifest khong hop le.');
            return self::FAILURE;
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                $this->error('CSV co dong khong hop le.');
                return self::FAILURE;
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        if (count($rows) !== 50) {
            $this->error(
                'Can dung 50 dong. Hien tai: ' . count($rows)
            );
            return self::FAILURE;
        }

        // Doc audit Firestore da co tren may
        $audit = json_decode(
            file_get_contents($auditPath),
            true
        );

        if (!is_array($audit)) {
            $this->error('Audit JSON khong hop le.');
            return self::FAILURE;
        }

        $existingPairs = [];
        $existingWords = [];

        foreach ($audit as $doc) {
            $word = mb_strtolower(trim($doc['word'] ?? ''), 'UTF-8');
            $fileName = strtolower(trim($doc['fileName'] ?? ''));

            if ($word !== '') {
                $existingWords[$word] = true;
            }

            if ($word !== '' && $fileName !== '') {
                $existingPairs[$word . '|' . $fileName] = true;
            }
        }

        $prepared = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $word = trim($row['word'] ?? '');
            $fileName = trim($row['fileName'] ?? '');
            $storagePath = trim($row['storagePath'] ?? '');
            $status = strtoupper(trim($row['status'] ?? ''));

            $parts = explode('/', $storagePath);

            if (
                $word === '' ||
                $fileName === '' ||
                $status !== 'UPLOAD_OK' ||
                count($parts) !== 2 ||
                basename($fileName) !== $fileName ||
                $parts[1] !== $fileName ||
                !in_array($parts[0], ['C', 'K', 'M', 'O', 'V'], true)
            ) {
                $this->error(
                    'Dong ' . ($index + 2) . ' khong hop le.'
                );
                return self::FAILURE;
            }

            $letter = $parts[0];

            $pairKey = mb_strtolower(
                $word . '|' . $fileName,
                'UTF-8'
            );

            if (isset($seen[$pairKey])) {
                $this->error("Trung trong manifest: {$word}");
                return self::FAILURE;
            }

            $seen[$pairKey] = true;

            // Neu tu da co nhung gan voi file khac thi dung lai
            $normalizedWord = mb_strtolower($word, 'UTF-8');

            if (
                isset($existingWords[$normalizedWord]) &&
                !isset($existingPairs[$pairKey])
            ) {
                $this->error(
                    "Tu da ton tai voi file khac: {$word}"
                );
                return self::FAILURE;
            }

            $id = 'word_sync_' . substr(
                hash(
                    'sha256',
                    $pairKey . '|' . $letter
                ),
                0,
                24
            );

            $prepared[] = [
                'id' => $id,
                'word' => $word,
                'letter' => $letter,
                'fileName' => $fileName,
                'storagePath' => $storagePath,
                'already_exists' => isset($existingPairs[$pairKey]),
            ];
        }

        $this->info('===== KIEM TRA 50 TU =====');

        $newCount = 0;
        $skipCount = 0;

        foreach ($prepared as $item) {
            if ($item['already_exists']) {
                $this->line(
                    "SKIP: {$item['word']} - {$item['fileName']}"
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
        $this->info('Tong so: ' . count($prepared));
        $this->info("Da co: {$skipCount}");
        $this->info("Can tao: {$newCount}");

        if (!$this->option('execute')) {
            $this->warn(
                'CHE DO KIEM TRA: Chua ghi du lieu vao Firestore.'
            );

            $this->info(
                'Neu ket qua chinh xac, chay lai voi --execute.'
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
        $this->info("Tao thanh cong: {$success}");
        $this->info("Bo qua: {$skipCount}");
        $this->error("That bai: {$failed}");

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}