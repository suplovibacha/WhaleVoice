<?php

namespace App\Services;

use RuntimeException;

class AudioMergeService
{
    private string $ffmpegPath =
        'D:/ffmpeg-9.0.2-full_build/ffmpeg-9.0.2-full_build/bin/ffmpeg.exe';

    /**
     * Ghép danh sách WAV thành một file WAV duy nhất.
     */
    public function merge(array $inputFiles, string $outputFile): void
    {
        if (count($inputFiles) === 0) {
            throw new RuntimeException('Không có file WAV để ghép.');
        }

        if (!file_exists($this->ffmpegPath)) {
            throw new RuntimeException('Không tìm thấy FFmpeg.');
        }

        foreach ($inputFiles as $file) {
            if (!file_exists($file)) {
                throw new RuntimeException("Không tìm thấy file: {$file}");
            }
        }

        $directory = dirname($outputFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $command = escapeshellarg($this->ffmpegPath)
            . ' -y';

        foreach ($inputFiles as $file) {
            $command .= ' -i ' . escapeshellarg($file);
        }

        $count = count($inputFiles);

        $filters = [];

        foreach (range(0, $count - 1) as $index) {
            $filters[] = "[$index:a]";
        }

        $filter = implode('', $filters)
            . "concat=n={$count}:v=0:a=1[out]";

        $command .= ' -filter_complex '
            . escapeshellarg($filter)
            . ' -map '
            . escapeshellarg('[out]')
            . ' -c:a pcm_s16le '
            . escapeshellarg($outputFile)
            . ' 2>&1';

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputFile)) {
            throw new RuntimeException(
                "Ghép âm thanh thất bại: " . implode("\n", $output)
            );
        }
    }
}
