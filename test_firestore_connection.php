<?php
$url = "https://firestore.googleapis.com/v1/projects/whalespeed-2ee1c/databases/(default)/documents/words?pageSize=1";

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 40,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

$result = curl_exec($ch);

echo "cURL errno: " . curl_errno($ch) . PHP_EOL;
echo "cURL error: " . curl_error($ch) . PHP_EOL;
echo "HTTP status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;

curl_close($ch);
?>
