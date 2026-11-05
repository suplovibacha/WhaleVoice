<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(\App\Services\FirestoreService::class);

$method = new ReflectionMethod($service, 'getAccessToken');
$method->setAccessible(true);
$token = $method->invoke($service);

$url = 'https://firestore.googleapis.com/v1/projects/whalespeed-2ee1c/databases/(default)/documents/words';

try {
    $response = Illuminate\Support\Facades\Http::withToken($token)
        ->timeout(30)
        ->connectTimeout(10)
        ->get($url, ['pageSize' => 1000]);

    echo "HTTP: " . $response->status() . PHP_EOL;
    echo "SUCCESS: " . ($response->successful() ? 'YES' : 'NO') . PHP_EOL;
    echo "DOCUMENTS: " . count($response->json('documents', [])) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERROR CLASS: " . get_class($e) . PHP_EOL;
    echo "ERROR MESSAGE: " . $e->getMessage() . PHP_EOL;
}
