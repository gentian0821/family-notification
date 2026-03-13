<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UseCase\TradeAnalyzeUseCase;
use Carbon\Carbon;

class TradeAnalysisController extends Controller
{
    public function __construct(
        private TradeAnalyzeUseCase $useCase
    ) {}

    public function index(Request $request)
    {
        $fromDate = $request->query('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->query('to',   Carbon::now()->format('Y-m-d'));
        $searchComment = $request->query('comment');
        $initialBalance = (float)$request->query('init', 500000);

        $result = ($this->useCase)($fromDate, $toDate, $searchComment, $initialBalance);

        return view('trades.analyze', $result);
    }
}