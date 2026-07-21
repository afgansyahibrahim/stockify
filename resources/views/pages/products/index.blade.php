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
                    $productAttributes = $product->attributes
                        ->map(fn ($attribute) => [
                            'name' => $attribute->name,
                            'value' => $attribute->value,
                        ])
                        ->values();
                    $purchaseReferences = $purchaseReferencesByProduct[$product->id] ?? [];
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

                            <button
                                type="button"
                                class="detail-product rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                data-name="{{ $product->name }}"
                                data-sku="{{ $product->sku }}"
                                data-category="{{ $product->category?->name ?? '-' }}"
                                data-supplier="{{ $product->supplier?->name ?? 'Tanpa supplier' }}"
                                data-description="{{ $product->description ?? '' }}"
                                data-stock="{{ $product->stock }}"
                                data-minimum="{{ $product->minimum_stock }}"
                                data-selling="{{ $product->selling_price }}"
                                data-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                                data-attributes="{{ $productAttributes->toJson() }}"
                                data-purchase-references="{{ base64_encode(json_encode($purchaseReferences, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]') }}"
                            >
                                Detail
                            </button>

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
                data-selling="{{ $product->selling_price }}"
                data-stock="{{ $product->stock }}"
                data-minimum="{{ $product->minimum_stock }}"
                data-active="{{ $product->is_active ? '1' : '0' }}"
                data-attributes="{{ $productAttributes->toJson() }}"
            >
                Edit
            </button>

            <form
                method="POST"
                action="{{ route('products.destroy', $product) }}"
                data-stockify-confirm="Produk yang sudah memiliki riwayat akan dinonaktifkan, bukan dihapus permanen."
                data-stockify-confirm-title="Hapus atau nonaktifkan produk"
                data-stockify-confirm-label="Ya, Lanjutkan"
                data-stockify-confirm-variant="danger"
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
                data-stockify-confirm="Aktifkan kembali produk ini?"
                data-stockify-confirm-title="Aktifkan produk"
                data-stockify-confirm-label="Ya, Aktifkan"
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

<div id="product-detail-modal" class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-3xl rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">Detail Produk</p>
                <h2 id="detail-name" class="mt-1 text-xl font-bold text-slate-900 dark:text-white">-</h2>
            </div>
            <button type="button" data-close-detail class="rounded-lg p-2 text-xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">✕</button>
        </div>

        <div class="space-y-5 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="shrink-0 sm:w-36">
                    <p class="mb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-400 sm:text-left">Foto Produk</p>
                    <div id="detail-image-placeholder" class="mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-blue-50 text-2xl font-bold text-blue-600 shadow-sm sm:mx-0 sm:h-36 sm:w-36 dark:border-gray-700 dark:bg-blue-950/50 dark:text-blue-300">P</div>
                </div>
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-gray-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">SKU</p>
                        <p id="detail-sku" class="mt-1 font-mono text-sm font-semibold text-slate-800 dark:text-gray-100">-</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-gray-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kategori</p>
                        <p id="detail-category" class="mt-1 text-sm font-semibold text-slate-800 dark:text-gray-100">-</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-gray-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Supplier Utama</p>
                        <p id="detail-supplier" class="mt-1 text-sm font-semibold text-slate-800 dark:text-gray-100">-</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-gray-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Stok</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-gray-100"><span id="detail-stock">0</span> Pcs <span class="font-normal text-slate-500 dark:text-gray-400">· Min. <span id="detail-minimum">0</span></span></p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-gray-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Harga Jual Standar</p>
                        <p id="detail-selling" class="mt-1 text-sm font-semibold text-slate-800 dark:text-gray-100">Rp 0</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 p-4 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Deskripsi</p>
                <p id="detail-description" class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-gray-300">Tidak ada deskripsi.</p>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-gray-700">
                <div class="flex flex-col gap-1 border-b border-slate-100 bg-slate-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                    <h3 class="font-bold text-slate-900 dark:text-white">Referensi Harga Beli Supplier Aktif</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">
                        Harga Barang Masuk terakhir dari setiap supplier aktif pada transaksi yang telah disetujui.
                    </p>
                </div>

                <div id="detail-purchase-references-empty" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-gray-400">
                    Belum ada harga beli dari supplier aktif untuk produk ini.
                </div>

                <div id="detail-purchase-references-table" class="hidden overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Supplier</th>
                                <th class="px-4 py-3 text-right font-semibold">Harga Beli Terakhir</th>
                                <th class="px-4 py-3 text-right font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-purchase-references-body" class="divide-y divide-slate-100 dark:divide-gray-700"></tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-gray-700">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">Atribut Produk</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">Spesifikasi tambahan produk.</p>
                    </div>
                    <span id="detail-attribute-count" class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">0 atribut</span>
                </div>
                <div id="detail-attributes-empty" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-gray-400">Produk ini belum memiliki atribut tambahan.</div>
                <table id="detail-attributes-table" class="hidden w-full text-sm">
                    <thead class="bg-white text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Atribut</th>
                            <th class="px-4 py-3 font-semibold">Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="detail-attributes-body" class="divide-y divide-slate-100 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end border-t border-slate-100 px-5 py-4 dark:border-gray-700">
            <button type="button" data-close-detail class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Tutup</button>
        </div>
    </div>
</div>

<div id="product-edit-modal" class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-3xl rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <form id="product-edit-form" method="POST">
            @csrf
            @method('PUT')
            <input id="editing-product-id" type="hidden" name="editing_product_id" value="">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Produk</h2>
                <button id="close-product-modal" type="button" class="rounded-lg p-2 text-xl leading-none text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700">✕</button>
            </div>

            @if($errors->any() && old('editing_product_id'))
                <div class="mx-5 mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300">
                    <p class="font-semibold">Produk belum dapat diperbarui.</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <input id="edit-name" name="name" required class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Nama produk">
                <input id="edit-sku" name="sku" required class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="SKU">

                <select id="edit-category" name="category_id" required class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <select id="edit-supplier" name="supplier_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Tanpa supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>

                <input id="edit-selling" name="selling_price" type="number" min="0" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Harga jual">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                        Stok Saat Ini
                    </label>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-gray-600 dark:bg-gray-900/50">
                        <span
                            id="edit-stock-display"
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            0
                        </span>

                        <span class="ml-1 text-sm text-slate-500 dark:text-gray-400">
                            Pcs
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                        Stok hanya dapat berubah melalui transaksi atau stock opname.
                    </p>
                </div>
                <input id="edit-minimum" name="minimum_stock" type="number" min="0" required class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Minimum stok">

                <textarea id="edit-description" name="description" rows="3" class="sm:col-span-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Deskripsi"></textarea>

                <select id="edit-active" name="is_active" class="sm:col-span-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>

                <div class="sm:col-span-2 rounded-lg border border-slate-200 p-4 dark:border-gray-700">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Atribut Produk</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Opsional. Tambah, ubah, atau hapus spesifikasi produk.</p>
                        </div>
                        <button id="add-edit-attribute" type="button" class="w-fit rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-300">+ Tambah atribut</button>
                    </div>
                    <div id="edit-attribute-rows" class="mt-4 space-y-3"></div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                <button id="cancel-product-modal" type="button" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:text-gray-200">
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
        const detailModal = document.getElementById('product-detail-modal');
        const detailAttributesBody = document.getElementById('detail-attributes-body');
        const detailAttributesTable = document.getElementById('detail-attributes-table');
        const detailAttributesEmpty = document.getElementById('detail-attributes-empty');
        const detailPurchaseReferencesBody = document.getElementById('detail-purchase-references-body');
        const detailPurchaseReferencesTable = document.getElementById('detail-purchase-references-table');
        const detailPurchaseReferencesEmpty = document.getElementById('detail-purchase-references-empty');
        const detailImage = document.getElementById('detail-image-placeholder');
        const editModal = document.getElementById('product-edit-modal');
        const editForm = document.getElementById('product-edit-form');
        const editAttributeRows = document.getElementById('edit-attribute-rows');
        const addEditAttribute = document.getElementById('add-edit-attribute');
        // Nilai lama edit dikosongkan agar halaman Produk tetap aman diproses Blade.
        const oldEditProductId = null;
        const oldEditValues = {};
        let editAttributeIndex = 0;

        search.addEventListener('input', function () {
            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.product-row').forEach(function (row) {
                row.classList.toggle('hidden', !row.dataset.search.includes(keyword));
            });
        });

        function parseAttributes(button) {
            try {
                const attributes = JSON.parse(button.dataset.attributes || '[]');

                return Array.isArray(attributes)
                    ? attributes.filter(function (attribute) {
                        return attribute && typeof attribute.name === 'string';
                    })
                    : [];
            } catch (error) {
                return [];
            }
        }

        function parsePurchaseReferences(button) {
            try {
                const encodedReferences = button.dataset.purchaseReferences || '';

                if (!encodedReferences) {
                    return [];
                }

                const references = JSON.parse(atob(encodedReferences));

                return Array.isArray(references)
                    ? references.filter(function (reference) {
                        return reference
                            && reference.unit_price !== null
                            && reference.unit_price !== undefined
                            && Number.isFinite(Number(reference.unit_price));
                    })
                    : [];
            } catch (error) {
                return [];
            }
        }

        function openModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showDetail(button) {
            document.getElementById('detail-name').textContent = button.dataset.name;
            document.getElementById('detail-sku').textContent = button.dataset.sku;
            document.getElementById('detail-category').textContent = button.dataset.category;
            document.getElementById('detail-supplier').textContent = button.dataset.supplier;
            document.getElementById('detail-stock').textContent = button.dataset.stock;
            document.getElementById('detail-minimum').textContent = button.dataset.minimum;
            document.getElementById('detail-selling').textContent =
                'Rp ' + Number(button.dataset.selling || 0).toLocaleString('id-ID');
            document.getElementById('detail-description').textContent =
                button.dataset.description || 'Tidak ada deskripsi.';

            detailImage.replaceChildren();

            const imageFallback = (button.dataset.name || 'P').charAt(0).toUpperCase();

            if (button.dataset.image) {
                const image = document.createElement('img');
                image.src = button.dataset.image;
                image.alt = 'Foto ' + button.dataset.name;
                image.width = 144;
                image.height = 144;
                image.className = 'h-full w-full object-cover';
                image.addEventListener('error', function () {
                    detailImage.replaceChildren();
                    detailImage.textContent = imageFallback;
                });
                detailImage.appendChild(image);
            } else {
                detailImage.textContent = imageFallback;
            }

            const attributes = parseAttributes(button);
            detailAttributesBody.replaceChildren();
            document.getElementById('detail-attribute-count').textContent =
                attributes.length + ' atribut';

            attributes.forEach(function (attribute) {
                const row = document.createElement('tr');
                row.className = 'bg-white dark:bg-gray-800';

                const name = document.createElement('td');
                name.className = 'px-4 py-3 font-semibold text-slate-700 dark:text-gray-200';
                name.textContent = attribute.name;

                const value = document.createElement('td');
                value.className = 'px-4 py-3 text-slate-600 dark:text-gray-300';
                value.textContent = attribute.value || '-';

                row.append(name, value);
                detailAttributesBody.appendChild(row);
            });

            detailAttributesEmpty.classList.toggle('hidden', attributes.length > 0);
            detailAttributesTable.classList.toggle('hidden', attributes.length === 0);

            const purchaseReferences = parsePurchaseReferences(button);
            detailPurchaseReferencesBody.replaceChildren();

            purchaseReferences.forEach(function (reference) {
                const row = document.createElement('tr');
                row.className = 'bg-white dark:bg-gray-800';

                const supplier = document.createElement('td');
                supplier.className = 'px-4 py-3 font-semibold text-slate-700 dark:text-gray-200';
                supplier.textContent = reference.supplier || 'Supplier tidak tersedia';

                const price = document.createElement('td');
                price.className = 'px-4 py-3 text-right font-semibold text-slate-900 dark:text-white';
                price.textContent = 'Rp ' + Number(reference.unit_price).toLocaleString('id-ID') + ' / Pcs';

                const date = document.createElement('td');
                date.className = 'px-4 py-3 text-right text-slate-600 dark:text-gray-300';
                date.textContent = reference.date || '-';

                row.append(supplier, price, date);
                detailPurchaseReferencesBody.appendChild(row);
            });

            detailPurchaseReferencesEmpty.classList.toggle('hidden', purchaseReferences.length > 0);
            detailPurchaseReferencesTable.classList.toggle('hidden', purchaseReferences.length === 0);
            openModal(detailModal);
        }

        function addEditAttributeRow(attribute) {
            const index = editAttributeIndex++;
            const row = document.createElement('div');

            row.className = 'rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-gray-700 dark:bg-gray-900/40';
            row.innerHTML = `
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Nama atribut</label>
                        <input type="text" name="attributes[${index}][name]" maxlength="100" class="edit-attribute-name block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Contoh: RAM">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Nilai</label>
                        <input type="text" name="attributes[${index}][value]" maxlength="255" class="edit-attribute-value block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Contoh: 16 GB">
                    </div>
                </div>
                <button type="button" class="remove-edit-attribute mt-3 w-full rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300">Hapus</button>
            `;

            row.querySelector('.edit-attribute-name').value = attribute.name || '';
            row.querySelector('.edit-attribute-value').value = attribute.value || '';
            row.querySelector('.remove-edit-attribute').addEventListener('click', function () {
                row.remove();
            });
            editAttributeRows.appendChild(row);
        }

        function renderEditAttributes(attributes) {
            editAttributeIndex = 0;
            editAttributeRows.replaceChildren();
            attributes.forEach(addEditAttributeRow);
        }

        document.querySelectorAll('.detail-product').forEach(function (button) {
            button.addEventListener('click', function () {
                showDetail(button);
            });
        });

        document.querySelectorAll('.edit-product').forEach(function (button) {
            button.addEventListener('click', function () {
                editForm.action = '{{ url('products') }}/' + button.dataset.id;
                document.getElementById('editing-product-id').value = button.dataset.id;

                document.getElementById('edit-name').value = button.dataset.name;
                document.getElementById('edit-sku').value = button.dataset.sku;
                document.getElementById('edit-category').value = button.dataset.category;
                document.getElementById('edit-supplier').value = button.dataset.supplier;
                document.getElementById('edit-description').value = button.dataset.description;
                document.getElementById('edit-selling').value = button.dataset.selling;
                document.getElementById('edit-stock-display').textContent =
                    button.dataset.stock;   
                document.getElementById('edit-minimum').value = button.dataset.minimum;
                document.getElementById('edit-active').value = button.dataset.active;
                renderEditAttributes(parseAttributes(button));

                openModal(editModal);
            });
        });

        addEditAttribute.addEventListener('click', function () {
            addEditAttributeRow({ name: '', value: '' });
        });

        ['close-product-modal', 'cancel-product-modal'].forEach(function (id) {
            document.getElementById(id).addEventListener('click', function () {
                closeModal(editModal);
            });
        });

        document.querySelectorAll('[data-close-detail]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(detailModal);
            });
        });

        [detailModal, editModal].forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(detailModal);
                closeModal(editModal);
            }
        });

        if (oldEditProductId) {
            const button = Array.from(document.querySelectorAll('.edit-product')).find(function (item) {
                return item.dataset.id === String(oldEditProductId);
            });

            if (button) {
                button.click();

                const editInputIds = {
                    name: 'edit-name',
                    sku: 'edit-sku',
                    category_id: 'edit-category',
                    supplier_id: 'edit-supplier',
                    description: 'edit-description',
                    selling_price: 'edit-selling',
                    minimum_stock: 'edit-minimum',
                    is_active: 'edit-active',
                };

                Object.entries(oldEditValues).forEach(function ([field, value]) {
                    if (value === null || field === 'attributes') {
                        return;
                    }

                    const input = document.getElementById(editInputIds[field]);

                    if (input) {
                        input.value = value;
                    }
                });

                if (Array.isArray(oldEditValues.attributes)) {
                    renderEditAttributes(oldEditValues.attributes);
                }
            }
        }
    });
</script>
@endsection