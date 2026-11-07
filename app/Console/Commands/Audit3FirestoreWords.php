<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use Throwable;

class Audit3FirestoreWords extends Command
{
    protected $signature = 'firestore:audit-3';

    protected $description = 'Retry verification of 3 Firestore documents';

    public function handle(FirestoreService $firestore): int
    {
        $targets = [
            [
                'id' => 'word_sync_4c364ff123efd10dcf4ae4fa',
                'word' => 'váy',
                'storagePath' => 'V/vay1.wav',
            ],
            [
                'id' => 'word_sync_f33134b4466019333780e7c4',
                'word' => 'viết',
                'storagePath' => 'V/vie+t1.wav',
            ],
            [
                'id' => 'word_sync_cdaea6dabbd7cbc15d0ca0dd',
                'word' => 'vạch',
                'storagePath' => 'V/vach5.wav',
            ],
        ];

        $this->info('===== AUDIT 3 FIRESTORE DOCUMENTS =====');
        $this->warn('READ ONLY - No Firestore data will be modified.');

        foreach ($targets as $index => $item) {
            $this->newLine();

            try {
                $doc = $firestore->getDocumentOrNull(
                    'words',
                    $item['id']
                );

                if ($doc === null) {
                    $this->warn(
                        ($index + 1) . '/3: '
                        . $item['word'] . ' - NOT_FOUND'
                    );
                    continue;
                }

                $fields = $doc['fields'] ?? [];

                $actualWord = $fields['word']['stringValue'] ?? '';
                $actualPath = $fields['storagePath']['stringValue'] ?? '';

                if (
                    $actualWord === $item['word'] &&
                    $actualPath === $item['storagePath']
                ) {
                    $this->info(
                        ($index + 1) . '/3: '
                        . $item['word'] . ' - FOUND_MATCH'
                    );
                } else {
                    $this->error(
                        ($index + 1) . '/3: '
                        . $item['word'] . ' - MISMATCH'
                    );

                    $this->line("Actual word: {$actualWord}");
                    $this->line("Actual path: {$actualPath}");
                }
            } catch (Throwable $e) {
                $this->error(
                    ($index + 1) . '/3: '
                    . $item['word'] . ' - ERROR: '
                    . $e->getMessage()
                );
            }
        }

        return self::SUCCESS;
    }
}