@extends('layouts.dashboard')

@section('content')
@php
    $actionLabels = [
        'transaction.created' => 'Pengajuan Transaksi Dibuat',
        'transaction.approved' => 'Transaksi Disetujui',
        'transaction.rejected' => 'Transaksi Ditolak',
        'product.created' => 'Produk Dibuat',
        'product.updated' => 'Produk Diubah',
        'product.deactivated' => 'Produk Dinonaktifkan',
        'product.activated' => 'Produk Diaktifkan',
        'product.deleted' => 'Produk Dihapus',
        'stock_opname.created' => 'Stock Opname Dibuat',
        'stock_opname.approved' => 'Stock Opname Disetujui',
        'stock_opname.rejected' => 'Stock Opname Ditolak',
        'stock_opname.deleted' => 'Stock Opname Dihapus',
    ];

    $subjectLabels = [
        'App\\Models\\Product' => 'Produk',
        'App\\Models\\StockTransaction' => 'Transaksi Stok',
        'App\\Models\\StockOpname' => 'Stock Opname',
    ];

    $fieldLabels = [
        'name' => 'Nama Produk',
        'sku' => 'SKU',
        'category_id' => 'Kategori',
        'supplier_id' => 'Supplier',
        'stock' => 'Stok',
        'minimum_stock' => 'Stok Minimum',
        'selling_price' => 'Harga Jual',
        'is_active' => 'Status Produk',
        'attributes' => 'Atribut Produk',
        'type' => 'Jenis Transaksi',
        'outflow_category' => 'Kategori Barang Keluar',
        'destination' => 'Tujuan',
        'transaction_date' => 'Tanggal Transaksi',
        'total_products' => 'Jumlah Jenis Produk',
        'total_quantity' => 'Jumlah Unit',
        'status' => 'Status',
        'adjustment_type' => 'Jenis Penyesuaian',
        'opname_date' => 'Tanggal Opname',
        'approved_by' => 'Diproses Oleh',
        'created_by' => 'Dibuat Oleh',
        'updated_by' => 'Diubah Oleh',
        'rejection_note' => 'Catatan Penolakan',
    ];

    $formatActivityValue = function (string $key, mixed $value) use ($referenceNames): string {
        if ($value === null || $value === '') {
            return '—';
        }

        if (in_array($key, ['approved_by', 'created_by', 'updated_by'], true)) {
            return $referenceNames['users'][$value] ?? 'Pengguna tidak tersedia';
        }

        if ($key === 'supplier_id') {
            return $referenceNames['suppliers'][$value] ?? 'Supplier tidak tersedia';
        }

        if ($key === 'category_id') {
            return $referenceNames['categories'][$value] ?? 'Kategori tidak tersedia';
        }

        if ($key === 'status') {
            return [
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
            ][$value] ?? (string) $value;
        }

        if ($key === 'type') {
            return [
                'in' => 'Barang Masuk',
                'out' => 'Barang Keluar',
            ][$value] ?? (string) $value;
        }

        if ($key === 'outflow_category') {
            return [
                'sale' => 'Penjualan',
                'operational' => 'Operasional',
                'damage' => 'Rusak/Hilang',
                'other' => 'Lainnya',
            ][$value] ?? (string) $value;
        }

        if ($key === 'adjustment_type') {
            return [
                'opname' => 'Stock Opname',
                'damage_loss' => 'Barang Rusak/Hilang',
            ][$value] ?? (string) $value;
        }

        if ($key === 'is_active') {
            return $value ? 'Aktif' : 'Nonaktif';
        }

        if (in_array($key, ['selling_price'], true) && is_numeric($value)) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        }

        if (in_array($key, ['stock', 'minimum_stock', 'total_quantity'], true) && is_numeric($value)) {
            return number_format((float) $value, 0, ',', '.') . ' pcs';
        }

        if (in_array($key, ['total_products'], true) && is_numeric($value)) {
            return number_format((float) $value, 0, ',', '.') . ' produk';
        }

        if (in_array($key, ['transaction_date', 'opname_date'], true)) {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($key === 'attributes' && is_array($value)) {
            $attributes = collect($value)
                ->map(function ($attribute) {
                    if (! is_array($attribute)) {
                        return (string) $attribute;
                    }

                    $name = $attribute['name'] ?? 'Atribut';
                    $attributeValue = $attribute['value'] ?? '—';

                    return $name . ': ' . $attributeValue;
                })
                ->filter()
                ->implode(', ');

            return $attributes !== '' ? $attributes : '—';
        }

        if (is_array($value)) {
            return collect($value)
                ->map(function ($item) {
                    return is_scalar($item) ? (string) $item : 'Data tersedia';
                })
                ->implode(', ');
        }

        return (string) $value;
    };
@endphp
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    {{-- Header --}}
    <div class="mb-6">
        <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
            Keamanan Sistem
        </p>

        <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
            Log Aktivitas
        </h1>

        <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
            Riwayat tindakan pengguna pada sistem Stockify.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Total Aktivitas
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                {{ $summary['total'] }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                Aktivitas Hari Ini
            </p>

            <p class="mt-2 text-3xl font-bold text-blue-700">
                {{ $summary['today'] }}
            </p>
        </div>

        <div class="rounded-xl border border-violet-200 bg-violet-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">
                Approval dan Penolakan
            </p>

            <p class="mt-2 text-3xl font-bold text-violet-700">
                {{ $summary['approvals'] }}
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <form
        method="GET"
        action="{{ route('activity-logs.index') }}"
        class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800"
    >
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">

            <div class="lg:col-span-2">
                <label
                    for="search"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
                >
                    Cari Aktivitas
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    placeholder="Pengguna, tindakan, atau deskripsi..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
            </div>

            <div>
                <label
                    for="action"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
                >
                    Jenis Tindakan
                </label>

                <select
                    id="action"
                    name="action"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">
                        Semua Tindakan
                    </option>

                    @foreach($actions as $action)
                        <option
                            value="{{ $action }}"
                            @selected(request('action') === $action)
                        >
                            {{ $action }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="date"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
                >
                    Tanggal
                </label>

                <input
                    id="date"
                    name="date"
                    type="date"
                    value="{{ request('date') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('activity-logs.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-gray-600 dark:text-gray-200"
            >
                Reset
            </a>

            <button
                type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
            >
                Terapkan Filter
            </button>
        </div>
    </form>

    {{-- Daftar log --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="divide-y divide-slate-100 dark:divide-gray-700">
            @forelse($logs as $log)
                @php
                    $oldValues = is_array($log->old_values) ? $log->old_values : [];
                    $newValues = is_array($log->new_values) ? $log->new_values : [];
                    $changedKeys = array_values(array_unique([
                        ...array_keys($oldValues),
                        ...array_keys($newValues),
                    ]));

                    $actionClass = match(true) {
                        str_contains($log->action, 'approved') =>
                            'bg-emerald-100 text-emerald-700',

                        str_contains($log->action, 'rejected') =>
                            'bg-rose-100 text-rose-700',

                        str_contains($log->action, 'created') =>
                            'bg-blue-100 text-blue-700',

                        str_contains($log->action, 'updated') =>
                            'bg-amber-100 text-amber-700',

                        str_contains($log->action, 'deleted') ||
                        str_contains($log->action, 'deactivated') =>
                            'bg-slate-200 text-slate-700',

                        default =>
                            'bg-violet-100 text-violet-700',
                    };

                    $actionLabel = $actionLabels[$log->action]
                        ?? \Illuminate\Support\Str::headline(str_replace('.', ' ', $log->action));

                    $subjectLabel = $subjectLabels[$log->subject_type]
                        ?? \Illuminate\Support\Str::headline(
                            class_basename($log->subject_type ?? 'Tidak tersedia')
                        );
                @endphp

                <div class="p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $actionClass }}">
                                    {{ $actionLabel }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    #{{ $log->id }}
                                </span>
                            </div>

                            <p class="mt-3 font-semibold text-slate-900 dark:text-white">
                                {{ $log->description }}
                            </p>

                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-gray-400">
                                <span>
                                    Pengguna:
                                    <strong class="text-slate-700 dark:text-gray-200">
                                        {{ $log->user?->name ?? 'Sistem' }}
                                    </strong>
                                </span>

                                <span>
                                    Email:
                                    <strong class="text-slate-700 dark:text-gray-200">
                                        {{ $log->user?->email ?? '-' }}
                                    </strong>
                                </span>

                                <span>
                                    Waktu:
                                    <strong class="text-slate-700 dark:text-gray-200">
                                        {{ $log->created_at->format('d M Y H:i:s') }}
                                    </strong>
                                </span>

                                <span>
                                    IP:
                                    <strong class="font-mono text-slate-700 dark:text-gray-200">
                                        {{ $log->ip_address ?? '-' }}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0 text-sm text-slate-500">
                            <p>
                                Objek:
                                <strong class="text-slate-700 dark:text-gray-200">
                                    {{ $subjectLabel }}
                                </strong>
                            </p>

                            <p class="mt-1">
                                ID Objek:
                                <strong class="text-slate-700 dark:text-gray-200">
                                    {{ $log->subject_id ?? '-' }}
                                </strong>
                            </p>
                        </div>
                    </div>

                    @if(count($changedKeys))
                        <details class="mt-4 rounded-lg bg-slate-50 p-4 dark:bg-gray-900">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-blue-600">
                                <span>Lihat detail perubahan</span>

                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ count($changedKeys) }} bidang
                                </span>
                            </summary>

                            <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                <div class="hidden grid-cols-[minmax(9rem,0.8fr)_minmax(10rem,1fr)_minmax(10rem,1fr)] border-b border-slate-200 bg-slate-100 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 sm:grid">
                                    <span>Bidang</span>
                                    <span>Sebelum</span>
                                    <span>Sesudah</span>
                                </div>

                                <dl class="divide-y divide-slate-100 dark:divide-gray-700">
                                    @foreach($changedKeys as $key)
                                        <div class="grid grid-cols-1 gap-3 px-4 py-3 sm:grid-cols-[minmax(9rem,0.8fr)_minmax(10rem,1fr)_minmax(10rem,1fr)] sm:gap-4">
                                            <dt class="text-sm font-semibold text-slate-700 dark:text-gray-200">
                                                {{ $fieldLabels[$key] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $key)) }}
                                            </dt>

                                            <dd class="text-sm text-slate-600 dark:text-gray-300">
                                                <span class="mr-2 text-xs font-semibold uppercase tracking-wide text-slate-400 sm:hidden">
                                                    Sebelum:
                                                </span>
                                                {{ $formatActivityValue($key, $oldValues[$key] ?? null) }}
                                            </dd>

                                            <dd class="text-sm font-medium text-slate-800 dark:text-white">
                                                <span class="mr-2 text-xs font-semibold uppercase tracking-wide text-slate-400 sm:hidden">
                                                    Sesudah:
                                                </span>
                                                {{ $formatActivityValue($key, $newValues[$key] ?? null) }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </details>
                    @endif
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <p class="font-semibold text-slate-700 dark:text-gray-200">
                        Log aktivitas tidak ditemukan.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Belum ada aktivitas atau filter terlalu spesifik.
                    </p>
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection