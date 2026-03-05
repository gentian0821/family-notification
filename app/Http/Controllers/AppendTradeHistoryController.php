<?php

namespace App\Http\Controllers;

use App\Repositories\GoogleSpreadSheetRepository;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendTradeHistoryController extends Controller
{
    public function __invoke(Request $request, GoogleSpreadSheetRepository $repository): Response
    {
        // JSON配列を受け取る
        $trades = $request->json()->all(); 
        
        if (empty($trades)) {
            return response()->json(['message' => 'No data'], 200);
        }

        $spreadsheetId = "1T1XLyCdC_mvYCN4lAplKlyNzNFreyifplXncTor2iWw";
        $range = "シート1!A1";

        $rowsToAppend = [];
        foreach ($trades as $data) {
            $rowsToAppend[] = [
                $data['ticket'],                             // 0
                str_replace('-', '.', $data['date']),        // 1 (MT4形式に合わせる)
                $data['time'],                               // 2
                $data['type'],                               // 3
                $data['lots'],                               // 4
                $data['symbol'],                             // 5
                '', '', '', '', '', '',                      // 6-11
                $data['fee'],                                // 12
                '', '',                                      // 13-14
                $data['profit'],                             // 15
                '',                                          // 16
                $data['comment']                             // 17
            ];
        }

        // まとめて追記
        $repository->appendRows($spreadsheetId, $range, $rowsToAppend);

        return response()->json(['status' => 'success', 'count' => count($rowsToAppend)]);
    }
}
