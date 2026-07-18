@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                Barang Masuk
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Catat barang dari supplier. Stok bertambah setelah transaksi disetujui.
            </p>
        </div>

        <a href="{{ auth()->user()->role === 'staff'
                ? route('stock.my-history')
                : route('stock.history') }}"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            Lihat Riwayat
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold">Data belum dapat dikirim:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="stock-in-form" action="{{ route('stock.in.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- Informasi transaksi --}}
            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-gray-700 dark:text-white">
                        Informasi Transaksi
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                                Tanggal Masuk
                            </label>

                            <input type="date" name="transaction_date"
                                value="{{ old('transaction_date', date('Y-m-d')) }}" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                                Supplier
                            </label>

                            <select name="supplier_id" required
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Pilih supplier</option>

                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                                Nomor Referensi
                            </label>

                            <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Nomor invoice atau surat jalan">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                                Catatan
                            </label>

                            <textarea name="notes" rows="4"
                                class="block w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Catatan tambahan transaksi.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-bold text-amber-800">Status transaksi: Pending</p>
                    <p class="mt-1 text-xs leading-relaxed text-amber-700">
                        Stok belum bertambah sampai Admin atau Manager menyetujui transaksi ini.
                    </p>
                </div>
            </div>

            {{-- Produk --}}
            <div class="xl:col-span-2">
                <div class="overflow-visible rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Produk Masuk</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                            Cari produk, lalu isi jumlah dan harga satuannya.
                        </p>
                    </div>

                    <div class="border-b border-slate-100 bg-slate-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="relative">
                            <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200">
                                Cari dan Tambahkan Produk
                            </label>

                            <input id="product-search" type="text"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Ketik nama produk atau SKU...">

                            <div id="product-menu"
                                class="absolute left-0 z-40 mt-2 hidden w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                                <div class="max-h-64 overflow-y-auto p-2">
                                    @foreach($products as $product)
                                        <button type="button"
                                            class="product-option flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left hover:bg-blue-50"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-sku="{{ $product->sku }}"
                                            data-search="{{ strtolower($product->name . ' ' . $product->sku) }}">

                                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 font-bold text-blue-600">
                                                P
                                            </span>

                                            <span>
                                                <span class="block text-sm font-semibold text-slate-800">{{ $product->name }}</span>
                                                <span class="mt-0.5 block font-mono text-xs text-slate-400">{{ $product->sku }}</span>
                                            </span>
                                        </button>
                                    @endforeach

                                    <p id="product-not-found" class="hidden px-4 py-6 text-center text-sm text-slate-500">
                                        Produk tidak ditemukan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <div id="empty-product-message" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-12 text-center">
                            <p class="font-semibold text-slate-700">Belum ada produk dipilih</p>
                            <p class="mt-1 text-sm text-slate-500">Gunakan kolom pencarian di atas.</p>
                        </div>

                        <div id="product-items" class="space-y-3"></div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-4">
                        <div class="ml-auto max-w-sm space-y-2">
                            <div class="flex justify-between text-sm text-slate-500">
                                <span>Total Jenis Produk</span>
                                <span id="item-count" class="font-semibold text-slate-700">0 Produk</span>
                            </div>

                            <div class="flex justify-between border-t border-slate-200 pt-3">
                                <span class="font-bold text-slate-900">Estimasi Nilai Barang</span>
                                <span id="grand-total" class="text-xl font-bold text-emerald-600">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button id="reset-form" type="button"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600">
                        Reset Form
                    </button>

                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Kirim untuk Persetujuan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('stock-in-form');
        const searchInput = document.getElementById('product-search');
        const productMenu = document.getElementById('product-menu');
        const productItems = document.getElementById('product-items');
        const emptyMessage = document.getElementById('empty-product-message');
        const itemCount = document.getElementById('item-count');
        const grandTotal = document.getElementById('grand-total');
        let itemIndex = 0;

        function rupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        }

        function updateSummary() {
            const rows = document.querySelectorAll('.product-item-row');
            let total = 0;

            rows.forEach(function (row) {
                const quantity = parseInt(row.querySelector('.item-quantity').value) || 0;
                const price = parseInt(row.querySelector('.item-price').value) || 0;
                const subtotal = quantity * price;

                row.querySelector('.item-subtotal').textContent = rupiah(subtotal);
                total += subtotal;
            });

            itemCount.textContent = rows.length + ' Produk';
            grandTotal.textContent = rupiah(total);
            emptyMessage.classList.toggle('hidden', rows.length > 0);
        }

        function addProduct(id, name, sku) {
            const alreadyAdded = Array.from(document.querySelectorAll('.product-id'))
                .some(input => input.value === id);

            if (alreadyAdded) {
                alert('Produk ini sudah ada pada transaksi.');
                return;
            }

            const index = itemIndex++;
            const row = document.createElement('div');

            row.className = 'product-item-row rounded-xl border border-slate-200 bg-white p-4';

            row.innerHTML = `
                <input class="product-id" type="hidden" name="items[${index}][product_id]" value="${id}">

                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <p class="font-semibold text-slate-900">${name}</p>
                        <p class="mt-1 font-mono text-xs text-slate-400">${sku}</p>
                    </div>

                    <button type="button" class="remove-product rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">
                        Hapus
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase text-slate-500">Jumlah</label>
                        <input type="number" min="1" value="1" name="items[${index}][quantity]"
                            class="item-quantity block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase text-slate-500">Harga Satuan</label>
                        <input type="number" min="0" value="0" name="items[${index}][unit_price]"
                            class="item-price block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase text-slate-500">Subtotal</label>
                        <div class="item-subtotal flex h-[42px] items-center rounded-lg bg-emerald-50 px-3 text-sm font-bold text-emerald-600">
                            Rp 0
                        </div>
                    </div>
                </div>
            `;

            productItems.appendChild(row);

            row.querySelector('.item-quantity').addEventListener('input', updateSummary);
            row.querySelector('.item-price').addEventListener('input', updateSummary);

            row.querySelector('.remove-product').addEventListener('click', function () {
                row.remove();
                updateSummary();
            });

            updateSummary();
        }

        searchInput.addEventListener('focus', function () {
            productMenu.classList.remove('hidden');
        });

        searchInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.product-option').forEach(function (option) {
                const match = option.dataset.search.includes(keyword);
                option.classList.toggle('hidden', !match);

                if (match) visible++;
            });

            document.getElementById('product-not-found').classList.toggle('hidden', visible !== 0);
            productMenu.classList.remove('hidden');
        });

        document.querySelectorAll('.product-option').forEach(function (option) {
            option.addEventListener('click', function () {
                addProduct(option.dataset.id, option.dataset.name, option.dataset.sku);

                searchInput.value = '';
                productMenu.classList.add('hidden');

                document.querySelectorAll('.product-option').forEach(item => item.classList.remove('hidden'));
                document.getElementById('product-not-found').classList.add('hidden');
            });
        });

        document.getElementById('reset-form').addEventListener('click', function () {
            if (!confirm('Reset seluruh data transaksi?')) return;

            form.reset();
            productItems.innerHTML = '';
            itemIndex = 0;
            updateSummary();
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('#product-search') && !event.target.closest('#product-menu')) {
                productMenu.classList.add('hidden');
            }
        });

        updateSummary();
    });
</script>
@endsection