<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class AuditUploaded545Firestore extends Command
{
    protected $signature = 'firestore:audit-uploaded-545';

    protected $description = 'Audit 545 uploaded WAV paths against Firestore without modifying data';

    public function handle(FirestoreService $firestore): int
    {
        $reportDir = storage_path('app/audit_reports');

        $inputFile = $reportDir . '/missing_upload_results.csv';

        if (!file_exists($inputFile)) {
            $this->error("Không tìm thấy file: {$inputFile}");
            return self::FAILURE;
        }

        $handle = fopen($inputFile, 'r');

        if ($handle === false) {
            $this->error('Không thể đọc file CSV.');
            return self::FAILURE;
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            $this->error('CSV không có tiêu đề.');
            return self::FAILURE;
        }

        $headers = array_map(
            fn ($header) => preg_replace('/^\xEF\xBB\xBF/', '', trim($header)),
            $headers
        );

        $pathIndex = array_search('storagePath', $headers, true);
        $statusIndex = array_search('status', $headers, true);

        if ($pathIndex === false || $statusIndex === false) {
            fclose($handle);
            $this->error('CSV thiếu cột storagePath hoặc status.');
            return self::FAILURE;
        }

        $paths = [];

        while (($row = fgetcsv($handle)) !== false) {
            $path = trim($row[$pathIndex] ?? '');
            $status = trim($row[$statusIndex] ?? '');

            if ($path !== '' && $status === 'UPLOADED') {
                $paths[] = $path;
            }
        }

        fclose($handle);

        $paths = array_values(array_unique($paths));

        $this->info('Tổng đường dẫn UPLOADED duy nhất: ' . count($paths));

        if (empty($paths)) {
            $this->warn('Không có đường dẫn nào để kiểm tra.');
            return self::SUCCESS;
        }

        $this->info('Đang truy vấn Firestore...');

        try {
            $found = $firestore->getWordsByStoragePaths($paths);
        } catch (Throwable $e) {
            $this->error('Truy vấn Firestore thất bại: ' . $e->getMessage());
            return self::FAILURE;
        }

        $rows = [];

        foreach ($paths as $path) {
            $documents = $found[$path] ?? [];

            if (count($documents) === 0) {
                $rows[] = [
                    'storagePath' => $path,
                    'status' => 'NOT_FOUND',
                    'documentCount' => 0,
                    'documentIds' => '',
                    'words' => '',
                    'fileNames' => '',
                ];

                continue;
            }

            $rows[] = [
                'storagePath' => $path,
                'status' => count($documents) > 1 ? 'DUPLICATE' : 'FOUND',
                'documentCount' => count($documents),
                'documentIds' => implode('|', array_column($documents, 'id')),
                'words' => implode('|', array_column($documents, 'word')),
                'fileNames' => implode('|', array_column($documents, 'fileName')),
            ];
        }

        $outputFile = $reportDir . '/uploaded_545_live_firestore_audit.csv';

        $output = fopen($outputFile, 'w');

        if ($output === false) {
            $this->error('Không thể tạo file báo cáo.');
            return self::FAILURE;
        }

        // UTF-8 BOM để Excel hiển thị tiếng Việt đúng.
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'storagePath',
            'status',
            'documentCount',
            'documentIds',
            'words',
            'fileNames',
        ]);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);

        $counts = collect($rows)->countBy('status');

        $this->newLine();
        $this->info('===== LIVE FIRESTORE AUDIT =====');
        $this->line('Tổng đường dẫn: ' . count($rows));
        $this->line('FOUND: ' . ($counts['FOUND'] ?? 0));
        $this->line('NOT_FOUND: ' . ($counts['NOT_FOUND'] ?? 0));
        $this->line('DUPLICATE: ' . ($counts['DUPLICATE'] ?? 0));

        $this->info("Báo cáo: {$outputFile}");

        return self::SUCCESS;
    }
}
