<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class SyncUploaded545Metadata extends Command
{
    protected $signature = 'firestore:sync-uploaded545-metadata
                            {--execute : Ghi dữ liệu thật vào Firestore}';

    protected $description = 'Đồng bộ metadata 545 file WAV đã upload';

    private function readCsv(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("Không tìm thấy: {$path}");
        }

        $handle = fopen($path, 'r');

        if (!$handle) {
            throw new RuntimeException("Không đọc được: {$path}");
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("CSV rỗng: {$path}");
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        return $rows;
    }

    private function key(string $word): string
    {
        return mb_strtolower(trim($word), 'UTF-8');
    }

    public function handle(FirestoreService $firestore): int
    {
        try {
            $reportDir = storage_path('app/audit_reports');

            $uploaded = $this->readCsv(
                "{$reportDir}/missing_upload_results.csv"
            );

            $excel = $this->readCsv(
                "{$reportDir}/excel_unique_pairs.csv"
            );

            /*
             * Ánh xạ các file ghi âm bổ sung đã xác định.
             */
            $overrides = [
                'anh5-2.wav' => ['ạnh', 'A'],
                'cach5-2.wav' => ['cạch', 'C'],
                'chay5-2.wav' => ['chạy', 'C'],
                'che+4-2.wav' => ['chễ', 'C'],
                'che+5-2.wav' => ['chệ', 'C'],
                'che+nh0-2.wav' => ['chênh', 'C'],
                'che+nh2-2.wav' => ['chềnh', 'C'],
                'che+nh3-2.wav' => ['chểnh', 'C'],
                'chinh1-2.wav' => ['chính', 'C'],
                'cong5-2.wav' => ['cọng', 'C'],
                'coon0-2.wav' => ['cơn', 'C'],
                'nga0-2.wav' => ['nha', 'N'],
                'qua+n5-2.wav' => ['quận', 'Q'],
                'qua+t5-2.wav' => ['quật', 'Q'],
                'quaan2-2.wav' => ['quằn', 'Q'],
                'quach1-2.wav' => ['quách', 'Q'],
                'quai1-2.wav' => ['quái', 'Q'],
                'quan0-2.wav' => ['quan', 'Q'],
                'quan1-2.wav' => ['quán', 'Q'],
                'que+t1-2.wav' => ['quết', 'Q'],
                'quye+n1-2.wav' => ['quyến', 'Q'],
                'quye+t1-2.wav' => ['quyết', 'Q'],
                'quynh1-2.wav' => ['quýnh', 'Q'],
                'quyt1-2.wav' => ['quýt', 'Q'],
                'un0-2.wav' => ['un', 'U'],
                'un2-2.wav' => ['ùn', 'U'],
                'un3-2.wav' => ['ủn', 'U'],
                'uo+n1-2.wav' => ['uốn', 'U'],
                'uo+ng1-2.wav' => ['uống', 'U'],

                // Đã xác nhận bằng kiểm tra âm thanh.
                'nga0.wav' => ['nga', 'N'],
            ];

            /*
             * Nhóm Excel theo tên file.
             * Chỉ nhận filename có đúng một từ tương ứng.
             */
            $excelByFile = [];

            foreach ($excel as $row) {
                $file = trim($row['fileName'] ?? '');
                $word = trim($row['word'] ?? '');
                $letter = trim($row['sheet'] ?? '');

                if ($file === '' || $word === '') {
                    continue;
                }

                $excelByFile[$file][] = [
                    'word' => $word,
                    'letter' => $letter,
                ];
            }

            $prepared = [];
            $skipped = [];

            foreach ($uploaded as $row) {
                if (strtoupper(trim($row['status'] ?? '')) !== 'UPLOADED') {
                    continue;
                }

                $file = trim($row['fileName'] ?? '');
                $path = trim($row['storagePath'] ?? '');
                $folder = trim($row['local_folder'] ?? '');

                if ($file === '' || $path === '') {
                    continue;
                }

                $lookupAliases = [
                    'com2_.wav' => 'com2.wav',
                    'com4_.wav' => 'com4.wav',
                    'suo+ng0 .wav' => 'suo+ng0.wav',
                    'd-it5x.wav' => 'd-it5.wav',
                ];

                if (isset($overrides[$file])) {
                    [$word, $letter] = $overrides[$file];

                    $matches = [[
                        'word' => $word,
                        'letter' => $letter,
                    ]];
                } else {
                    $lookupFile = $lookupAliases[$file] ?? $file;
                    $matches = $excelByFile[$lookupFile] ?? [];
                }

                if (count($matches) === 0) {
                    $skipped[] = [
                        'fileName' => $file,
                        'storagePath' => $path,
                        'reason' => 'NO_EXACT_MATCH',
                    ];

                    continue;
                }

                foreach ($matches as $match) {
                    $prepared[] = [
                        'word' => $match['word'],
                        'letter' => $match['letter'],
                        'fileName' => $file,
                        'storagePath' => $path,
                    ];
                }

                $prepared[] = [
                    'word' => $word,
                    'letter' => $letter,
                    'fileName' => $file,
                    'storagePath' => $path,
                ];
            }

            /*
             * Loại bỏ đường dẫn trùng.
             */
            $unique = [];

            foreach ($prepared as $item) {
                $key = mb_strtolower($item['word']) . '|' . $item['storagePath'];
                $unique[$key] = $item;
            }

            $prepared = array_values($unique);

            /*
             * Nhóm các file theo từ.
             */
            $groups = [];

            foreach ($prepared as $item) {
                $key = $this->key($item['word']);

                $groups[$key]['word'] = $item['word'];
                $groups[$key]['letter'] = $item['letter'];
                $groups[$key]['audioFiles'][] = [
                    'fileName' => $item['fileName'],
                    'storagePath' => $item['storagePath'],
                ];
            }

            /*
             * Lấy danh sách document Firestore hiện tại.
             */
            $existingWords = $firestore->getAllWords();

            $existingByWord = [];

            foreach ($existingWords as $item) {
                $key = $this->key($item['word']);

                $existingByWord[$key][] = $item;
            }

            $operations = [];
            $report = [];

            foreach ($groups as $key => $group) {
                $matches = $existingByWord[$key] ?? [];

                if (count($matches) > 1) {
                    $skipped[] = [
                        'fileName' => '',
                        'storagePath' => '',
                        'reason' => 'DUPLICATE_WORD_DOCUMENT: '
                            . $group['word'],
                    ];

                    continue;
                }

                if (count($matches) === 1) {
                    $document = $firestore->getDocument(
                        'words',
                        $matches[0]['id']
                    );

                    $audioFiles = $firestore->getWordAudioFiles(
                        $document
                    );

                    /*
                     * Giữ lại file cũ nếu document chưa có audioFiles.
                     */
                    if (empty($audioFiles)) {
                        $oldFile = $matches[0]['fileName'] ?? '';
                        $oldPath = $matches[0]['storagePath'] ?? '';

                        if ($oldFile !== '' && $oldPath !== '') {
                            $audioFiles[] = [
                                'fileName' => $oldFile,
                                'storagePath' => $oldPath,
                            ];
                        }
                    }

                    $knownPaths = array_column(
                        $audioFiles,
                        'storagePath'
                    );

                    $added = [];

                    foreach ($group['audioFiles'] as $audio) {
                        if (!in_array(
                            $audio['storagePath'],
                            $knownPaths,
                            true
                        )) {
                            $audioFiles[] = $audio;
                            $added[] = $audio;
                        }
                    }

                    if (empty($added)) {
                        $report[] = [
                            'word' => $group['word'],
                            'action' => 'ALREADY_EXISTS',
                            'count' => 0,
                        ];

                        continue;
                    }

                    $operations[] = [
                        'type' => 'UPDATE',
                        'id' => $matches[0]['id'],
                        'word' => $group['word'],
                        'audioFiles' => $audioFiles,
                    ];

                    $report[] = [
                        'word' => $group['word'],
                        'action' => 'UPDATE',
                        'count' => count($added),
                    ];
                } else {
                    $id = 'word_sync_' . substr(
                        hash('sha256', $key),
                        0,
                        24
                    );

                    $operations[] = [
                        'type' => 'CREATE',
                        'id' => $id,
                        'word' => $group['word'],
                        'letter' => $group['letter'],
                        'audioFiles' => $group['audioFiles'],
                    ];

                    $report[] = [
                        'word' => $group['word'],
                        'action' => 'CREATE',
                        'count' => count($group['audioFiles']),
                    ];
                }
            }

            /*
             * Xuất báo cáo.
             */
            $reportPath = "{$reportDir}/uploaded545_metadata_preview.csv";

            $handle = fopen($reportPath, 'w');

            fputcsv($handle, ['word', 'action', 'audio_count']);

            foreach ($report as $row) {
                fputcsv($handle, [
                    $row['word'],
                    $row['action'],
                    $row['count'],
                ]);
            }

            fclose($handle);

            $skipPath = "{$reportDir}/uploaded545_metadata_skipped.csv";

            $handle = fopen($skipPath, 'w');

            fputcsv($handle, [
                'fileName',
                'storagePath',
                'reason',
            ]);

            foreach ($skipped as $row) {
                fputcsv($handle, [
                    $row['fileName'],
                    $row['storagePath'],
                    $row['reason'],
                ]);
            }

            fclose($handle);

            $createCount = count(array_filter(
                $operations,
                fn($item) => $item['type'] === 'CREATE'
            ));

            $updateCount = count(array_filter(
                $operations,
                fn($item) => $item['type'] === 'UPDATE'
            ));

            $this->info('===== KẾT QUẢ KIỂM TRA =====');
            $this->info('File metadata hợp lệ: ' . count($prepared));
            $this->info('Nhóm từ: ' . count($groups));
            $this->info("Document cần tạo: {$createCount}");
            $this->info("Document cần cập nhật: {$updateCount}");
            $this->info('Trường hợp bỏ qua: ' . count($skipped));
            $this->info("Báo cáo: {$reportPath}");
            $this->info("Báo cáo bỏ qua: {$skipPath}");

            if (!$this->option('execute')) {
                $this->warn(
                    'DRY RUN: Chưa ghi bất kỳ dữ liệu nào vào Firestore.'
                );

                return self::SUCCESS;
            }

            $success = 0;
            $failed = 0;

            foreach ($operations as $operation) {
                try {
                    if ($operation['type'] === 'UPDATE') {
                        $firestore->updateWordAudioFiles(
                            $operation['id'],
                            $operation['audioFiles']
                        );
                    } else {
                        $firestore->createWordWithAudioFiles(
                            $operation['id'],
                            $operation['word'],
                            $operation['letter'],
                            $operation['audioFiles']
                        );
                    }

                    $this->info(
                        $operation['type'] . ': '
                            . $operation['word']
                    );

                    $success++;
                } catch (Throwable $e) {
                    $this->error(
                        $operation['word'] . ': ' . $e->getMessage()
                    );

                    $failed++;
                }
            }

            Cache::forget('firestore.words.all');

            $this->info("Thành công: {$success}");
            $this->error("Thất bại: {$failed}");

            return $failed > 0
                ? self::FAILURE
                : self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
