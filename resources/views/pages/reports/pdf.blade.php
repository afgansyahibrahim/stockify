<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris - Stockify</title>
    <style>
        @page {
            margin: 18mm 14mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.45;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 16px;
            padding-bottom: 12px;
        }

        .brand-mark {
            background: #2563eb;
            border-radius: 6px;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            height: 32px;
            text-align: center;
            width: 32px;
        }

        .brand-name {
            color: #172033;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.2px;
        }

        .brand-caption,
        .report-meta,
        .muted {
            color: #64748b;
        }

        .report-title {
            color: #0f172a;
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .report-period {
            color: #475569;
            font-size: 9px;
            margin-top: 4px;
        }

        .section-title {
            border-left: 3px solid #2563eb;
            color: #0f172a;
            font-size: 10px;
            font-weight: bold;
            margin: 18px 0 8px;
            padding-left: 7px;
            text-transform: uppercase;
        }

        .metric-grid,
        .financial-grid,
        .loss-grid,
        .transaction-table {
            border-collapse: separate;
            border-spacing: 6px 0;
            width: 100%;
        }

        .metric-card,
        .financial-card {
            border: 1px solid #dbe3ef;
            border-radius: 5px;
            padding: 10px;
            vertical-align: top;
        }

        .metric-card {
            background: #f8fafc;
            width: 33.33%;
        }

        .financial-card {
            background: #ffffff;
            width: 25%;
        }

        .metric-label,
        .financial-label,
        .loss-label {
            color: #64748b;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .metric-value {
            color: #0f172a;
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        .financial-value {
            color: #0f172a;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .revenue {
            color: #047857;
        }

        .gross-profit {
            color: #1d4ed8;
        }

        .estimated-profit-positive {
            color: #047857;
        }

        .estimated-profit-negative {
            color: #be123c;
        }

        .loss-grid {
            border-spacing: 6px 0;
            margin-top: 8px;
        }

        .loss-item {
            background: #fff7f7;
            border: 1px solid #fecdd3;
            border-radius: 4px;
            color: #be123c;
            padding: 7px 8px;
            vertical-align: top;
            width: 33.33%;
        }

        .loss-item.neutral {
            background: #f5f3ff;
            border-color: #ddd6fe;
            color: #6d28d9;
        }

        .loss-value {
            font-size: 10px;
            font-weight: bold;
            margin-top: 3px;
        }

        .transaction-table {
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 8px;
        }

        .transaction-table th {
            background: #0f2f67;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2px;
            padding: 8px 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .transaction-table td {
            border-bottom: 1px solid #dbe3ef;
            padding: 8px 7px;
            vertical-align: top;
        }

        .transaction-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .code {
            color: #0f172a;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7.5px;
            font-weight: bold;
        }

        .type-in {
            color: #047857;
            font-weight: bold;
        }

        .type-out {
            color: #be123c;
            font-weight: bold;
        }

        .product-line {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 3px;
            padding-bottom: 3px;
        }

        .product-line:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .footer {
            border-top: 1px solid #dbe3ef;
            color: #64748b;
            font-size: 7.5px;
            margin-top: 18px;
            padding-top: 7px;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $estimatedProfit = (float) $summary['estimated_profit'];
        $generatedAt = $generatedAt ?? now();
    @endphp

    <table class="header">
        <tr>
            <td width="52" valign="top">
                <div class="brand-mark">S</div>
            </td>
            <td valign="top">
                <div class="brand-name">Stockify</div>
                <div class="brand-caption">Sistem Inventaris</div>
            </td>
            <td align="right" valign="top">
                <p class="report-title">Laporan Inventaris</p>
                <p class="report-period">{{ $periodLabel }}</p>
                <p class="report-meta">Dicetak {{ $generatedAt->format('d/m/Y H:i') }} WIB</p>
            </td>
        </tr>
    </table>

    <p class="section-title">Ringkasan Operasional</p>

    <table class="metric-grid">
        <tr>
            <td class="metric-card">
                <div class="metric-label">Transaksi Disetujui</div>
                <div class="metric-value">{{ $summary['transactions'] }}</div>
            </td>
            <td class="metric-card">
                <div class="metric-label">Barang Masuk</div>
                <div class="metric-value">+{{ $summary['incoming_items'] }} Pcs</div>
            </td>
            <td class="metric-card">
                <div class="metric-label">Barang Keluar</div>
                <div class="metric-value">-{{ $summary['outgoing_items'] }} Pcs</div>
            </td>
        </tr>
    </table>

    <p class="section-title">Penjualan dan Profit Estimasi</p>

    <table class="financial-grid">
        <tr>
            <td class="financial-card">
                <div class="financial-label">Omzet Penjualan</div>
                <div class="financial-value revenue">Rp {{ number_format($summary['sales_revenue'], 0, ',', '.') }}</div>
            </td>
            <td class="financial-card">
                <div class="financial-label">HPP Penjualan</div>
                <div class="financial-value">Rp {{ number_format($summary['sales_cost'], 0, ',', '.') }}</div>
            </td>
            <td class="financial-card">
                <div class="financial-label">Profit Kotor</div>
                <div class="financial-value gross-profit">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</div>
            </td>
            <td class="financial-card">
                <div class="financial-label">Profit Bersih Estimasi</div>
                <div class="financial-value {{ $estimatedProfit < 0 ? 'estimated-profit-negative' : 'estimated-profit-positive' }}">
                    Rp {{ number_format($estimatedProfit, 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="loss-grid">
        <tr>
            <td class="loss-item">
                <div class="loss-label">Rusak / Hilang</div>
                <div class="loss-value">- Rp {{ number_format($summary['damage_loss'], 0, ',', '.') }}</div>
            </td>
            <td class="loss-item">
                <div class="loss-label">Opname Minus</div>
                <div class="loss-value">- Rp {{ number_format($summary['opname_loss'], 0, ',', '.') }}</div>
            </td>
            <td class="loss-item neutral">
                <div class="loss-label">Opname Plus - Bukan Profit</div>
                <div class="loss-value">+ Rp {{ number_format($summary['opname_gain'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <p class="section-title">Rincian Transaksi Disetujui</p>

    <table class="transaction-table">
        <thead>
            <tr>
                <th width="13%">Kode</th>
                <th width="10%">Tanggal</th>
                <th width="11%">Jenis</th>
                <th width="18%">Supplier / Tujuan</th>
                <th width="28%">Produk dan Harga</th>
                <th width="12%">Nilai</th>
                <th width="8%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                @php
                    $isIncoming = $transaction->type === 'in';
                    $typeLabel = $isIncoming
                        ? 'Barang Masuk'
                        : ($transaction->outflow_category === 'sale'
                            ? 'Penjualan'
                            : 'Barang Keluar');
                    $isSale = !$isIncoming
                        && $transaction->outflow_category === 'sale';
                    $transactionValue = $transaction->items->sum(
                        fn ($item) => (int) $item->quantity * (float) (
                            $isSale
                                ? ($item->sale_unit_price ?? 0)
                                : ($item->unit_price ?? 0)
                        )
                    );
                @endphp
                <tr>
                    <td class="code">{{ $transaction->transaction_code }}</td>
                    <td>{{ $transaction->transaction_date?->format('d/m/Y') }}</td>
                    <td class="{{ $isIncoming ? 'type-in' : 'type-out' }}">{{ $typeLabel }}</td>
                    <td>
                        {{ $isIncoming
                            ? ($transaction->supplier?->name ?? '-')
                            : ($transaction->destination ?: '-') }}
                    </td>
                    <td>
                        @foreach ($transaction->items as $item)
                            <div class="product-line">
                                {{ $item->product?->name ?? 'Produk dihapus' }}
                                <br>
                                @php
                                    $itemPrice = $isSale
                                        ? (float) ($item->sale_unit_price ?? 0)
                                        : (float) ($item->unit_price ?? 0);
                                @endphp
                                <span class="muted">
                                    {{ $isIncoming ? 'Beli' : ($isSale ? 'Jual' : 'Modal') }}:
                                    Rp {{ number_format($itemPrice, 0, ',', '.') }} / Pcs
                                </span>
                            </div>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($transactionValue, 0, ',', '.') }}</td>
                    <td>{{ $transaction->items->sum('quantity') }} Pcs</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted" style="padding: 18px; text-align: center;">
                        Tidak ada transaksi yang disetujui pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Dokumen internal Stockify - Sistem Inventaris
    </p>
</body>
</html>