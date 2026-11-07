<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class Audit85FirestoreWords extends Command
{
    protected $signature = 'firestore:audit-85';

    protected $description = 'Verify 85 expected word documents without modifying Firestore';

    public function handle(FirestoreService $firestore): int
    {
        $reportDir = storage_path('app/audit_reports');

        $file50 = $reportDir . '/50_upload_results.csv';
        $file35 = $reportDir . '/d_stroke_sync_completed.csv';

        $output = $reportDir . '/firestore_85_verification.csv';

        try {
            if (!is_file($file50) || !is_file($file35)) {
                throw new RuntimeException('Missing input CSV files.');
            }

            $rows50 = $this->readCsv($file50);
            $rows35 = $this->readCsv($file35);

            if (count($rows50) !== 50 || count($rows35) !== 35) {
                throw new RuntimeException(
                    'Expected 50 and 35 rows. Found: '
                    . count($rows50) . ' and ' . count($rows35)
                );
            }

            $targets = [];

            // Prepare 50 documents
            foreach ($rows50 as $row) {
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
                    $parts[1] !== $fileName
                ) {
                    throw new RuntimeException(
                        "Invalid 50-word manifest row: {$word}"
                    );
                }

                $letter = $parts[0];

                // Same ID formula as Sync50MissingWords
                $pairKey = mb_strtolower(
                    $word . '|' . $fileName,
                    'UTF-8'
                );

                $id = 'word_sync_' . substr(
                    hash('sha256', $pairKey . '|' . $letter),
                    0,
                    24
                );

                $targets[] = [
                    'id' => $id,
                    'word' => $word,
                    'letter' => $letter,
                    'fileName' => $fileName,
                    'storagePath' => $storagePath,
                ];
            }

            // Prepare 35 D-stroke documents
            foreach ($rows35 as $row) {
                $targets[] = [
                    'id' => trim($row['documentId'] ?? ''),
                    'word' => trim($row['word'] ?? ''),
                    'letter' => trim($row['letter'] ?? ''),
                    'fileName' => trim($row['fileName'] ?? ''),
                    'storagePath' => trim($row['storagePath'] ?? ''),
                ];
            }

            if (count($targets) !== 85) {
                throw new RuntimeException('Expected exactly 85 targets.');
            }

            $ids = array_column($targets, 'id');

            if (
                in_array('', $ids, true) ||
                count(array_unique($ids)) !== 85
            ) {
                throw new RuntimeException(
                    'Empty or duplicate document IDs detected.'
                );
            }

            $this->info('===== FIRESTORE AUDIT: 85 DOCUMENTS =====');
            $this->warn('READ ONLY - No documents will be created or modified.');
            $this->newLine();

            $handle = fopen($output, 'w');

            if ($handle === false) {
                throw new RuntimeException('Cannot create output CSV.');
            }

            fputcsv($handle, [
                'documentId',
                'expected_word',
                'expected_letter',
                'expected_fileName',
                'expected_storagePath',
                'status',
                'actual_word',
                'actual_letter',
                'actual_fileName',
                'actual_storagePath',
                'error',
            ]);

            $counts = [
                'FOUND_MATCH' => 0,
                'NOT_FOUND' => 0,
                'MISMATCH' => 0,
                'ERROR' => 0,
            ];

            foreach ($targets as $index => $item) {
                $status = '';
                $actual = [
                    'word' => '',
                    'letter' => '',
                    'fileName' => '',
                    'storagePath' => '',
                ];
                $error = '';

                try {
                    $doc = $firestore->getDocumentOrNull(
                        'words',
                        $item['id']
                    );

                    if ($doc === null) {
                        $status = 'NOT_FOUND';
                    } else {
                        $fields = $doc['fields'] ?? [];

                        foreach ($actual as $key => $value) {
                            $actual[$key] =
                                $fields[$key]['stringValue'] ?? '';
                        }

                        $status = 'FOUND_MATCH';

                        foreach ($actual as $key => $value) {
                            if ($value !== $item[$key]) {
                                $status = 'MISMATCH';
                                break;
                            }
                        }
                    }
                } catch (Throwable $e) {
                    $status = 'ERROR';
                    $error = $e->getMessage();
                }

                $counts[$status]++;

                fputcsv($handle, [
                    $item['id'],
                    $item['word'],
                    $item['letter'],
                    $item['fileName'],
                    $item['storagePath'],
                    $status,
                    $actual['word'],
                    $actual['letter'],
                    $actual['fileName'],
                    $actual['storagePath'],
                    $error,
                ]);

                $this->line(
                    ($index + 1) . '/85: '
                    . $item['word'] . ' - ' . $status
                );
            }

            fclose($handle);

            $this->newLine();
            $this->info('===== AUDIT SUMMARY =====');

            foreach ($counts as $status => $count) {
                $this->line("{$status}: {$count}");
            }

            $this->info("Report saved: {$output}");

            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Cannot open CSV: {$path}");
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("Invalid CSV: {$path}");
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw new RuntimeException("Invalid CSV row: {$path}");
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        return $rows;
    }
}
