<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$path = config('services.firebase.credentials');

echo "Laravel configured path: " . ($path ?: 'MISSING') . PHP_EOL;
echo "File exists: " . ($path && file_exists($path) ? 'YES' : 'NO') . PHP_EOL;

if ($path && file_exists($path)) {
    $data = json_decode(file_get_contents($path), true);

    echo "Project ID: " . ($data['project_id'] ?? 'MISSING') . PHP_EOL;
    echo "Key ID: " . ($data['private_key_id'] ?? 'MISSING') . PHP_EOL;
}