<?php

$path = 'D:/Projects/HCSDLDPT/firebase_key/serviceAccountKey.json';

if (!file_exists($path)) {
    exit("ERROR: Không tìm thấy file JSON\n");
}

$data = json_decode(file_get_contents($path), true);

if (!is_array($data)) {
    exit("ERROR: JSON không hợp lệ\n");
}

echo "JSON: OK\n";

echo "Project ID: " . ($data['project_id'] ?? 'MISSING') . "\n";

echo "Client email: " . (!empty($data['client_email']) ? 'OK' : 'MISSING') . "\n";

echo "Private key ID: " . (!empty($data['private_key_id']) ? 'OK' : 'MISSING') . "\n";

if (empty($data['private_key'])) {
    exit("Private key: MISSING\n");
}

$key = openssl_pkey_get_private($data['private_key']);

echo "OpenSSL private key: " . ($key !== false ? 'VALID' : 'INVALID') . "\n";

if ($key !== false) {
    openssl_free_key($key);
}