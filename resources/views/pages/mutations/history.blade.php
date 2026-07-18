@extends('layouts.dashboard')
@section('content')
@php
    $isStaff = auth()->user()->role === 'staff';
@endphp
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">
    <div class="space-y-6">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                    {{ $isStaff ? 'Riwayat Stok' : 'Riwayat Mutasi' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    @if($isStaff)
                        Lihat pergerakan barang masuk dan keluar yang telah disetujui.
                    @else
                        Pantau seluruh transaksi dan perubahan stok inventaris.
                    @endif
                </p>
            </div>

            <a href="{{ route('stock.history') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                ↻ Reset Filter
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 {{ $isStaff ? 'xl:grid-cols-3' : 'xl:grid-cols-4' }}">
            <div class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['total'] }}</p>
            </div>

            @if(!$isStaff)
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-5">
                    <p class="text-sm font-medium text-amber-700">
                        Menunggu Persetujuan
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-700">
                        {{ $summary['pending'] }}
                    </p>
                </div>
            @endif

            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-700">Barang Masuk Disetujui</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">+{{ $summary['incoming'] }}</p>
            </div>

            <div class="rounded-xl border border-rose-100 bg-rose-50 p-5">
                <p class="text-sm font-medium text-rose-700">Barang Keluar Disetujui</p>
                <p class="mt-2 text-3xl font-bold text-rose-700">-{{ $summary['outgoing'] }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('stock.history') }}"
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 {{ $isStaff ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }}">
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Cari Transaksi</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Kode transaksi, supplier, tujuan, atau pembuat..."
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Transaksi</label>
                    <select name="type"
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Jenis</option>
                        <option value="in" @selected(request('type') === 'in')>Barang Masuk</option>
                        <option value="out" @selected(request('type') === 'out')>Barang Keluar</option>
                    </select>
                </div>

                @if(!$isStaff)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Status
                        </label>

                        <select name="status"
                            class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>
                                Menunggu
                            </option>
                            <option value="approved" @selected(request('status') === 'approved')>
                                Disetujui
                            </option>
                            <option value="rejected" @selected(request('status') === 'rejected')>
                                Ditolak
                            </option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Filter Periode</label>
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
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="period-field" data-period="month">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Bulan</label>
                    <input type="month" name="period_month" value="{{ request('period_month') }}"
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="period-field" data-period="year">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tahun</label>
                    <input type="number" name="period_year" min="2020" max="{{ now()->year }}"
                        value="{{ request('period_year') }}" placeholder="{{ now()->year }}"
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="period-field" data-period="range">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="period-field" data-period="range">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
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
                <h2 class="font-bold text-slate-900">Daftar Transaksi</h2>
            </div>

            @forelse ($transactions as $transaction)
                <div class="border-b border-slate-100 p-5 last:border-0">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full
                                {{ $transaction->type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $transaction->type === 'in' ? '↓' : '↑' }}
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-bold text-slate-900">{{ $transaction->transaction_code }}</p>

                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $transaction->type === 'in'
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-rose-100 text-rose-700' }}">
                                        {{ $transaction->type === 'in' ? 'Barang Masuk' : 'Barang Keluar' }}
                                    </span>

                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $transaction->status === 'approved'
                                            ? 'bg-blue-100 text-blue-700'
                                            : ($transaction->status === 'rejected'
                                                ? 'bg-rose-100 text-rose-700'
                                                : 'bg-amber-100 text-amber-700') }}">
                                        {{ $transaction->status === 'approved'
                                            ? 'Disetujui'
                                            : ($transaction->status === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $transaction->transaction_date?->format('d M Y') }}
                                    · Dibuat oleh {{ $transaction->creator?->name ?? '-' }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    @if ($transaction->type === 'in')
                                        Supplier: {{ $transaction->supplier?->name ?? '-' }}
                                    @else
                                        Tujuan: {{ $transaction->destination ?: '-' }}
                                    @endif

                                    @if ($transaction->reference_number)
                                        · Referensi: {{ $transaction->reference_number }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="lg:text-right">
                            <p class="text-sm text-slate-500">Jumlah Barang</p>
                            <p class="text-xl font-bold text-slate-900">
                                {{ $transaction->items->sum('quantity') }} Item
                            </p>
                        </div>
                    </div>

                    <details class="mt-4 rounded-lg bg-slate-50 px-4 py-3">
                        <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                            Lihat rincian produk
                        </summary>

                        <div class="mt-3 space-y-2">
                            @foreach ($transaction->items as $item)
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2 text-sm last:border-0 last:pb-0">
                                    <span class="font-medium text-slate-700">
                                        {{ $item->product?->name ?? 'Produk sudah dihapus' }}
                                    </span>
                                    <span class="font-semibold text-slate-900">
                                        {{ $item->quantity }} Pcs
                                    </span>
                                </div>
                            @endforeach

                            @if ($transaction->notes)
                                <p class="border-t border-slate-200 pt-3 text-sm text-slate-500">
                                    Catatan: {{ $transaction->notes }}
                                </p>
                            @endif

                            @if(
                                !$isStaff &&
                                $transaction->status === 'rejected' &&
                                $transaction->rejection_note
                            )
                                <p class="border-t border-rose-200 pt-3 text-sm text-rose-600">
                                    Alasan penolakan: {{ $transaction->rejection_note }}
                                </p>
                            @endif
                        </div>
                    </details>
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-lg font-semibold text-slate-700">Transaksi tidak ditemukan</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Coba ubah filter atau buat transaksi barang masuk/keluar baru.
                    </p>
                </div>
            @endforelse

            @if ($transactions->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
        {{-- Riwayat penyesuaian Stock Opname --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-900">
                    Penyesuaian Stock Opname
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Riwayat perubahan stok berdasarkan hasil penghitungan fisik.
                </p>
            </div>

            <span class="mt-2 inline-flex w-fit rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700 sm:mt-0">
                {{ $adjustments->total() }} Penyesuaian
            </span>
        </div>
    </div>

    @forelse($adjustments as $adjustment)
        <div class="border-b border-slate-100 p-5 last:border-0">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-violet-100 font-bold text-violet-700">
                        ±
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-slate-900">
                                {{ $adjustment->product?->name ?? 'Produk tidak ditemukan' }}
                            </p>

                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                Penyesuaian Opname
                            </span>

                            <span class="font-mono text-xs font-semibold text-blue-600">
                                {{ $adjustment->stockOpname?->opname_code ?? '-' }}
                            </span>
                        </div>

                        <p class="mt-1 font-mono text-xs text-slate-400">
                            {{ $adjustment->product?->sku ?? '-' }}
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ optional($adjustment->adjusted_at)->format('d M Y H:i') }}
                            · Disetujui oleh
                            {{ $adjustment->approver?->name ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5 rounded-lg bg-slate-50 px-4 py-3 text-center">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Sebelum
                        </p>

                        <p class="mt-1 font-bold text-slate-900">
                            {{ $adjustment->stock_before }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Sesudah
                        </p>

                        <p class="mt-1 font-bold text-slate-900">
                            {{ $adjustment->stock_after }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Selisih
                        </p>

                        <p class="mt-1 font-bold
                            {{ $adjustment->difference < 0
                                ? 'text-rose-600'
                                : ($adjustment->difference > 0
                                    ? 'text-emerald-600'
                                    : 'text-slate-500') }}">
                            {{ $adjustment->difference > 0 ? '+' : '' }}
                            {{ $adjustment->difference }}
                        </p>
                    </div>
                </div>
            </div>

            <details class="mt-4 rounded-lg bg-slate-50 px-4 py-3">
                <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                    Lihat detail opname
                </summary>

                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-slate-400">Pembuat Opname</p>
                        <p class="mt-1 font-semibold text-slate-700">
                            {{ $adjustment->stockOpname?->creator?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-400">Tanggal Opname</p>
                        <p class="mt-1 font-semibold text-slate-700">
                            {{ optional($adjustment->stockOpname?->opname_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>

                    @if($adjustment->stockOpname?->notes)
                        <div class="sm:col-span-2">
                            <p class="text-slate-400">Catatan Opname</p>
                            <p class="mt-1 font-semibold text-slate-700">
                                {{ $adjustment->stockOpname->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </details>
        </div>
    @empty
        <div class="p-10 text-center">
            <p class="text-lg font-semibold text-slate-700">
                Belum ada penyesuaian stock opname
            </p>

            <p class="mt-1 text-sm text-slate-500">
                Penyesuaian akan muncul setelah stock opname disetujui.
            </p>
        </div>
    @endforelse

    @if($adjustments->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $adjustments->links() }}
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