<?php

namespace App\Http\Controllers;

use App\Repositories\GoogleSpreadSheetRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AppendTradeHistoryController extends Controller
{
    public function __invoke(Request $request, GoogleSpreadSheetRepository $repository): Response
    {
        // 方法1: JSONとしてデコード。第2引数をtrueにして連想配列にする
        $trades = json_decode($request->getContent(), true);

        // デバッグ用: もしこれでも空なら、ログに生データを出す
        if (empty($trades)) {
            Log::warning('MT4 Sync: Decoded data is empty. Raw content: ' . $request->getContent());
            return response()->json(['message' => 'No data', 'debug' => 'Data was empty after decode'], 200);
        }

        $spreadsheetId = "1T1XLyCdC_mvYCN4lAplKlyNzNFreyifplXncTor2iWw";
        $range = "シート1!A1";

        $rowsToAppend = [];
        foreach ($trades as $data) {
            $rowsToAppend[] = [
                $data['ticket'] ?? '',
                str_replace('-', '.', $data['date'] ?? ''),
                $data['time'] ?? '',
                $data['type'] ?? '',
                $data['lots'] ?? 0,
                $data['symbol'] ?? '',
                $data['open_price'] ?? 0,
                $data['sl'] ?? 0,
                $data['tp'] ?? 0,
                str_replace('-', '.', $data['close_date'] ?? ''),
                $data['close_time'] ?? '',
                $data['close_price'] ?? 0,
                $data['fee'] ?? 0,
                $data['swap'] ?? 0,
                0,                 // Index 13-14
                $data['profit'] ?? 0,
                $data['magic'] ?? '', 
                $data['comment'] ?? ''  // Index 17
            ];
        }

        // まとめて追記
        try {
            $repository->appendRows($spreadsheetId, $range, $rowsToAppend);
        } catch (\Exception $e) {
            Log::error('Spreadsheet Append Error: ' . $e->getMessage());
            return response()->json(['message' => 'Spreadsheet error', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success', 
            'count' => count($rowsToAppend)
        ]);
    }
}
