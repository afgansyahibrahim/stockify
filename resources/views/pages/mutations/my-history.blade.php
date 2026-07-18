@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">
        <div class="space-y-6">

            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pengajuan Saya</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Pantau status barang masuk dan keluar yang Anda ajukan.
                </p>
            </div>

            <form method="GET" action="{{ route('stock.my-history') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Transaksi</label>
                        <select name="type" class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                            <option value="">Semua Jenis</option>
                            <option value="in" @selected(request('type') === 'in')>Barang Masuk</option>
                            <option value="out" @selected(request('type') === 'out')>Barang Keluar</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu Persetujuan</option>
                            <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('stock.my-history') }}"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Daftar Pengajuan</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <div class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                                                : ($transaction->status === 'rejected'
                                                    ? 'Ditolak'
                                                    : 'Menunggu') }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tanggal transaksi: {{ $transaction->transaction_date?->format('d M Y') }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-600">
                                        @if ($transaction->type === 'in')
                                            Supplier: {{ $transaction->supplier?->name ?? '-' }}
                                        @else
                                            Tujuan: {{ $transaction->destination ?: '-' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="lg:text-right">
                                    <p class="text-sm text-slate-500">Jumlah Barang</p>
                                    <p class="text-xl font-bold text-slate-900">
                                        {{ $transaction->items->sum('quantity') }} Pcs
                                    </p>
                                </div>
                            </div>

                            @if ($transaction->status === 'rejected' && $transaction->rejection_note)
                                <div class="mt-4 rounded-lg border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                    <span class="font-semibold">Alasan penolakan:</span>
                                    {{ $transaction->rejection_note }}
                                </div>
                            @endif

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
                                </div>
                            </details>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="text-lg font-semibold text-slate-700">Belum ada pengajuan</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Buat transaksi barang masuk atau barang keluar terlebih dahulu.
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
@endsection