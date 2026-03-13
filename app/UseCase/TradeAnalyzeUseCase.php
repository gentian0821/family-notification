<?php

namespace App\UseCase;

use App\Repositories\GoogleSpreadSheetRepository;
use Carbon\Carbon;

class TradeAnalyzeUseCase
{
    public function __construct(
        private GoogleSpreadSheetRepository $repository
    ) {
    }

    public function __invoke(?string $fromDate, ?string $toDate, ?string $searchComment, ?string $initialBalance): array
    {
        // --- ロジック開始 ---
        $sheet = str_contains($searchComment, 'EA3') || str_contains($searchComment, 'EA7') ? 'シート2' : 'シート1';
        $range = $sheet . '!A2:R'; 
        $spreadsheetId = "1T1XLyCdC_mvYCN4lAplKlyNzNFreyifplXncTor2iWw";
        
        try {
            $values = $this->repository->getSheet($spreadsheetId, $range);
        } catch (\Exception $e) {
            throw $e;
        }

        if (empty($values)) {
            return [];
        }

        // 初期化
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyStats[$i] = ['profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0];
        }

        $dailyStats = [
            1 => ['name' => '月', 'profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0],
            2 => ['name' => '火', 'profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0],
            3 => ['name' => '水', 'profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0],
            4 => ['name' => '木', 'profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0],
            5 => ['name' => '金', 'profit' => 0, 'loss' => 0, 'win_count' => 0, 'total_count' => 0, 'fee' => 0],
        ];

        $stats = [
            'total' => ['win' => 0, 'loss' => 0, 'profit' => 0, 'loss_sum' => 0, 'max_win' => 0, 'max_loss' => 0, 'fee' => 0],
            'buy'   => ['win' => 0, 'loss' => 0],
            'sell'  => ['win' => 0, 'loss' => 0],
        ];

        $balance = $initialBalance; 
        $max_balance = $initialBalance; // ドローダウン計算のため初期残高で初期化
        $max_dd_amount = 0; 
        $abs_dd = 0;
        $max_rel_dd_percent = 0;

        $equity_data = [$initialBalance]; // グラフ用：初期値をセット
        $equity_labels = [0];
        $filteredCount = 0;

        foreach ($values as $cols) {
            if (count($cols) < 16) continue;

            $comment = $cols[17] ?? '';

            // 修正後
            if ($searchComment) {
                $keywords = array_filter(explode(' ', $searchComment));
                foreach ($keywords as $keyword) {
                    if (!str_contains($comment, $keyword)) continue 2;
                }
            }

            $rawDate = str_replace('.', '-', $cols[1]); 
            $tradeTime = Carbon::parse($rawDate . ' ' . $cols[2]);

            if ($fromDate && $tradeTime->lt(Carbon::parse($fromDate)->startOfDay())) continue;
            if ($toDate && $tradeTime->gt(Carbon::parse($toDate)->endOfDay())) continue;

            $filteredCount++;

            $type = strtolower($cols[3]);       
            $fee = (float)($cols[12] ?? 0);     
            $profit = (float)($cols[15] ?? 0);  
            $side = str_contains($type, 'buy') ? 'buy' : 'sell';

            $stats['total']['fee'] += $fee;
            $hour = $tradeTime->hour;
            $dayOfWeek = $tradeTime->dayOfWeekIso;

            if (isset($dailyStats[$dayOfWeek])) {
                $dailyStats[$dayOfWeek]['total_count']++;
            }
            $hourlyStats[$hour]['total_count']++;

            if ($profit >= 0) {
                $hourlyStats[$hour]['profit'] += $profit;
                $hourlyStats[$hour]['win_count']++;
                if (isset($dailyStats[$dayOfWeek])) $dailyStats[$dayOfWeek]['win_count']++;
                if (isset($dailyStats[$dayOfWeek])) $dailyStats[$dayOfWeek]['profit'] += $profit;
                
                $stats['total']['win']++;
                $stats[$side]['win']++;
                $stats['total']['profit'] += $profit;
                $stats['total']['max_win'] = max($stats['total']['max_win'], $profit);
            } else {
                $hourlyStats[$hour]['loss'] += abs($profit);
                if (isset($dailyStats[$dayOfWeek])) $dailyStats[$dayOfWeek]['loss'] += abs($profit);
                
                $stats['total']['loss']++;
                $stats[$side]['loss']++;
                $stats['total']['loss_sum'] += abs($profit);
                $stats['total']['max_loss'] = max($stats['total']['max_loss'], abs($profit));
            }
            $hourlyStats[$hour]['fee'] += $fee;
            if (isset($dailyStats[$dayOfWeek])) $dailyStats[$dayOfWeek]['fee'] += $fee;

            // --- 資産推移 & ドローダウン ---
            $balance += ($profit + $fee); 
            $equity_data[] = $balance;
            $equity_labels[] = $filteredCount;

            if ($balance > $max_balance) $max_balance = $balance;
            $current_dd_amount = $max_balance - $balance;
            $max_dd_amount = max($max_dd_amount, $current_dd_amount);

            if ($max_balance > 0) {
                $current_rel_dd = ($current_dd_amount / $max_balance) * 100;
                $max_rel_dd_percent = max($max_rel_dd_percent, $current_rel_dd);
            }

            if ($balance < $initialBalance) {
                $abs_dd = max($abs_dd, $initialBalance - $balance);
            }
        }

        // --- 指標の集計 ---
        $total_trades = $stats['total']['win'] + $stats['total']['loss'];
        $net_profit = $stats['total']['profit'] - $stats['total']['loss_sum'];
        $total_fee = $stats['total']['fee'];

        // 手数料別（fee excluded）
        $pf_ex     = $stats['total']['loss_sum'] > 0 ? $stats['total']['profit'] / $stats['total']['loss_sum'] : 0;
        $rf_ex     = $max_dd_amount > 0 ? $net_profit / $max_dd_amount : 0;
        $payoff_ex = $total_trades > 0 ? $net_profit / $total_trades : 0;

        // 手数料込み（fee included）
        $net_with_fee    = $net_profit + $total_fee;
        $loss_with_fee   = $stats['total']['loss_sum'] + abs($total_fee); // 手数料を損失側に加算
        $pf_in           = $loss_with_fee > 0 ? $stats['total']['profit'] / $loss_with_fee : 0;
        $rf_in           = $max_dd_amount > 0 ? $net_with_fee / $max_dd_amount : 0;
        $payoff_in       = $total_trades > 0 ? $net_with_fee / $total_trades : 0;

        // グラフ用データ成形
        $hourlyLabels = array_keys($hourlyStats);
        $hourlyValues = array_map(fn($h) => $h['profit'] - $h['loss'] + $h['fee'], $hourlyStats);
        $dailyLabels = array_values(array_column($dailyStats, 'name'));
        $dailyValues = array_map(fn($d) => $d['profit'] - $d['loss'] + $d['fee'], $dailyStats);

        return [
            'stats' => $stats,
            'net_profit' => $net_profit,
            'total_fee' => $total_fee,
            'pf'              => $pf_ex,
            'rf'              => $rf_ex,
            'expected_payoff' => $payoff_ex,
            'net_with_fee'    => $net_with_fee,
            'pf_in'           => $pf_in,
            'rf_in'           => $rf_in,
            'payoff_in'       => $payoff_in,
            'abs_dd' => $abs_dd,
            'max_dd' => $max_dd_amount,
            'max_rel_dd' => $max_rel_dd_percent,
            'total_trades' => $total_trades,
            'buy_total' => $stats['buy']['win'] + $stats['buy']['loss'],
            'sell_total' => $stats['sell']['win'] + $stats['sell']['loss'],
            'total_win_rate' => $total_trades > 0 ? round(($stats['total']['win'] / $total_trades) * 100, 2) : 0,
            'buy_win_rate' => ($stats['buy']['win'] + $stats['buy']['loss']) > 0 ? round(($stats['buy']['win'] / ($stats['buy']['win'] + $stats['buy']['loss'])) * 100, 2) : 0,
            'sell_win_rate' => ($stats['sell']['win'] + $stats['sell']['loss']) > 0 ? round(($stats['sell']['win'] / ($stats['sell']['win'] + $stats['sell']['loss'])) * 100, 2) : 0,
            'equity_labels' => $equity_labels,
            'equity_data' => $equity_data,
            'hourlyLabels' => $hourlyLabels,
            'hourlyValues' => $hourlyValues,
            'dailyLabels' => $dailyLabels,
            'dailyStats' => $dailyStats,
            'daily_profits' => array_values($dailyValues)
        ];
    }
}
