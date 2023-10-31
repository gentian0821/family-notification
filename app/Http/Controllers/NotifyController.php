<?php

namespace App\Http\Controllers;

use App\UseCase\NotifyUseCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends Controller
{
    public function __invoke(Request $request, NotifyUseCase $notifyUseCase): Response
    {
        $notifyUseCase($request->input());

        return response()->json(['ok']);
    }
}
