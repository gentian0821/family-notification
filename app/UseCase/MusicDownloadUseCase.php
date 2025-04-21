<?php

namespace App\UseCase;

use App\Repositories\OpenWeatherRepository;
use App\Repositories\LineMessageApiRepository;
use Exception;
use Illuminate\Support\Facades\Process;

class MusicDownloadUseCase
{
    public function __construct(
        private LineMessageApiRepository $lineMessageApiRepository,
        private OpenWeatherRepository $openWeatherRepository
    ) {
    }

    public function __invoke(array $params): string
    {
        Process::run("rm -f /application/storage/app/*.mp3");
        $process = Process::run("yt-dlp -x -f \"bestaudio\" --audio-format mp3 --audio-quality 0 --output '/application/storage/app/music.mp3' " . $params['url']);
        $output = $process->output() ?: $process->errorOutput();

        if (!$process->successful()) {
            throw new Exception($output);
        }

        $process = Process::run("yt-dlp --output '%(title)s.%(ext)s' --print filename " . $params['url']);

        return pathinfo($process->output())['filename'] . '.mp3';
    }
}
