<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseStorageService;
use RuntimeException;
use Throwable;

class Upload50MissingWavs extends Command
{
    protected $signature = 'supabase:upload-50-missing';

    protected $description = 'Upload 50 WAV files missing from Supabase';

    public function handle(SupabaseStorageService $supabase): int
    {
        $manifest = storage_path(
            'app/audit_reports/50_source_recheck.csv'
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

        $required = [
            'word',
            'fileName',
            'folder',
            'source_path',
            'status',
        ];

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

        if (count($rows) !== 50) {
            $this->error(
                'Dừng upload: cần đúng 50 dòng, hiện có '
                . count($rows)
            );

            return self::FAILURE;
        }

        $this->info('Đã đọc đủ 50 dòng manifest.');

        $success = 0;
        $failed = 0;
        $report = [];

        foreach ($rows as $row) {
            $word = trim($row['word']);
            $fileName = trim($row['fileName']);
            $folder = strtoupper(trim($row['folder']));
            $localPath = trim($row['source_path']);

            if (
                strtoupper(trim($row['status'])) !== 'FOUND'
                || basename($fileName) !== $fileName
                || !in_array($folder, ['C', 'K', 'M', 'O', 'V'], true)
                || !is_file($localPath)
            ) {
                $this->error("Bỏ qua: {$word} - {$fileName}");
                $failed++;

                $report[] = [
                    'word' => $word,
                    'fileName' => $fileName,
                    'storagePath' => $folder . '/' . $fileName,
                    'status' => 'LOCAL_FILE_INVALID',
                ];

                continue;
            }

            $storagePath = $folder . '/' . $fileName;

            try {
                $result = $supabase->uploadFile(
                    $localPath,
                    $storagePath
                );

                if ($result['success']) {
                    $this->info("UPLOAD OK: {$word} - {$fileName}");
                    $success++;
                    $status = 'UPLOAD_OK';
                } else {
                    $this->error(
                        "UPLOAD FAILED: {$fileName} | HTTP "
                        . $result['status']
                        . ' | '
                        . $result['body']
                    );

                    $failed++;
                    $status = 'UPLOAD_FAILED_' . $result['status'];
                }
            } catch (Throwable $e) {
                $this->error(
                    "ERROR: {$fileName} | " . $e->getMessage()
                );

                $failed++;
                $status = 'ERROR';
            }

            $report[] = [
                'word' => $word,
                'fileName' => $fileName,
                'storagePath' => $storagePath,
                'status' => $status,
            ];
        }

        $reportPath = storage_path(
            'app/audit_reports/50_upload_results.csv'
        );

        $output = fopen($reportPath, 'w');

        if ($output !== false) {
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'word',
                'fileName',
                'storagePath',
                'status',
            ]);

            foreach ($report as $item) {
                fputcsv($output, $item);
            }

            fclose($output);
        }

        $this->newLine();
        $this->info("Upload thành công: {$success}");
        $this->info("Upload thất bại: {$failed}");
        $this->info("Báo cáo: {$reportPath}");

        return $failed === 0
            ? self::SUCCESS
            : self::FAILURE;
    }
}