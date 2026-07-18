@extends('layouts.dashboard')

@section('content')
@php
    $canManageProducts = in_array(
        auth()->user()->role,
        ['admin', 'manager'],
        true
    );
@endphp
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">Produk</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                @if($canManageProducts)
                    Kelola seluruh produk dan kondisi stok inventaris.
                @else
                    Lihat produk, kategori, supplier, dan kondisi stok inventaris.
                @endif
            </p>
        </div>

        @if($canManageProducts)
            <a href="{{ route('products.create') }}"
                class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                + Tambah Produk
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-slate-100 p-4 dark:border-gray-700">
            <input id="product-search" type="text"
                class="block w-full max-w-md rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                placeholder="Cari nama produk atau SKU...">
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-700">
            @forelse($products as $product)
                @php
                    $stockStatus = $product->stock <= 0
                        ? 'Habis'
                        : ($product->stock <= $product->minimum_stock ? 'Menipis' : 'Aman');
                @endphp

                <div class="product-row px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-gray-700/40"
                    data-search="{{ strtolower($product->name . ' ' . $product->sku) }}">

                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-blue-50 text-blue-600">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="h-full w-full object-cover">
                                @else
                                    P
                                @endif
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ $product->name }}
                                    </p>

                                    @if($product->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 font-mono text-xs text-slate-400">{{ $product->sku }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $product->category?->name ?? '-' }} · {{ $product->supplier?->name ?? 'Tanpa supplier' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-bold text-slate-700 dark:text-gray-200">{{ $product->stock }} Pcs</span>

                            @if($stockStatus === 'Aman')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Stok Aman</span>
                            @elseif($stockStatus === 'Menipis')
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menipis</span>
                            @else
                                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Stok Habis</span>
                            @endif

                            @if($canManageProducts)
    <div class="flex flex-wrap items-center gap-2">
        @if($product->is_active)
            <button
                type="button"
                class="edit-product rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name }}"
                data-sku="{{ $product->sku }}"
                data-category="{{ $product->category_id }}"
                data-supplier="{{ $product->supplier_id }}"
                data-description="{{ $product->description }}"
                data-purchase="{{ $product->purchase_price }}"
                data-selling="{{ $product->selling_price }}"
                data-stock="{{ $product->stock }}"
                data-minimum="{{ $product->minimum_stock }}"
                data-active="{{ $product->is_active ? '1' : '0' }}"
            >
                Edit
            </button>

            <form
                method="POST"
                action="{{ route('products.destroy', $product) }}"
                onsubmit="return confirm('Hapus atau nonaktifkan produk ini?')"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                >
                    Hapus
                </button>
            </form>
        @else
            <form
                method="POST"
                action="{{ route('products.activate', $product) }}"
                onsubmit="return confirm('Aktifkan kembali produk ini?')"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                >
                    Aktifkan
                </button>
            </form>
        @endif
    </div>
@endif

                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center text-slate-500">
                    Belum ada produk. Tambahkan produk pertama Anda.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div id="product-edit-modal" class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto bg-slate-900/50 p-4">
    <div class="my-8 w-full max-w-2xl rounded-xl bg-white shadow-2xl">
        <form id="product-edit-form" method="POST">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Edit Produk</h2>
                <button id="close-product-modal" type="button" class="text-xl text-slate-400">✕</button>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <input id="edit-name" name="name" required class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Nama produk">
                <input id="edit-sku" name="sku" required class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="SKU">

                <select id="edit-category" name="category_id" required class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <select id="edit-supplier" name="supplier_id" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    <option value="">Tanpa supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>

                <input id="edit-purchase" name="purchase_price" type="number" min="0" required class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Harga beli">
                <input id="edit-selling" name="selling_price" type="number" min="0" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Harga jual">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Stok Saat Ini
                    </label>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <span
                            id="edit-stock-display"
                            class="text-sm font-bold text-slate-900"
                        >
                            0
                        </span>

                        <span class="ml-1 text-sm text-slate-500">
                            Pcs
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-slate-500">
                        Stok hanya dapat berubah melalui transaksi atau stock opname.
                    </p>
                </div>
                <input id="edit-minimum" name="minimum_stock" type="number" min="0" required class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Minimum stok">

                <textarea id="edit-description" name="description" rows="3" class="sm:col-span-2 rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Deskripsi"></textarea>

                <select id="edit-active" name="is_active" class="sm:col-span-2 rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4">
                <button id="cancel-product-modal" type="button" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600">
                    Batal
                </button>

                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('product-search');

        search.addEventListener('input', function () {
            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.product-row').forEach(function (row) {
                row.classList.toggle('hidden', !row.dataset.search.includes(keyword));
            });
        });

        const modal = document.getElementById('product-edit-modal');
        const form = document.getElementById('product-edit-form');

        document.querySelectorAll('.edit-product').forEach(function (button) {
            button.addEventListener('click', function () {
                form.action = '{{ url('products') }}/' + button.dataset.id;

                document.getElementById('edit-name').value = button.dataset.name;
                document.getElementById('edit-sku').value = button.dataset.sku;
                document.getElementById('edit-category').value = button.dataset.category;
                document.getElementById('edit-supplier').value = button.dataset.supplier;
                document.getElementById('edit-description').value = button.dataset.description;
                document.getElementById('edit-purchase').value = button.dataset.purchase;
                document.getElementById('edit-selling').value = button.dataset.selling;
                document.getElementById('edit-stock-display').textContent =
                    button.dataset.stock;   
                document.getElementById('edit-minimum').value = button.dataset.minimum;
                document.getElementById('edit-active').value = button.dataset.active;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        ['close-product-modal', 'cancel-product-modal'].forEach(function (id) {
            document.getElementById(id).addEventListener('click', function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });
    });
</script>
@endsection