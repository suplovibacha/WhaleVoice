<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use App\Services\AudioMergeService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

Route::get('/test-firestore', function (FirestoreService $firestore) {
    try {
        $document = $firestore->getDocument(
            'speakers',
            'speaker_01'
        );

        return response()->json([
            'success' => true,
            'message' => 'Connected to Firestore successfully!',
            'data' => $document,
        ]);
    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Firestore connection failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
});



Route::post('/tts/convert', function (
    Request $request,
    FirestoreService $firestore,
    AudioMergeService $audioMerge
) {
    $request->validate([
        'text' => ['required', 'string', 'max:2000'],
    ]);

    try {
        set_time_limit(120);

        $text = trim($request->input('text'));

        $tokens = preg_split(
            '/\s+/u',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        // Làm sạch dấu câu và chuẩn hóa từ.
        $tokenWords = [];

        foreach ($tokens as $token) {
            $word = preg_replace(
                '/^\p{P}+|\p{P}+$/u',
                '',
                $token
            );

            if ($word !== '') {
                $tokenWords[] = mb_strtolower($word, 'UTF-8');
            }
        }

        // Chỉ truy vấn những từ người dùng nhập.
        $allWords = $firestore->getWordsByWords($tokenWords);

        $wordMap = collect($allWords)->keyBy(function ($item) {
            return mb_strtolower(
                trim($item['word'] ?? ''),
                'UTF-8'
            );
        });

        // Thư mục WAV gốc trên máy tính.
        $root = realpath(config('services.audio.local_root'));

        if ($root === false) {
            throw new RuntimeException(
                'Không tìm thấy thư mục WAV local.'
            );
        }

        // Ánh xạ tên thư mục Firestore về tên thư mục local.
        $folderMappings = [
            'A_BREVE' => 'Ă',
            'A_CIRCUMFLEX' => 'Â',
            'D_STROKE' => 'Đ',
            'E_CIRCUMFLEX' => 'Ê',
            'O_CIRCUMFLEX' => 'Ô',
            'O_HORN' => 'Ơ',
            'U_HORN' => 'Ư',
        ];

        $inputFiles = [];
        $missing = [];
        $matchedWords = [];

        foreach ($tokenWords as $word) {
            if (!$wordMap->has($word)) {
                $missing[] = $word;
                continue;
            }

            $item = $wordMap->get($word);
            $storagePath = str_replace(
                '\\',
                '/',
                trim($item['storagePath'] ?? '')
            );

            if (
                $storagePath === '' ||
                str_contains($storagePath, '..') ||
                str_starts_with($storagePath, '/') ||
                str_contains($storagePath, ':')
            ) {
                $missing[] = $word;
                continue;
            }

            $parts = explode('/', $storagePath);

            if (count($parts) !== 2) {
                $missing[] = $word;
                continue;
            }

            $parts[0] = $folderMappings[$parts[0]] ?? $parts[0];

            if (
                strtolower(pathinfo($parts[1], PATHINFO_EXTENSION))
                !== 'wav'
            ) {
                $missing[] = $word;
                continue;
            }

            $sourceFile = realpath(
                $root . DIRECTORY_SEPARATOR .
                implode(DIRECTORY_SEPARATOR, $parts)
            );

            $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR;

            if (
                $sourceFile === false ||
                !str_starts_with(
                    strtolower($sourceFile),
                    strtolower($rootPrefix)
                ) ||
                !is_file($sourceFile)
            ) {
                $missing[] = $word;
                continue;
            }

            // Sử dụng trực tiếp WAV gốc, không tải từ Supabase.
            $inputFiles[] = $sourceFile;
            $matchedWords[] = $word;
        }

        if (count($inputFiles) === 0) {
            return response()->json([
                'success' => true,
                'total' => 0,
                'missing' => array_values(array_unique($missing)),
                'audio_url' => null,
            ]);
        }

        // Tạo thư mục lưu file WAV sau khi ghép.
        $jobId = (string) Str::uuid();
        $jobDirectory = storage_path('app/tts/' . $jobId);

        if (
            !is_dir($jobDirectory) &&
            !mkdir($jobDirectory, 0775, true)
        ) {
            throw new RuntimeException(
                'Không thể tạo thư mục lưu âm thanh.'
            );
        }

        $outputFile = $jobDirectory . '/merged.wav';

        // Ghép các file WAV local.
        $audioMerge->merge(
            $inputFiles,
            $outputFile
        );

        if (
            !file_exists($outputFile) ||
            filesize($outputFile) === 0
        ) {
            throw new RuntimeException(
                'File WAV sau khi ghép không hợp lệ.'
            );
        }

        Log::info('TTS LOCAL MERGE SUCCESS', [
            'total' => count($inputFiles),
            'matched_words' => $matchedWords,
            'missing' => $missing,
            'output_size' => filesize($outputFile),
        ]);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download(
            $outputFile,
            'whalevoice.wav',
            [
                'Content-Type' => 'audio/wav',
            ]
        );

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Không thể ghép âm thanh.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

