<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(\App\Services\FirestoreService::class);

$method = new ReflectionMethod($service, 'getAccessToken');
$method->setAccessible(true);
$token = $method->invoke($service);

$url = 'https://firestore.googleapis.com/v1/projects/whalespeed-2ee1c/databases/(default)/documents/words?pageSize=1000';

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);

echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
echo "ERRNO: " . curl_errno($ch) . PHP_EOL;
echo "ERROR: " . curl_error($ch) . PHP_EOL;

if ($response !== false) {
    echo "RESPONSE: " . substr($response, 0, 1000) . PHP_EOL;
}

curl_close($ch);

