<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

$projectId = 'whalespeed-2ee1c';
$keyPath = 'D:/Projects/HCSDLDPT/firebase_key/serviceAccountKey.json';

try {
    // 1. Kiểm tra Service Account
    if (!file_exists($keyPath)) {
        throw new Exception('Không tìm thấy Service Account JSON.');
    }

    $key = json_decode(file_get_contents($keyPath), true);

    if (!$key || empty($key['client_email'])) {
        throw new Exception('Service Account JSON không hợp lệ.');
    }

    // 2. Xác thực với Google
    $credentials = new ServiceAccountCredentials(
        ['https://www.googleapis.com/auth/datastore'],
        $key
    );

    $token = $credentials->fetchAuthToken();

    if (empty($token['access_token'])) {
        throw new Exception('Không lấy được Google Access Token.');
    }

    // 3. Gọi Firestore REST API
    $client = new Client();

    $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/words?pageSize=1";

    $response = $client->get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Accept' => 'application/json',
        ],
    ]);

    $data = json_decode($response->getBody(), true);

    echo "KET NOI FIRESTORE THANH CONG!\n";
    echo "Project: {$projectId}\n";
    echo "Collection: words\n";

    if (!empty($data['documents'])) {
        $document = $data['documents'][0];
        $fields = $document['fields'] ?? [];

        echo "Doc ID: " . basename($document['name']) . "\n";
        echo "Word: " . ($fields['word']['stringValue'] ?? 'N/A') . "\n";
        echo "Letter: " . ($fields['letter']['stringValue'] ?? 'N/A') . "\n";
        echo "Storage Path: " . ($fields['storagePath']['stringValue'] ?? 'N/A') . "\n";
    } else {
        echo "Collection chua co document nao.\n";
    }

} catch (Throwable $e) {
    echo "KET NOI THAT BAI!\n";
    echo "Loi: " . $e->getMessage() . "\n";
}