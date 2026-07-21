@extends('layouts.dashboard')

@section('content')
@php
    $canManageSuppliers = in_array(
        auth()->user()->role,
        ['admin', 'manager'],
        true
    );
@endphp
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">S</span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Master Data</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                Supplier
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                @if($canManageSuppliers)
                    Kelola data supplier yang memasok produk inventaris.
                @else
                    Lihat informasi supplier dan kontak pemasok produk.
                @endif
            </p>
        </div>

        @if($canManageSuppliers)
            <button
                type="button"
                id="open-supplier-modal"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
            >
                + Tambah Supplier
            </button>
        @endif
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
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Supplier</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $suppliers->count() }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Supplier Aktif</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $suppliers->where('is_active', true)->count() }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Produk Terhubung</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $suppliers->sum('products_count') }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-slate-100 p-4 dark:border-gray-700">
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m2.35-5.15a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                </svg>

                <input id="supplier-search" type="text"
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Cari supplier, email, atau telepon...">
            </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-700">
            @forelse($suppliers as $supplier)
                <div class="supplier-row px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-gray-700/40"
                    data-search="{{ strtolower($supplier->name . ' ' . $supplier->email . ' ' . $supplier->phone) }}">

                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $supplier->name }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                                    {{ $supplier->email ?: 'Email belum diisi' }} · {{ $supplier->phone ?: 'Telepon belum diisi' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-gray-700 dark:text-gray-200">
                                {{ $supplier->products_count }} Produk
                            </span>

                            @if($supplier->is_active)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Aktif
                                </span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    Nonaktif
                                </span>
                            @endif

                            @if($canManageSuppliers)
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="edit-supplier rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700"
                                        data-id="{{ $supplier->id }}"
                                        data-name="{{ $supplier->name }}"
                                        data-phone="{{ $supplier->phone }}"
                                        data-email="{{ $supplier->email }}"
                                        data-address="{{ $supplier->address }}"
                                        data-active="{{ $supplier->is_active ? '1' : '0' }}"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route('suppliers.destroy', $supplier) }}"
                                        onsubmit="return confirm('Hapus supplier ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <p class="font-semibold text-slate-700 dark:text-gray-200">Belum ada supplier.</p>
                    <p class="mt-1 text-sm text-slate-500">Tambahkan supplier pertama untuk mulai mencatat barang masuk.</p>
                </div>
            @endforelse
        </div>

        <div id="supplier-empty-result" class="hidden px-5 py-16 text-center">
            <p class="font-semibold text-slate-700 dark:text-gray-200">Supplier tidak ditemukan.</p>
        </div>
    </div>
</div>
@if($canManageSuppliers)
{{-- Modal Tambah/Edit --}}
<div id="supplier-modal" class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto bg-slate-900/50 p-4">
    <div class="my-8 w-full max-w-xl rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <form id="supplier-form" action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <input id="supplier-method" type="hidden" name="_method" value="POST">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 id="supplier-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">Tambah Supplier</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Isi data supplier.</p>
                </div>

                <button type="button" class="close-supplier-modal rounded-lg p-2 text-slate-400 hover:bg-slate-100">✕</button>
            </div>

            <div class="space-y-4 p-5">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Nama Supplier</label>
                    <input id="supplier-name-input" name="name" type="text" required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Nomor Telepon</label>
                        <input id="supplier-phone-input" name="phone" type="text"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Email</label>
                        <input id="supplier-email-input" name="email" type="email"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Alamat</label>
                    <textarea id="supplier-address-input" name="address" rows="3"
                        class="block w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Status</label>
                    <select id="supplier-status-input" name="is_active"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                <button type="button" class="close-supplier-modal rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">
                    Batal
                </button>

                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Simpan Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endif
{{-- Modal Hapus --}}
<div id="delete-supplier-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <form id="delete-supplier-form" method="POST">
            @csrf
            @method('DELETE')

            <div class="p-6 text-center">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hapus supplier?</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Supplier <span id="delete-supplier-name" class="font-semibold text-slate-700"></span> akan dihapus.
                </p>
            </div>

            <div class="flex justify-center gap-3 border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                <button id="cancel-supplier-delete" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">
                    Batal
                </button>

                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('supplier-search');
        const rows = document.querySelectorAll('.supplier-row');
        const emptyResult = document.getElementById('supplier-empty-result');

        if (searchInput && emptyResult) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                let visibleRows = 0;

                rows.forEach(function (row) {
                    const visible = row.dataset.search.includes(keyword);
                    row.classList.toggle('hidden', !visible);

                    if (visible) visibleRows++;
                });

                emptyResult.classList.toggle('hidden', visibleRows !== 0);
            });
        }

        @if($canManageSuppliers)
        const modal = document.getElementById('supplier-modal');
        const form = document.getElementById('supplier-form');
        const methodInput = document.getElementById('supplier-method');

        const title = document.getElementById('supplier-modal-title');
        const nameInput = document.getElementById('supplier-name-input');
        const phoneInput = document.getElementById('supplier-phone-input');
        const emailInput = document.getElementById('supplier-email-input');
        const addressInput = document.getElementById('supplier-address-input');
        const statusInput = document.getElementById('supplier-status-input');

        document.getElementById('open-supplier-modal').addEventListener('click', function () {
            title.textContent = 'Tambah Supplier';
            form.action = '{{ route('suppliers.store') }}';
            methodInput.value = 'POST';

            nameInput.value = '';
            phoneInput.value = '';
            emailInput.value = '';
            addressInput.value = '';
            statusInput.value = '1';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

        document.querySelectorAll('.edit-supplier').forEach(function (button) {
            button.addEventListener('click', function () {
                title.textContent = 'Edit Supplier';
                form.action = '{{ url('suppliers') }}/' + button.dataset.id;
                methodInput.value = 'PUT';

                nameInput.value = button.dataset.name;
                phoneInput.value = button.dataset.phone;
                emailInput.value = button.dataset.email;
                addressInput.value = button.dataset.address;
                statusInput.value = button.dataset.status;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('.close-supplier-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });

        const deleteModal = document.getElementById('delete-supplier-modal');
        const deleteForm = document.getElementById('delete-supplier-form');

        document.querySelectorAll('.delete-supplier').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('delete-supplier-name').textContent = button.dataset.name;
                deleteForm.action = '{{ url('suppliers') }}/' + button.dataset.id;

                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
            });
        });

        document.getElementById('cancel-supplier-delete').addEventListener('click', function () {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        });
        @endif
    });
</script>
@endsection