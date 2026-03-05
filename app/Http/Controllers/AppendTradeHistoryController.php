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
        // 生のデータを取得し、前後の空白や目に見えない改行を完全にトリミング
        $rawContent = trim($request->getContent());

        // JSONデコード（第2引数 true で連想配列へ）
        $trades = json_decode($rawContent, true);

        // デコードに失敗した場合の調査用ロジック
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = json_last_error_msg();
            
            // 特効薬：もう一度、より強力にクリーンアップして再試行
            $cleanContent = preg_replace('/[[:cntrl:]]/', '', $rawContent); // 制御文字を全削除
            $trades = json_decode($cleanContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON Decode Final Failure: " . $errorMsg);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON structure',
                    'error_detail' => $errorMsg
                ], 400);
            }
        }

        if (empty($trades)) {
            return response()->json(['message' => 'No data'], 200);
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
