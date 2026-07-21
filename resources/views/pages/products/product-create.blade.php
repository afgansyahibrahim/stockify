@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Produk</h1>
            <p class="mt-1 text-sm text-slate-500">Daftarkan produk baru ke inventaris.</p>
        </div>

        <a href="{{ route('products.index') }}"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
            Kembali
        </a>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-gray-700 dark:text-white">
                        Informasi Produk
                    </h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Nama Produk</label>
                            <input name="name" value="{{ old('name') }}" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Contoh: Mouse Logitech M331">
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">SKU</label>
                                <input name="sku" value="{{ old('sku') }}" required
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm uppercase text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    placeholder="SKU-LOGI-M331">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Kategori</label>
                                <select name="category_id" required
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <option value="">Pilih kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Supplier Utama (opsional)</label>
                            <select name="supplier_id"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Belum memilih supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Deskripsi</label>
                            <textarea name="description" rows="4"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Deskripsi atau catatan produk.">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-gray-700 dark:text-white">
                        Harga Jual dan Batas Stok
                    </h2>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Harga Jual</label>
                            <input name="selling_price" type="number" min="0" value="{{ old('selling_price', 0) }}"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-gray-400">Harga jual standar. Harga beli diisi saat Barang Masuk.</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Batas Minimum Stok</label>
                            <input name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', 0) }}" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-gray-400">Stok awal selalu 0 dan bertambah melalui Barang Masuk yang disetujui.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                   <div class="border-b border-slate-100 pb-4 dark:border-gray-700">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            Atribut Produk
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                            Opsional. Contoh laptop: RAM, prosesor, atau penyimpanan.
                        </p>
                    </div>

                    <div id="create-attribute-rows" class="mt-5 space-y-3"></div>

                    <button id="add-create-attribute" type="button"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-6 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/70 sm:w-auto sm:min-w-[180px]">
                        <span class="text-base leading-none">+</span>
                        Tambah atribut
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200">Foto Produk</label>
                    <input name="image" type="file" accept=".jpg,.jpeg,.png"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700">
                    <p class="mt-2 text-xs text-slate-400">PNG/JPG maksimal 2 MB.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200">Status</label>
                    <select name="is_active"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <p class="font-bold">Produk belum dapat disimpan:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('products.index') }}"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600">
                Batal
            </a>

            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                Simpan Produk
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('create-attribute-rows');
        const addButton = document.getElementById('add-create-attribute');
        const oldAttributes = @json(old('attributes', []));
        let nextIndex = 0;

        function addAttributeRow(attribute) {
            const index = nextIndex++;
            const row = document.createElement('div');

            row.className = 'attribute-row grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-gray-700 dark:bg-gray-900/40 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end';
            row.innerHTML = `
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Nama atribut</label>
                    <input type="text" name="attributes[${index}][name]" maxlength="100"
                        class="attribute-name block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="Contoh: RAM">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Nilai</label>
                    <input type="text" name="attributes[${index}][value]" maxlength="255"
                        class="attribute-value block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="Contoh: 16 GB">
                </div>
                <button type="button" class="remove-attribute rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300">
                    Hapus
                </button>
            `;

            row.querySelector('.attribute-name').value = attribute.name || '';
            row.querySelector('.attribute-value').value = attribute.value || '';
            row.querySelector('.remove-attribute').addEventListener('click', function () {
                row.remove();
            });

            container.appendChild(row);
        }

        oldAttributes.forEach(function (attribute) {
            addAttributeRow(attribute);
        });

        addButton.addEventListener('click', function () {
            addAttributeRow({ name: '', value: '' });
        });
    });
</script>
@endsection