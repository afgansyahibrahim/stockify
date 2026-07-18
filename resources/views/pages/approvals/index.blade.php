@extends('layouts.dashboard')

@section('content')
@php
    $pendingCount = $transactions->where('status', 'pending')->count();
@endphp

<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6">
        <div class="mb-2 flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">✓</span>
            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Kontrol Transaksi</span>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
            Persetujuan Transaksi
        </h1>

        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
            Setujui atau tolak pengajuan sebelum stok inventaris diperbarui.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Menunggu Persetujuan</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ $pendingCount }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang Masuk Pending</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">
                {{ $transactions->where('status', 'pending')->where('type', 'in')->count() }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang Keluar Pending</p>
            <p class="mt-2 text-3xl font-bold text-rose-600">
                {{ $transactions->where('status', 'pending')->where('type', 'out')->count() }}
            </p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($transactions as $transaction)
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $transaction->type === 'in' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $transaction->type === 'in' ? '+' : '−' }}
                            </span>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-mono text-sm font-bold text-blue-600">
                                        {{ $transaction->transaction_code }}
                                    </p>

                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $transaction->type === 'in' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $transaction->type === 'in' ? 'Barang Masuk' : 'Barang Keluar' }}
                                    </span>

                                    @if($transaction->status === 'pending')
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Pending
                                        </span>
                                    @elseif($transaction->status === 'approved')
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                            Ditolak
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 grid grid-cols-1 gap-x-6 gap-y-1 text-sm text-slate-500 sm:grid-cols-2">
                                    <p>Pengaju: <span class="font-semibold text-slate-700">{{ $transaction->creator?->name }}</span></p>
                                    <p>Tanggal: <span class="font-semibold text-slate-700">{{ $transaction->transaction_date->format('d M Y') }}</span></p>
                                    <p>
                                        {{ $transaction->type === 'in' ? 'Supplier' : 'Tujuan' }}:
                                        <span class="font-semibold text-slate-700">
                                            {{ $transaction->type === 'in' ? $transaction->supplier?->name : $transaction->destination }}
                                        </span>
                                    </p>
                                    <p>Referensi: <span class="font-semibold text-slate-700">{{ $transaction->reference_number ?: '-' }}</span></p>
                                </div>
                            </div>
                        </div>

                        @if($transaction->status === 'pending')
                            <div class="flex flex-wrap gap-2">
                                <form action="{{ route('approvals.approve', $transaction) }}" method="POST">
                                    @csrf

                                    <button type="submit"
                                        onclick="return confirm('Setujui transaksi ini? Stok akan langsung diperbarui.')"
                                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                        Setujui
                                    </button>
                                </form>

                                <button type="button"
                                    class="open-reject-modal rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700"
                                    data-id="{{ $transaction->id }}"
                                    data-code="{{ $transaction->transaction_code }}">
                                    Tolak
                                </button>
                            </div>
                        @endif
                    </div>

                    <details class="mt-5 border-t border-slate-100 pt-4">
                        <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                            Lihat detail produk dan catatan
                        </summary>

                        <div class="mt-4 space-y-3">
                            @foreach($transaction->items as $item)
                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $item->product?->name }}</p>
                                        <p class="mt-1 font-mono text-xs text-slate-400">{{ $item->product?->sku }}</p>
                                    </div>

                                    <p class="font-bold {{ $transaction->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $transaction->type === 'in' ? '+' : '-' }}{{ $item->quantity }} Pcs
                                    </p>
                                </div>
                            @endforeach

                            <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                                <span class="font-semibold text-slate-800">Catatan:</span>
                                {{ $transaction->notes ?: 'Tidak ada catatan.' }}
                            </div>

                            @if($transaction->status === 'rejected')
                                <div class="rounded-lg bg-rose-50 p-4 text-sm text-rose-700">
                                    <span class="font-semibold">Alasan Penolakan:</span>
                                    {{ $transaction->rejection_note }}
                                </div>
                            @endif
                        </div>
                    </details>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-16 text-center">
                <p class="font-semibold text-slate-700">Belum ada transaksi.</p>
                <p class="mt-1 text-sm text-slate-500">Pengajuan Barang Masuk atau Barang Keluar akan tampil di halaman ini.</p>
            </div>
        @endforelse
    </div>
</div>

<div id="reject-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-2xl">
        <form id="reject-form" method="POST">
            @csrf

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Tolak Transaksi</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Berikan alasan penolakan untuk <span id="reject-code" class="font-mono font-semibold"></span>.
                </p>
            </div>

            <div class="p-5">
                <textarea name="rejection_note" rows="4" required
                    class="block w-full rounded-lg border border-rose-200 px-3 py-2.5 text-sm text-slate-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-100"
                    placeholder="Tuliskan alasan penolakan."></textarea>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4">
                <button id="close-reject-modal" type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600">
                    Batal
                </button>

                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white">
                    Tolak Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('reject-modal');
        const form = document.getElementById('reject-form');

        document.querySelectorAll('.open-reject-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                form.action = '{{ url('approvals') }}/' + button.dataset.id + '/reject';
                document.getElementById('reject-code').textContent = button.dataset.code;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.getElementById('close-reject-modal').addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    });
</script>
@endsection