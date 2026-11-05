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

        $response = Http::withToken($this->getAccessToken())->get($url);

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
}
