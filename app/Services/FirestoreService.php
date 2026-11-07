<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class FirestoreService
{
    private string $projectId;
    private string $credentialsPath;
    private string $baseUrl;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->credentialsPath = config('services.firebase.credentials');

        if (!$this->projectId || !$this->credentialsPath) {
            throw new RuntimeException('Firebase configuration is missing.');
        }

        if (!file_exists($this->credentialsPath)) {
            throw new RuntimeException('Firebase service account file not found.');
        }

        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    private function getAccessToken(): string
    {
        $jsonContent = file_get_contents($this->credentialsPath);

        if ($jsonContent === false) {
            throw new RuntimeException('Cannot read Firebase credentials file.');
        }

        $jsonKey = json_decode($jsonContent, true);

        if (
            !is_array($jsonKey) ||
            empty($jsonKey['client_email']) ||
            empty($jsonKey['private_key'])
        ) {
            throw new RuntimeException('Firebase credentials JSON is invalid.');
        }

        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/datastore',
            $jsonKey
        );

        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Unable to obtain Firebase access token.');
        }

        return $token['access_token'];
    }

    /**
     * Lấy một document theo ID.
     */
    public function getDocument(string $collection, string $documentId): array
    {
        $url = "{$this->baseUrl}/{$collection}/{$documentId}";

        $response = Http::withToken($this->getAccessToken())
            ->connectTimeout(30)
            ->timeout(60)
            ->retry(
                3,
                1000,
                fn($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException
            )
            ->get($url);
        if (!$response->successful()) {
            throw new RuntimeException(
                'Firestore request failed: ' . $response->body()
            );
        }

        return $response->json();
    }
    public function getDocumentOrNull(string $collection, string $documentId): ?array
    {
        $url = "{$this->baseUrl}/{$collection}/{$documentId}";

        $response = Http::withToken($this->getAccessToken())
            ->connectTimeout(30)
            ->timeout(60)
            ->retry(
                3,
                1000,
                fn($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException
            )
            ->get($url);

        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            throw new RuntimeException(
                'Firestore request failed: ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Lấy toàn bộ từ trong collection và cache trong 1 giờ.
     */
    public function getAllWords(): array
    {
        return Cache::remember(
            'firestore.words.all',
            now()->addHour(),
            function () {
                $accessToken = $this->getAccessToken();

                $url = "{$this->baseUrl}/words";

                $words = [];
                $nextPageToken = null;

                do {
                    $query = [
                        'pageSize' => 1000,
                    ];

                    if ($nextPageToken) {
                        $query['pageToken'] = $nextPageToken;
                    }

                    $response = Http::withToken($accessToken)
                        ->connectTimeout(30)
                        ->timeout(60)
                        ->withOptions([
                            'force_ip_resolve' => 'v4',
                        ])
                        ->get($url, $query);

                    if (!$response->successful()) {
                        throw new RuntimeException(
                            'Firestore list failed: ' . $response->body()
                        );
                    }

                    $data = $response->json();

                    foreach ($data['documents'] ?? [] as $document) {
                        $fields = $document['fields'] ?? [];

                        $words[] = [
                            'id' => basename($document['name']),
                            'word' => $fields['word']['stringValue'] ?? '',
                            'letter' => $fields['letter']['stringValue'] ?? '',
                            'fileName' => $fields['fileName']['stringValue'] ?? '',
                            'storagePath' => $fields['storagePath']['stringValue'] ?? '',
                        ];
                    }

                    $nextPageToken = $data['nextPageToken'] ?? null;
                } while ($nextPageToken);

                return $words;
            }
        );
    }

    /**
     * Truy vấn các document theo danh sách storagePath.
     * Mỗi lần truy vấn tối đa 30 đường dẫn.
     * Kết quả được nhóm theo storagePath để phát hiện trùng.
     */
    public function getWordsByStoragePaths(array $storagePaths): array
    {
        $storagePaths = array_values(array_unique(array_filter(
            $storagePaths,
            fn($path) => is_string($path) && trim($path) !== ''
        )));

        if (empty($storagePaths)) {
            return [];
        }

        // Chỉ lấy access token một lần cho toàn bộ quá trình.
        $accessToken = $this->getAccessToken();

        $url = "{$this->baseUrl}:runQuery";
        $results = [];

        // Firestore IN filter hỗ trợ tối đa 30 giá trị.
        foreach (array_chunk($storagePaths, 30) as $chunk) {
            $values = array_map(
                fn($path) => ['stringValue' => $path],
                $chunk
            );

            $body = [
                'structuredQuery' => [
                    'from' => [
                        ['collectionId' => 'words'],
                    ],
                    'where' => [
                        'fieldFilter' => [
                            'field' => [
                                'fieldPath' => 'storagePath',
                            ],
                            'op' => 'IN',
                            'value' => [
                                'arrayValue' => [
                                    'values' => $values,
                                ],
                            ],
                        ],
                    ],
                    'select' => [
                        'fields' => [
                            ['fieldPath' => 'word'],
                            ['fieldPath' => 'letter'],
                            ['fieldPath' => 'fileName'],
                            ['fieldPath' => 'storagePath'],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->connectTimeout(30)
                ->timeout(60)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Firestore query failed: ' . $response->body()
                );
            }

            foreach ($response->json() as $item) {
                if (empty($item['document'])) {
                    continue;
                }

                $document = $item['document'];
                $fields = $document['fields'] ?? [];

                $storagePath = $fields['storagePath']['stringValue'] ?? '';

                if ($storagePath === '') {
                    continue;
                }

                $results[$storagePath][] = [
                    'id' => basename($document['name']),
                    'word' => $fields['word']['stringValue'] ?? '',
                    'letter' => $fields['letter']['stringValue'] ?? '',
                    'fileName' => $fields['fileName']['stringValue'] ?? '',
                    'storagePath' => $storagePath,
                ];
            }
        }

        return $results;
    }

    /**
     * Lấy các document theo danh sách từ cần chuyển thành âm thanh.
     * Mỗi truy vấn tối đa 30 từ.
     */
    public function getWordsByWords(array $words): array
    {
        $words = array_values(array_unique(array_filter(
            array_map(
                fn($word) => mb_strtolower(trim($word), 'UTF-8'),
                $words
            ),
            fn($word) => $word !== ''
        )));

        if (empty($words)) {
            return [];
        }

        $accessToken = $this->getAccessToken();
        $url = "{$this->baseUrl}:runQuery";

        $results = [];

        foreach (array_chunk($words, 30) as $chunk) {
            $values = array_map(
                fn($word) => ['stringValue' => $word],
                $chunk
            );

            $body = [
                'structuredQuery' => [
                    'from' => [
                        ['collectionId' => 'words'],
                    ],
                    'where' => [
                        'fieldFilter' => [
                            'field' => [
                                'fieldPath' => 'word',
                            ],
                            'op' => 'IN',
                            'value' => [
                                'arrayValue' => [
                                    'values' => $values,
                                ],
                            ],
                        ],
                    ],
                    'select' => [
                        'fields' => [
                            ['fieldPath' => 'word'],
                            ['fieldPath' => 'letter'],
                            ['fieldPath' => 'fileName'],
                            ['fieldPath' => 'storagePath'],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->connectTimeout(30)
                ->timeout(60)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Firestore word query failed: ' . $response->body()
                );
            }

            foreach ($response->json() as $item) {
                if (empty($item['document'])) {
                    continue;
                }

                $document = $item['document'];
                $fields = $document['fields'] ?? [];

                $word = mb_strtolower(
                    trim($fields['word']['stringValue'] ?? ''),
                    'UTF-8'
                );

                if ($word === '') {
                    continue;
                }

                $results[] = [
                    'id' => basename($document['name']),
                    'word' => $word,
                    'letter' => $fields['letter']['stringValue'] ?? '',
                    'fileName' => $fields['fileName']['stringValue'] ?? '',
                    'storagePath' => $fields['storagePath']['stringValue'] ?? '',
                ];
            }
        }

        return $results;
    }


    /**
     * Tạo hồ sơ người dùng trong Firestore.
     */
    public function createUserProfile(
        string $uid,
        string $name,
        string $email
    ): void {
        $url = "{$this->baseUrl}/users/" . rawurlencode($uid);

        $fields = [
            'name' => [
                'stringValue' => $name,
            ],
            'email' => [
                'stringValue' => $email,
            ],
            'role' => [
                'stringValue' => 'user',
            ],
            'status' => [
                'stringValue' => 'active',
            ],
            'createdAt' => [
                'timestampValue' => now()->toISOString(),
            ],
        ];

        $response = Http::withToken($this->getAccessToken())
            ->patch($url, [
                'fields' => $fields,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Cannot create user profile: ' . $response->body()
            );
        }
    }
    /**
     * Tạo document từ mới trong collection words.
     * Không ghi đè document đã tồn tại.
     */
    /**
     * Tạo document từ mới trong Firestore.
     * Không ghi đè document đã tồn tại.
     */
    public function createWord(
        string $id,
        string $word,
        string $letter,
        string $fileName,
        string $storagePath
    ): void {
        $url = "{$this->baseUrl}/words?" . http_build_query([
            'documentId' => $id,
        ]);

        $fields = [
            'word' => ['stringValue' => $word],
            'letter' => ['stringValue' => $letter],
            'fileName' => ['stringValue' => $fileName],
            'storagePath' => ['stringValue' => $storagePath],
        ];

        $response = Http::withToken($this->getAccessToken())
            ->connectTimeout(30)
            ->timeout(60)
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->post($url, [
                'fields' => $fields,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Không thể tạo từ {$word}: " . $response->body()
            );
        }

        Cache::forget('firestore.words.all');
    }
    public function getWordAudioFiles(array $document): array
    {
        $values = $document['fields']['audioFiles']['arrayValue']['values'] ?? [];

        $result = [];

        foreach ($values as $item) {
            $fields = $item['mapValue']['fields'] ?? [];

            $fileName = $fields['fileName']['stringValue'] ?? '';
            $storagePath = $fields['storagePath']['stringValue'] ?? '';

            if ($fileName !== '' && $storagePath !== '') {
                $result[] = [
                    'fileName' => $fileName,
                    'storagePath' => $storagePath,
                ];
            }
        }

        return $result;
    }
    public function updateWordAudioFiles(
        string $documentId,
        array $audioFiles
    ): void {
        $url = "{$this->baseUrl}/words/" . rawurlencode($documentId);

        $values = [];

        foreach ($audioFiles as $audio) {
            $values[] = [
                'mapValue' => [
                    'fields' => [
                        'fileName' => [
                            'stringValue' => $audio['fileName'],
                        ],
                        'storagePath' => [
                            'stringValue' => $audio['storagePath'],
                        ],
                    ],
                ],
            ];
        }

        $response = Http::withToken($this->getAccessToken())
            ->connectTimeout(30)
            ->timeout(60)
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->patch($url . '?' . http_build_query([
                'updateMask.fieldPaths' => 'audioFiles',
            ]), [
                'fields' => [
                    'audioFiles' => [
                        'arrayValue' => [
                            'values' => $values,
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Không thể cập nhật audioFiles cho {$documentId}: "
                    . $response->body()
            );
        }

        Cache::forget('firestore.words.all');
    }
    public function createWordWithAudioFiles(
        string $id,
        string $word,
        string $letter,
        array $audioFiles
    ): void {
        if (empty($audioFiles)) {
            throw new RuntimeException('audioFiles không được rỗng.');
        }

        $first = $audioFiles[0];

        $values = [];

        foreach ($audioFiles as $audio) {
            $values[] = [
                'mapValue' => [
                    'fields' => [
                        'fileName' => [
                            'stringValue' => $audio['fileName'],
                        ],
                        'storagePath' => [
                            'stringValue' => $audio['storagePath'],
                        ],
                    ],
                ],
            ];
        }

        $url = "{$this->baseUrl}/words?" . http_build_query([
            'documentId' => $id,
        ]);

        $response = Http::withToken($this->getAccessToken())
            ->timeout(60)
            ->post($url, [
                'fields' => [
                    'word' => ['stringValue' => $word],
                    'letter' => ['stringValue' => $letter],
                    'fileName' => ['stringValue' => $first['fileName']],
                    'storagePath' => ['stringValue' => $first['storagePath']],
                    'audioFiles' => [
                        'arrayValue' => [
                            'values' => $values,
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Không thể tạo từ {$word}: " . $response->body()
            );
        }

        Cache::forget('firestore.words.all');
    }
}
