<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseStorageService
{
    private string $baseUrl;
    private string $bucket;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.supabase.url'),
            '/'
        );

        $this->bucket = config('services.supabase.bucket');

        if (!$this->baseUrl || !$this->bucket) {
            throw new RuntimeException(
                'Supabase configuration is missing.'
            );
        }
    }

    public function getPublicUrl(string $filePath): string
    {
        $segments = array_map(
            'rawurlencode',
            explode('/', trim($filePath, '/'))
        );

        $encodedPath = implode('/', $segments);

        return "{$this->baseUrl}/storage/v1/object/public/{$this->bucket}/{$encodedPath}";
    }

    /**
     * Tải file WAV từ Supabase về máy chủ Laravel.
     */
    public function downloadToLocal(
        string $filePath,
        string $destination
    ): void {
        $response = Http::timeout(60)->get(
            $this->getPublicUrl($filePath)
        );

        if (!$response->successful()) {
            throw new RuntimeException(
                "Không thể tải file WAV: {$filePath}"
            );
        }

        $directory = dirname($destination);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $written = file_put_contents(
            $destination,
            $response->body()
        );

        if ($written === false) {
            throw new RuntimeException(
                "Không thể lưu file WAV: {$destination}"
            );
        }
    }
    public function uploadFile(
        string $localPath,
        string $filePath
    ): array {
        $key = config('services.supabase.service_role_key');

        if (!$key) {
            throw new RuntimeException('Supabase key is missing.');
        }

        if (!is_file($localPath)) {
            throw new RuntimeException(
                "Không tìm thấy file nguồn: {$localPath}"
            );
        }

        $contents = file_get_contents($localPath);

        if ($contents === false) {
            throw new RuntimeException(
                "Không đọc được file: {$localPath}"
            );
        }

        $segments = array_map(
            'rawurlencode',
            explode('/', trim($filePath, '/'))
        );

        $encodedPath = implode('/', $segments);

        $url = "{$this->baseUrl}/storage/v1/object/{$this->bucket}/{$encodedPath}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$key}",
            'apikey' => $key,
            'Content-Type' => 'audio/wav',
            'x-upsert' => 'false',
        ])
            ->timeout(120)
            ->withBody($contents, 'audio/wav')
            ->post($url);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}
