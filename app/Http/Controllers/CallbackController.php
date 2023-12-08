<?php

namespace App\Http\Controllers;

use App\UseCase\CallbackUseCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CallbackController extends Controller
{
    public function __invoke(Request $request, CallbackUseCase $callbackUseCase): Response
    {
        $callbackUseCase($request->input());

        return response()->json(['ok']);
    }
}
