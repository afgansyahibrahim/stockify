@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">
        <div class="space-y-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Laporan Inventaris</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Rekap transaksi barang masuk dan keluar yang sudah disetujui.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('reports.export.pdf', request()->query()) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                        ↓ Ekspor PDF
                    </a>

                    <a href="{{ route('reports.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        ↻ Reset Filter
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Transaksi Disetujui</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['transactions'] }}</p>
                </div>

                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                    <p class="text-sm font-medium text-emerald-700">Total Barang Masuk</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">+{{ $summary['incoming_items'] }}</p>
                </div>

                <div class="rounded-xl border border-rose-100 bg-rose-50 p-5">
                    <p class="text-sm font-medium text-rose-700">Total Barang Keluar</p>
                    <p class="mt-2 text-3xl font-bold text-rose-700">-{{ $summary['outgoing_items'] }}</p>
                </div>

                <div class="rounded-xl border border-violet-100 bg-violet-50 p-5">
                    <p class="text-sm font-medium text-violet-700">Nilai Mutasi Masuk</p>
                    <p class="mt-2 text-2xl font-bold text-violet-700">
                        Rp {{ number_format($summary['total_value_in'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/60 dark:bg-blue-950/30">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-blue-950 dark:text-blue-100">Ringkasan Penjualan dan Profit Estimasi</h2>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">Hanya penjualan dan penyesuaian baru yang sudah disetujui pada periode terpilih.</p>
                    </div>
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Belum termasuk biaya operasional</span>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                        <p class="text-xs font-semibold uppercase text-slate-500">Omzet Penjualan</p>
                        <p class="mt-2 text-xl font-bold text-emerald-600">Rp {{ number_format($summary['sales_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                        <p class="text-xs font-semibold uppercase text-slate-500">HPP Penjualan</p>
                        <p class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($summary['sales_cost'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                        <p class="text-xs font-semibold uppercase text-slate-500">Profit Kotor</p>
                        <p class="mt-2 text-xl font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</p>
                    </div>
                    @php
                        $estimatedProfit = (float) $summary['estimated_profit'];
                        $profitCardClass = $estimatedProfit < 0
                            ? 'border border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/30'
                            : ($estimatedProfit > 0
                                ? 'border border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30'
                                : 'border border-slate-200 bg-slate-50 dark:border-gray-700 dark:bg-gray-800');
                        $profitTextClass = $estimatedProfit < 0
                            ? 'text-rose-700 dark:text-rose-300'
                            : ($estimatedProfit > 0
                                ? 'text-emerald-700 dark:text-emerald-300'
                                : 'text-slate-800 dark:text-white');
                    @endphp

                    <div class="rounded-lg p-4 shadow-sm {{ $profitCardClass }}">
                        <p class="text-xs font-semibold uppercase {{ $profitTextClass }}">
                            Profit Bersih Estimasi
                        </p>
                        <p class="mt-2 text-xl font-bold {{ $profitTextClass }}">
                            Rp {{ number_format($estimatedProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                    <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">Rusak/hilang: − Rp {{ number_format($summary['damage_loss'], 0, ',', '.') }}</p>
                    <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">Opname minus: − Rp {{ number_format($summary['opname_loss'], 0, ',', '.') }}</p>
                    <p class="rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-violet-700">Opname plus (bukan profit): + Rp {{ number_format($summary['opname_gain'], 0, ',', '.') }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('reports.index') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Cari Transaksi</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Kode transaksi, nomor referensi, supplier, atau tujuan..."
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Transaksi</label>
                        <select name="type"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Barang Masuk dan Keluar</option>
                            <option value="in" @selected(request('type') === 'in')>Barang Masuk</option>
                            <option value="out" @selected(request('type') === 'out')>Barang Keluar</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Periode Laporan</label>
                        <select name="period_mode" id="period_mode"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" @selected($periodMode === 'all')>Semua Waktu</option>
                            <option value="day" @selected($periodMode === 'day')>Harian</option>
                            <option value="month" @selected($periodMode === 'month')>Bulanan</option>
                            <option value="year" @selected($periodMode === 'year')>Tahunan</option>
                            <option value="range" @selected($periodMode === 'range')>Rentang Tanggal</option>
                        </select>
                    </div>

                    <div class="period-field" data-period="day">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal</label>
                        <input type="date" name="period_day" value="{{ request('period_day') }}"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div class="period-field" data-period="month">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Bulan</label>
                        <input type="month" name="period_month"
                            value="{{ request('period_month', now()->format('Y-m')) }}"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div class="period-field" data-period="year">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tahun</label>
                        <input type="number" name="period_year" min="2020" max="{{ now()->year }}"
                            value="{{ request('period_year', now()->year) }}"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div class="period-field" data-period="range">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div class="period-field" data-period="range">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Terapkan Filter
                    </button>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Rincian Laporan</h2>
                    <p class="mt-1 text-sm text-slate-500">Hanya transaksi yang telah disetujui.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        @php
                            $isSale = $transaction->type === 'out'
                                && $transaction->outflow_category === 'sale';

                            $transactionValue = $transaction->items->sum(
                                fn ($item) => (int) $item->quantity * (float) (
                                    $isSale
                                        ? ($item->sale_unit_price ?? 0)
                                        : ($item->unit_price ?? 0)
                                )
                            );

                            $transactionValueLabel = $transaction->type === 'in'
                                ? 'Nilai Pembelian'
                                : ($isSale ? 'Nilai Penjualan' : 'Nilai Modal Keluar');
                        @endphp
                        <div class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold text-slate-900">{{ $transaction->transaction_code }}</p>

                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $transaction->type === 'in'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-rose-100 text-rose-700' }}">
                                            {{ $transaction->type === 'in'
                                                ? 'Barang Masuk'
                                                : ($transaction->outflow_category === 'sale'
                                                    ? 'Penjualan'
                                                    : 'Barang Keluar') }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $transaction->transaction_date?->format('d M Y') }}
                                        · Disetujui oleh {{ $transaction->approver?->name ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ $transaction->type === 'in'
                                            ? 'Supplier: ' . ($transaction->supplier?->name ?? '-')
                                            : 'Tujuan: ' . ($transaction->destination ?: '-') }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-5 lg:justify-end lg:text-right">
                                    <div>
                                        <p class="text-sm text-slate-500">Total Barang</p>
                                        <p class="text-xl font-bold text-slate-900">
                                            {{ $transaction->items->sum('quantity') }} Pcs
                                        </p>
                                    </div>

                                    <div class="border-l border-slate-200 pl-5">
                                        <p class="text-sm text-slate-500">{{ $transactionValueLabel }}</p>
                                        <p class="text-xl font-bold {{ $isSale ? 'text-emerald-600' : 'text-slate-900' }}">
                                            Rp {{ number_format($transactionValue, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <details class="mt-4 rounded-lg bg-slate-50 px-4 py-3">
                                <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                                    Lihat produk dalam transaksi
                                </summary>

                                <div class="mt-3 space-y-2">
                                    @foreach ($transaction->items as $item)
                                        @php
                                            $itemPrice = $isSale
                                                ? (float) ($item->sale_unit_price ?? 0)
                                                : (float) ($item->unit_price ?? 0);
                                        @endphp

                                        <div class="flex flex-col gap-2 border-b border-slate-200 pb-2 text-sm last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="font-medium text-slate-700">
                                                {{ $item->product?->name ?? 'Produk sudah dihapus' }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $transaction->type === 'in'
                                                        ? 'Harga beli'
                                                        : ($isSale ? 'Harga jual' : 'Nilai modal') }}:
                                                    Rp {{ number_format($itemPrice, 0, ',', '.') }} / Pcs
                                                </p>
                                            </div>
                                            <div class="text-left sm:text-right">
                                                <p class="font-semibold text-slate-900">
                                                    {{ $item->quantity }} Pcs
                                                </p>
                                                <p class="mt-1 text-xs font-semibold {{ $isSale ? 'text-emerald-600' : 'text-slate-600' }}">
                                                    Subtotal Rp {{ number_format($item->quantity * $itemPrice, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="text-lg font-semibold text-slate-700">Belum ada data laporan</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Data akan muncul setelah transaksi disetujui.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($transactions->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const periodMode = document.getElementById('period_mode');
        const periodFields = document.querySelectorAll('.period-field');

        function updatePeriodFields() {
            periodFields.forEach((field) => {
                field.style.display = field.dataset.period === periodMode.value ? 'block' : 'none';
            });
        }

        periodMode.addEventListener('change', updatePeriodFields);
        updatePeriodFields();
    </script>
@endsection