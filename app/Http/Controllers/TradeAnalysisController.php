<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UseCase\TradeAnalyzeUseCase;

class TradeAnalysisController extends Controller
{
    public function __construct(
        private TradeAnalyzeUseCase $useCase
    ) {}

    public function index(Request $request)
    {
        $fromDate = $request->query('from');
        $toDate = $request->query('to');
        $searchComment = $request->query('comment');
        $initialBalance = (float)$request->query('init', 1000000);

        $result = ($this->useCase)($fromDate, $toDate, $searchComment, $initialBalance);

        return view('trades.analyze', $result);
    }
}