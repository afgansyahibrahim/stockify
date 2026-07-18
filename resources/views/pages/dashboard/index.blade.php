@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                    S
                </span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                    Stockify {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                Dashboard Inventaris
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Pantau stok, transaksi, dan pengajuan yang membutuhkan persetujuan.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stock.in') }}"
                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                + Barang Masuk
            </a>

            <a href="{{ route('stock.out') }}"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                Barang Keluar
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'staff')
        <a href="{{ route('stock.my-history') }}"
            class="mb-6 flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-blue-800 transition hover:bg-blue-100">
            <div>
                <p class="font-semibold">Pantau status pengajuan barang.</p>
                <p class="mt-1 text-sm text-blue-700">
                    Lihat transaksi yang masih menunggu, disetujui, atau ditolak.
                </p>
            </div>

            <span class="text-sm font-bold">Pengajuan Saya →</span>
        </a>
    @elseif($pendingCount > 0)
        <a href="{{ route('approvals.index') }}"
            class="mb-6 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-800 transition hover:bg-amber-100">
            <div>
                <p class="font-semibold">{{ $pendingCount }} transaksi menunggu persetujuan.</p>
                <p class="mt-1 text-sm text-amber-700">
                    Stok belum berubah sampai transaksi disetujui.
                </p>
            </div>

            <span class="text-sm font-bold">Lihat Approval →</span>
        </a>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Produk</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $totalProducts }}</p>
            <p class="mt-1 text-xs text-slate-500">Produk aktif</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Stok</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalStock) }}</p>
            <p class="mt-1 text-xs text-slate-500">Unit tersedia</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Masuk Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($stockInToday) }}</p>
            <p class="mt-1 text-xs text-slate-500">Unit disetujui</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Keluar Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-rose-600">{{ number_format($stockOutToday) }}</p>
            <p class="mt-1 text-xs text-slate-500">Unit disetujui</p>
        </div>

        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Stok Kritis</p>
            <p class="mt-2 text-3xl font-bold text-rose-700">{{ $lowStockCount }}</p>
            <p class="mt-1 text-xs text-rose-600">Produk perlu restock</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Arus Stok 7 Hari Terakhir</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Perbandingan barang masuk dan keluar yang sudah disetujui.
                </p>
            </div>

            <div id="weekly-chart" class="h-80"></div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Produk per Kategori</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Kategori dengan jumlah produk terbanyak.</p>

            <div id="category-chart" class="mt-3 h-72"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Aktivitas Terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Transaksi yang sudah disetujui.</p>
                </div>

                <a href="{{ route('stock.history') }}"
                    class="text-sm font-semibold text-blue-600">
                    Lihat semua
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-gray-700">
                @forelse($recentActivities as $activity)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold
                                {{ $activity['type'] === 'in'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : ($activity['type'] === 'out'
                                        ? 'bg-rose-100 text-rose-700'
                                        : 'bg-violet-100 text-violet-700') }}"
                            >
                                @if($activity['type'] === 'in')
                                    ↓
                                @elseif($activity['type'] === 'out')
                                    ↑
                                @else
                                    ±
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">
                                    {{ $activity['product_name'] }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $activity['code'] }}
                                    ·
                                    {{ $activity['actor'] }}

                                    @if($activity['source'] === 'adjustment')
                                        · Stock Opname
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <p
                                class="font-bold
                                {{ $activity['quantity'] < 0
                                    ? 'text-rose-600'
                                    : ($activity['quantity'] > 0
                                        ? 'text-emerald-600'
                                        : 'text-slate-500') }}"
                            >
                                {{ $activity['quantity'] > 0 ? '+' : '' }}
                                {{ $activity['quantity'] }}
                                Pcs
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ optional($activity['date'])->format('d M H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500">
                        Belum ada aktivitas stok yang disetujui.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Stok Perlu Restock</h2>
                    <p class="mt-1 text-sm text-slate-500">Di bawah batas minimum.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-gray-700">
                @forelse($lowStocks as $product)
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $product->name }}</p>
                            <p class="mt-1 font-mono text-xs text-slate-400">{{ $product->sku }}</p>
                        </div>

                        <div class="text-right">
                            <p class="font-bold text-rose-600">{{ $product->stock }} Pcs</p>
                            <p class="mt-1 text-xs text-slate-400">Min. {{ $product->minimum_stock }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500">
                        Semua stok berada pada kondisi aman.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-center dark:border-gray-700">
                @if(auth()->user()->role === 'staff')
                    <a href="{{ route('stock.in') }}"
                        class="text-sm font-semibold text-blue-600">
                        Buat Pengajuan Barang Masuk
                    </a>
                @else
                    <a href="{{ route('products.index') }}"
                        class="text-sm font-semibold text-blue-600">
                        Kelola Produk
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const weeklyOptions = {
            series: [
                {
                    name: 'Barang Masuk',
                    data: @json($weeklyIn),
                },
                {
                    name: 'Barang Keluar',
                    data: @json($weeklyOut),
                },
            ],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
            },
            colors: ['#10b981', '#ef4444'],
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.25,
                    opacityTo: 0.02,
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($weeklyLabels),
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
            },
        };

        new ApexCharts(document.querySelector('#weekly-chart'), weeklyOptions).render();

        const categoryOptions = {
            series: @json($categories->pluck('products_count')),
            labels: @json($categories->pluck('name')),
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Inter, sans-serif',
            },
            colors: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
        };

        new ApexCharts(document.querySelector('#category-chart'), categoryOptions).render();
    });
</script>
@endsection