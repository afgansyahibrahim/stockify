<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 11px;
        }

        h1 {
            margin: 0;
            color: #1d4ed8;
            font-size: 22px;
        }

        .subtitle {
            margin: 5px 0 22px;
            color: #64748b;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary td {
            width: 33.33%;
            padding: 10px;
            border: 1px solid #dbeafe;
        }

        .summary-title {
            color: #64748b;
            font-size: 10px;
        }

        .summary-value {
            margin-top: 5px;
            color: #0f172a;
            font-size: 17px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 9px;
            text-align: left;
        }

        td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .in {
            color: #047857;
            font-weight: bold;
        }

        .out {
            color: #be123c;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            color: #64748b;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Laporan Inventaris Stockify</h1>
    <p class="subtitle">
        {{ $periodLabel }} · Dicetak: {{ now()->format('d M Y H:i') }}
    </p>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-title">Transaksi Disetujui</div>
                <div class="summary-value">{{ $summary['transactions'] }}</div>
            </td>
            <td>
                <div class="summary-title">Total Barang Masuk</div>
                <div class="summary-value">+{{ $summary['incoming_items'] }} Pcs</div>
            </td>
            <td>
                <div class="summary-title">Total Barang Keluar</div>
                <div class="summary-value">-{{ $summary['outgoing_items'] }} Pcs</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="15%">Kode</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Jenis</th>
                <th width="24%">Supplier / Tujuan</th>
                <th width="22%">Produk</th>
                <th width="12%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_code }}</td>
                    <td>{{ $transaction->transaction_date?->format('d/m/Y') }}</td>
                    <td class="{{ $transaction->type === 'in' ? 'in' : 'out' }}">
                        {{ $transaction->type === 'in' ? 'Barang Masuk' : 'Barang Keluar' }}
                    </td>
                    <td>
                        {{ $transaction->type === 'in'
                            ? ($transaction->supplier?->name ?? '-')
                            : ($transaction->destination ?: '-') }}
                    </td>
                    <td>
                        @foreach ($transaction->items as $item)
                            {{ $item->product?->name ?? 'Produk dihapus' }}<br>
                        @endforeach
                    </td>
                    <td>{{ $transaction->items->sum('quantity') }} Pcs</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Stockify Inventory System
    </p>
</body>
</html>