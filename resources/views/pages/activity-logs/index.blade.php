@extends('layouts.dashboard')

@section('content')
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
                @endphp

                <div class="p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $actionClass }}">
                                    {{ $log->action }}
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
                                    {{ class_basename($log->subject_type ?? 'Tidak tersedia') }}
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

                    @if($log->old_values || $log->new_values)
                        <details class="mt-4 rounded-lg bg-slate-50 p-4 dark:bg-gray-900">
                            <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                                Lihat perubahan data
                            </summary>

                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Data Sebelum
                                    </p>

                                    <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-white p-3 text-xs text-slate-700 dark:bg-gray-800 dark:text-gray-200">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Data Sesudah
                                    </p>

                                    <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-white p-3 text-xs text-slate-700 dark:bg-gray-800 dark:text-gray-200">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                </div>
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