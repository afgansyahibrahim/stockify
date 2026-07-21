@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                Pengendalian Persediaan
            </p>

            <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                Stock Opname
            </h1>

            <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                Catat stok fisik dan bandingkan dengan stok pada sistem.
            </p>
        </div>

        <a href="{{ route('stock-opnames.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
            + Buat Stock Opname
        </a>
    </div>

    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Alert error --}}
    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Total Opname
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                {{ $summary['total'] }}
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                Menunggu
            </p>

            <p class="mt-2 text-3xl font-bold text-amber-700">
                {{ $summary['pending'] }}
            </p>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                Disetujui
            </p>

            <p class="mt-2 text-3xl font-bold text-emerald-700">
                {{ $summary['approved'] }}
            </p>
        </div>

        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">
                Ditolak
            </p>

            <p class="mt-2 text-3xl font-bold text-rose-700">
                {{ $summary['rejected'] }}
            </p>
        </div>
    </div>

{{-- TEMPel FORM FILTER DI SINI --}}
<form
    method="GET"
    action="{{ route('stock-opnames.index') }}"
    class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800"
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="xl:col-span-2">
            <label
                for="search"
                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
            >
                Cari Stock Opname
            </label>

            <input
                type="text"
                id="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Kode opname, produk, SKU, atau pembuat..."
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
        </div>

        <div>
            <label
                for="status"
                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
            >
                Status
            </label>

            <select
                id="status"
                name="status"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
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

        <div>
            <label
                for="date"
                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
            >
                Tanggal Opname
            </label>

            <input
                type="date"
                id="date"
                name="date"
                value="{{ request('date') }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
        </div>
    </div>

    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('stock-opnames.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
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

    {{-- Daftar opname --}}
    <div class="space-y-4">
        @forelse($opnames as $opname)
            @php
                $totalDifference = $opname->items->sum('difference');
                $totalProducts = $opname->items->count();
                $adjustmentsByProduct = $opname->adjustments->keyBy('product_id');

                $statusClasses = match($opname->status) {
                    'approved' => 'bg-emerald-100 text-emerald-700',
                    'rejected' => 'bg-rose-100 text-rose-700',
                    default => 'bg-amber-100 text-amber-700',
                };

                $statusLabel = match($opname->status) {
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    default => 'Menunggu',
                };
            @endphp

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                {{-- Header kartu --}}
                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between dark:border-gray-700">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-sm font-bold text-blue-600">
                                {{ $opname->opname_code }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-gray-400">
                            <span>
                                Tanggal:
                                <strong class="text-slate-700 dark:text-gray-200">
                                    {{ optional($opname->opname_date)->format('d M Y') }}
                                </strong>
                            </span>

                            <span>
                                Dibuat oleh:
                                <strong class="text-slate-700 dark:text-gray-200">
                                    {{ $opname->creator?->name ?? '-' }}
                                </strong>
                            </span>

                            <span>
                                Produk:
                                <strong class="text-slate-700 dark:text-gray-200">
                                    {{ $totalProducts }}
                                </strong>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        {{-- Approval hanya Admin dan Manager --}}
                        @if(
                            in_array(auth()->user()->role, ['admin', 'manager']) &&
                            $opname->status === 'pending'
                        )
                            <form
                                method="POST"
                                action="{{ route('stock-opnames.approve', $opname) }}"
                                data-stockify-confirm="Setujui stock opname ini dan sesuaikan stok produk?"
                                data-stockify-confirm-title="Setujui stock opname"
                                data-stockify-confirm-label="Ya, Setujui"
                            >
                                @csrf

                                <button type="submit"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    Setujui
                                </button>
                            </form>

                            <button
                                type="button"
                                onclick="openRejectModal({{ $opname->id }})"
                                class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">
                                Tolak
                            </button>
                        @endif

                        {{-- Hapus opname yang belum disetujui --}}
                        @if($opname->status !== 'approved')
                            <form
                                method="POST"
                                action="{{ route('stock-opnames.destroy', $opname) }}"
                                data-stockify-confirm="Hapus stock opname ini?"
                                data-stockify-confirm-title="Hapus stock opname"
                                data-stockify-confirm-label="Ya, Hapus"
                                data-stockify-confirm-variant="danger"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Ringkasan selisih --}}
                <div class="grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Total Produk
                        </p>

                        <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            {{ $totalProducts }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Total Selisih
                        </p>

                        <p class="mt-1 text-lg font-bold
                            {{ $totalDifference < 0 ? 'text-rose-600' : ($totalDifference > 0 ? 'text-emerald-600' : 'text-slate-700') }}">
                            {{ $totalDifference > 0 ? '+' : '' }}{{ $totalDifference }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Pemeriksa
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-gray-200">
                            {{ $opname->approver?->name ?? 'Belum diperiksa' }}
                        </p>
                    </div>
                </div>

                {{-- Detail produk --}}
                <details class="border-t border-slate-100 dark:border-gray-700">
                    <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-blue-600">
                        Lihat detail produk
                    </summary>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-gray-700">
                            <thead class="bg-slate-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-gray-300">
                                        Produk
                                    </th>

                                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-gray-300">
                                        Stok Sistem
                                    </th>

                                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-gray-300">
                                        Stok Fisik
                                    </th>

                                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-gray-300">
                                        Selisih
                                    </th>

                                    <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-gray-300">
                                        Harga Modal
                                    </th>

                                    <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-gray-300">
                                        Dampak Nilai
                                    </th>

                                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-gray-300">
                                        Catatan
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                                @foreach($opname->items as $item)
                                    @php
                                        $adjustment = $adjustmentsByProduct->get($item->product_id);
                                        $unitCost = (float) ($adjustment?->unit_cost ?? 0);
                                        $adjustmentValue = abs((int) $item->difference) * $unitCost;
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ $item->product?->name ?? 'Produk tidak ditemukan' }}
                                            </p>

                                            <p class="mt-1 font-mono text-xs text-slate-400">
                                                {{ $item->product?->sku }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-center font-semibold text-slate-700 dark:text-gray-200">
                                            {{ $item->system_stock }}
                                        </td>

                                        <td class="px-5 py-4 text-center font-semibold text-slate-700 dark:text-gray-200">
                                            {{ $item->physical_stock }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span class="font-bold
                                                {{ $item->difference < 0 ? 'text-rose-600' : ($item->difference > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right font-semibold text-slate-700 dark:text-gray-200">
                                            @if($opname->status === 'approved' && $adjustment)
                                                Rp {{ number_format($unitCost, 0, ',', '.') }}
                                            @elseif($opname->status === 'approved')
                                                Rp 0
                                            @else
                                                <span class="text-xs font-normal text-slate-400">Setelah disetujui</span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-right font-semibold">
                                            @if($opname->status !== 'approved')
                                                <span class="text-xs font-normal text-slate-400">Belum tersedia</span>
                                            @elseif($item->difference < 0)
                                                <span class="text-rose-600">- Rp {{ number_format($adjustmentValue, 0, ',', '.') }}</span>
                                            @elseif($item->difference > 0)
                                                <span class="text-emerald-600">+ Rp {{ number_format($adjustmentValue, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-slate-500">Rp 0</span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-slate-500 dark:text-gray-400">
                                            {{ $item->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>

                {{-- Catatan umum --}}
                @if($opname->notes)
                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-4 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <p class="font-semibold text-slate-700 dark:text-gray-200">
                            Catatan
                        </p>

                        <p class="mt-1 text-slate-500 dark:text-gray-400">
                            {{ $opname->notes }}
                        </p>
                    </div>
                @endif

                {{-- Alasan penolakan --}}
                @if($opname->status === 'rejected' && $opname->rejection_note)
                    <div class="border-t border-rose-200 bg-rose-50 px-5 py-4 text-sm">
                        <p class="font-semibold text-rose-700">
                            Alasan Penolakan
                        </p>

                        <p class="mt-1 text-rose-600">
                            {{ $opname->rejection_note }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-800">
                @if(request()->hasAny(['search', 'status', 'date']))
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        Stock opname tidak ditemukan
                    </h2>

                    <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                        Coba ubah kata pencarian atau filter yang digunakan.
                    </p>

                    <a
                        href="{{ route('stock-opnames.index') }}"
                        class="mt-5 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    >
                        Reset Filter
                    </a>
                @else
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        Belum ada stock opname
                    </h2>

                    <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                        Buat stock opname untuk mulai mencatat hasil penghitungan stok fisik.
                    </p>

                    <a
                        href="{{ route('stock-opnames.create') }}"
                        class="mt-5 inline-flex rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        + Buat Stock Opname
                    </a>
                @endif
            </div>
                    @endforelse
    </div>

    {{-- Pagination --}}
    @if($opnames->hasPages())
        <div class="mt-6">
            {{ $opnames->links() }}
        </div>
    @endif
</div>

{{-- Modal penolakan --}}
<div
    id="rejectModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4"
>
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Tolak Stock Opname
            </h2>

            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Tuliskan alasan penolakan secara jelas.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf

            <label
                for="rejection_note"
                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
            >
                Alasan Penolakan
            </label>

            <textarea
                id="rejection_note"
                name="rejection_note"
                rows="4"
                required
                maxlength="1000"
                class="w-full rounded-lg border border-slate-300 p-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Contoh: Jumlah fisik perlu dihitung ulang."
            ></textarea>

            <div class="mt-5 flex justify-end gap-2">
                <button
                    type="button"
                    onclick="closeRejectModal()"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
                >
                    Tolak Opname
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(opnameId) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const routeTemplate = @json(route('stock-opnames.reject', ':id'));

        form.action = routeTemplate.replace(':id', opnameId);

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('rejection_note').focus();
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.getElementById('rejection_note').value = '';
    }
</script>
@endsection