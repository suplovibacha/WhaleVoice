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

use App\Services\SupabaseStorageService;

Route::post('/tts/convert', function (
    Request $request,
    FirestoreService $firestore,
    SupabaseStorageService $supabase,
    AudioMergeService $audioMerge
) {
    $request->validate([
        'text' => ['required', 'string', 'max:2000'],
    ]);

    $tempDirectory = storage_path('app/tts');

    try {
        set_time_limit(120);

        Log::info('TTS REQUEST START', [
            'max_execution_time' => ini_get('max_execution_time'),
        ]);
        $text = trim($request->input('text'));

        $tokens = preg_split(
            '/\s+/u',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $allWords = collect($firestore->getAllWords());

        $wordMap = $allWords->keyBy(function ($item) {
            return mb_strtolower(
                trim($item['word'] ?? ''),
                'UTF-8'
            );
        });

        $inputFiles = [];
        $missing = [];
        $matchedWords = [];

        $jobId = (string) Str::uuid();
        $jobDirectory = $tempDirectory . '/' . $jobId;

        foreach ($tokens as $token) {
            $word = preg_replace(
                '/^\p{P}+|\p{P}+$/u',
                '',
                $token
            );

            if ($word === '') {
                continue;
            }

            $key = mb_strtolower($word, 'UTF-8');

            if (!$wordMap->has($key)) {
                $missing[] = $word;
                continue;
            }

            $item = $wordMap->get($key);
            $storagePath = $item['storagePath'] ?? '';

            if ($storagePath === '') {
                $missing[] = $word;
                continue;
            }

            $localFile = $jobDirectory . '/' .
                count($inputFiles) . '.wav';

            $supabase->downloadToLocal(
                $storagePath,
                $localFile
            );

            $inputFiles[] = $localFile;
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

        $outputFile = $jobDirectory . '/merged.wav';

        $audioMerge->merge(
            $inputFiles,
            $outputFile
        );
        Log::info('TTS MERGE CHECK', [
            'outputFile' => $outputFile,
            'exists' => file_exists($outputFile),
            'size' => file_exists($outputFile)
                ? filesize($outputFile)
                : null,
        ]);
        $handle = fopen($outputFile, 'rb');
        $firstBytes = bin2hex(fread($handle, 8));
        fclose($handle);

        Log::info('TTS WAV BYTE CHECK', [
            'first_bytes' => $firstBytes,
            'output_buffer_level' => ob_get_level(),
            'output_buffer_contents' => ob_get_contents(),
        ]);
        // Xóa dữ liệu thừa trong output buffer trước khi trả file WAV.
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
