<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LocalAudioController extends Controller
{
    public function show(Request $request)
    {
        $path = str_replace('\\', '/', $request->query('path', ''));

        // Không cho phép đường dẫn rỗng hoặc truy cập thư mục ngoài.
        if (
            $path === '' ||
            str_contains($path, '..') ||
            str_starts_with($path, '/') ||
            str_contains($path, ':')
        ) {
            abort(400, 'Đường dẫn âm thanh không hợp lệ.');
        }

        // Chỉ cho phép file WAV.
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'wav') {
            abort(400, 'Chỉ hỗ trợ file WAV.');
        }

        // Chuyển tên thư mục Supabase thành tên thư mục local.
        $folderMappings = [
            'A_BREVE' => 'Ă',
            'A_CIRCUMFLEX' => 'Â',
            'D_STROKE' => 'Đ',
            'E_CIRCUMFLEX' => 'Ê',
            'O_CIRCUMFLEX' => 'Ô',
            'O_HORN' => 'Ơ',
            'U_HORN' => 'Ư',
        ];

        $parts = explode('/', $path);

        if (count($parts) < 2) {
            abort(400, 'Đường dẫn âm thanh không hợp lệ.');
        }

        $parts[0] = $folderMappings[$parts[0]] ?? $parts[0];

        $root = realpath(config('services.audio.local_root'));

        if ($root === false) {
            abort(500, 'Không tìm thấy thư mục âm thanh.');
        }

        $filePath = realpath(
            $root . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts)
        );

        // Kiểm tra file có thực sự nằm trong thư mục source.
        if (
            $filePath === false ||
            !str_starts_with(
                strtolower($filePath),
                strtolower($root . DIRECTORY_SEPARATOR)
            ) ||
            !File::isFile($filePath)
        ) {
            abort(404, 'Không tìm thấy file âm thanh.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'audio/wav',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
