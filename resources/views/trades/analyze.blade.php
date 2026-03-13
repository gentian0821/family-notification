<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MT4詳細統計レポート</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f7f6; font-family: "Segoe UI", "Helvetica Neue", sans-serif; color: #333; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 20px; border-radius: 8px; }
        .report-header { background: #fff; border-bottom: 3px solid #34495e; padding: 25px; margin-bottom: 25px; border-radius: 8px 8px 0 0; }
        .table-mt4 { background: #fff; font-size: 0.9rem; margin: 0; }
        .table-mt4 th { background: #fcfcfc; color: #7f8c8d; font-weight: 600; width: 45%; border-bottom: 1px solid #eee; }
        .value-plus { color: #2980b9; font-weight: bold; }
        .value-minus { color: #c0392b; font-weight: bold; }
        .chart-container { position: relative; height: 350px; width: 100%; }
        .stats-label { font-size: 0.75rem; text-transform: uppercase; color: #95a5a6; font-weight: bold; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">分析パラメータ設定</h5>
            <form action="/trades/analyze" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">期間（開始）</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">期間（終了）</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">初期証拠金</label>
                    <input type="number" name="init" class="form-control" value="{{ request('init', 1000000) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">コメント検索</label>
                    <input type="text" name="comment" class="form-control" placeholder="EA名など" value="{{ request('comment') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">分析実行</button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($stats))
    <div class="report-header shadow-sm">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="mb-1 text-dark">Strategy Tester Report</h2>
                <p class="text-muted small mb-0">
                    Target Comment: <strong>{{ request('comment') ?: 'All Records' }}</strong>
                </p>
            </div>
            <div class="text-end text-muted small">
                Report Generated: {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="stats-label mb-2">Equity Curve (資産推移)</div>
            <div class="chart-container">
                <canvas id="equityChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="stats-label">Summary Performance</span>
                    <!-- トグルスイッチ -->
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="feeToggle" role="switch">
                        <label class="form-check-label small text-muted" for="feeToggle" id="feeToggleLabel">
                            手数料別
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-mt4">
                        <tr>
                            <th>純利益 (Gross Net Profit)</th>
                            <td id="val-net"
                                data-ex="{{ number_format($net_profit, 2) }}"
                                data-in="{{ number_format($net_with_fee, 2) }}"
                                class="{{ $net_profit >= 0 ? 'value-plus' : 'value-minus' }}">
                                {{ number_format($net_profit, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>プロフィットファクター (PF)</th>
                            <td id="val-pf"
                                data-ex="{{ number_format($pf, 2) }}"
                                data-in="{{ number_format($pf_in, 2) }}">
                                {{ number_format($pf, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>リカバリーファクター (RF)</th>
                            <td id="val-rf"
                                data-ex="{{ number_format($rf, 2) }}"
                                data-in="{{ number_format($rf_in, 2) }}">
                                {{ number_format($rf, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>期待利得 (Expected Payoff)</th>
                            <td id="val-payoff"
                                data-ex="{{ number_format($expected_payoff, 2) }}"
                                data-in="{{ number_format($payoff_in, 2) }}">
                                {{ number_format($expected_payoff, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>手数料合計 (Total Fee)</th>
                            <td class="text-secondary fw-bold">{{ number_format($total_fee, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white stats-label">Risk Metrics</div>
                <div class="card-body p-0">
                    <table class="table table-mt4">
                        <tr><th>絶対ドローダウン (Abs DD)</th><td>{{ number_format($abs_dd, 2) }}</td></tr>
                        <tr><th>最大ドローダウン (Max DD)</th><td>{{ number_format($max_dd, 2) }}</td></tr>
                        <tr><th>相対ドローダウン (Rel DD %)</th><td class="text-danger fw-bold">{{ number_format($max_rel_dd, 2) }}%</td></tr>
                        <tr><th>総取引数 (Total Trades)</th><td>{{ $total_trades }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white stats-label">Side-by-Side Analysis</div>
        <div class="card-body p-0 text-center">
            <table class="table table-mt4 mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-start ps-4">Metric</th><th>Overall</th><th>Buy (Long)</th><th>Sell (Short)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th class="text-start ps-4">Trades</th><td>{{ $total_trades }}</td><td>{{ $buy_total }}</td><td>{{ $sell_total }}</td></tr>
                    <tr><th class="text-start ps-4">Win Rate</th><td>{{ $total_win_rate }}%</td><td>{{ $buy_win_rate }}%</td><td>{{ $sell_win_rate }}%</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white stats-label">Profit by Hour (Server Time)</div>
                <div class="card-body">
                    <canvas id="hourChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white stats-label">Profit by Weekday</div>
                <div class="card-body">
                    <canvas id="dayChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@if(isset($stats))
<script>
    // ヘルパー：安全にグラフを初期化
    const initChart = (id, config) => {
        const ctx = document.getElementById(id);
        if (ctx) return new Chart(ctx, config);
    };

    // 1. 資産曲線
    initChart('equityChart', {
        type: 'line',
        data: {
            labels: @json($equity_labels),
            datasets: [{
                label: 'Account Balance',
                data: @json($equity_data),
                borderColor: '#2980b9',
                backgroundColor: 'rgba(41, 128, 185, 0.05)',
                fill: true,
                pointRadius: 0,
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { display: false },
                y: { ticks: { callback: v => v.toLocaleString() } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // 2. 時間帯別純益
    initChart('hourChart', {
        type: 'bar',
        data: {
            labels: @json($hourlyLabels),
            datasets: [{
                data: @json($hourlyValues),
                backgroundColor: 'rgba(52, 152, 219, 0.8)',
                borderRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 3. 曜日別純益
    initChart('dayChart', {
        type: 'bar',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                data: @json($daily_profits),
                backgroundColor: 'rgba(26, 188, 156, 0.8)',
                borderRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 手数料トグル
    document.getElementById('feeToggle').addEventListener('change', function () {
        const mode = this.checked ? 'in' : 'ex';
        const label = this.checked ? '手数料込み' : '手数料別';
        document.getElementById('feeToggleLabel').textContent = label;

        ['val-net', 'val-pf', 'val-rf', 'val-payoff'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            const val = parseFloat(el.dataset[mode].replace(/,/g, ''));
            el.textContent = val.toLocaleString('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            // 色クラスも更新
            el.classList.toggle('value-plus', val >= 0);
            el.classList.toggle('value-minus', val < 0);
        });
    });
</script>
@endif

</body>
</html>