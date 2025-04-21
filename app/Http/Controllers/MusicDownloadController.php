<?php

namespace App\Http\Controllers;

use App\UseCase\MusicDownloadUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MusicDownloadController extends Controller
{
    public function __invoke(Request $request, MusicDownloadUseCase $musicDownloadUseCase): Response
    {
        $fileName = $musicDownloadUseCase($request->input());
    
        return Storage::download(
            'music.mp3', 
            $fileName,
            [
                [
                    'Content-Type' => 'audio/mpeg'
                ]
            ]
        );
    }
}
