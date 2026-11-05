<?php
$ch = curl_init("https://firestore.googleapis.com/");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);

echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
echo "ERRNO: " . curl_errno($ch) . PHP_EOL;
echo "ERROR: " . curl_error($ch) . PHP_EOL;

curl_close($ch);
