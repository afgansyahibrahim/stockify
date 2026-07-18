@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                Pengendalian Persediaan
            </p>

            <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                Buat Stock Opname
            </h1>

            <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                Masukkan hasil penghitungan stok fisik untuk dibandingkan dengan stok sistem.
            </p>
        </div>

        <a href="{{ route('stock-opnames.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700">
            <p class="font-semibold">
                Data belum dapat disimpan.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('stock-opnames.store') }}"
        id="stockOpnameForm"
    >
        @csrf

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- Informasi umum --}}
            <div class="space-y-6 xl:col-span-1">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Informasi Opname
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        Tentukan tanggal dan catatan penghitungan.
                    </p>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label
                                for="opname_date"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
                            >
                                Tanggal Opname
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="opname_date"
                                name="opname_date"
                                value="{{ old('opname_date', now()->format('Y-m-d')) }}"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                        </div>

                        <div>
                            <label
                                for="notes"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-200"
                            >
                                Catatan Umum
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                rows="5"
                                maxlength="1000"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Contoh: Penghitungan stok gudang utama."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Ringkasan
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500 dark:text-gray-400">
                                Produk dipilih
                            </span>

                            <span
                                id="selectedProductCount"
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                0
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500 dark:text-gray-400">
                                Selisih positif
                            </span>

                            <span
                                id="positiveDifference"
                                class="font-bold text-emerald-600"
                            >
                                0
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500 dark:text-gray-400">
                                Selisih negatif
                            </span>

                            <span
                                id="negativeDifference"
                                class="font-bold text-rose-600"
                            >
                                0
                            </span>
                        </div>

                        <div class="border-t border-slate-100 pt-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-700 dark:text-gray-200">
                                    Total selisih
                                </span>

                                <span
                                    id="totalDifference"
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    0
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Produk --}}
            <div class="space-y-6 xl:col-span-2">

                {{-- Pencarian produk --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <h2 class="text-left text-base font-bold text-slate-900 dark:text-white">
                            Pilih Produk
                        </h2>

                        <p class="mt-1 text-left text-sm text-slate-500 dark:text-gray-400">
                            Cari produk berdasarkan nama atau SKU.
                        </p>
                    </div>

                    <div class="relative mt-4 w-full">
                        <input
                            type="text"
                            id="productSearch"
                            placeholder="Cari nama atau SKU..."
                            autocomplete="off"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >

                        <div
                            id="productSearchResults"
                            class="absolute left-0 right-0 z-30 mt-2 hidden max-h-80 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                        ></div>
                    </div>
                </div>

                {{-- Produk terpilih --}}
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            Hasil Penghitungan
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                            Isi stok fisik berdasarkan hasil penghitungan langsung.
                        </p>
                    </div>

                    <div
                        id="emptyState"
                        class="flex min-h-64 flex-col items-center justify-center px-6 py-10 text-center"
                    >
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-100 text-2xl dark:bg-gray-700">
                            📦
                        </div>

                        <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">
                            Belum ada produk dipilih
                        </h3>

                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-gray-400">
                            Gunakan kolom pencarian di atas untuk menambahkan produk ke dalam penghitungan.
                        </p>
                    </div>

                    <div
                        id="selectedProductsWrapper"
                        class="hidden overflow-x-auto"
                    >
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

                                    <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-gray-300">
                                        Catatan
                                    </th>

                                    <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-gray-300">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                id="selectedProductsBody"
                                class="divide-y divide-slate-100 dark:divide-gray-700"
                            ></tbody>
                        </table>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        id="resetFormButton"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                        Reset
                    </button>

                    <button
                        type="submit"
                        id="submitButton"
                        disabled
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Kirim untuk Persetujuan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = {{ Illuminate\Support\Js::from(
        route('stock-opnames.products.search')
    ) }};

    const oldSelectedProducts =
        {{ Illuminate\Support\Js::from($oldSelectedProducts) }};

    const productSearch =
        document.getElementById('productSearch');

    const productSearchResults =
        document.getElementById('productSearchResults');

    const selectedProductsBody =
        document.getElementById('selectedProductsBody');

    const selectedProductsWrapper =
        document.getElementById('selectedProductsWrapper');

    const emptyState =
        document.getElementById('emptyState');

    const selectedProductCount =
        document.getElementById('selectedProductCount');

    const positiveDifference =
        document.getElementById('positiveDifference');

    const negativeDifference =
        document.getElementById('negativeDifference');

    const totalDifference =
        document.getElementById('totalDifference');

    const submitButton =
        document.getElementById('submitButton');

    const resetFormButton =
        document.getElementById('resetFormButton');

    const stockOpnameForm =
        document.getElementById('stockOpnameForm');

    let selectedProducts = Array.isArray(
        oldSelectedProducts
    )
        ? oldSelectedProducts.map(product => ({
            id: Number(product.id),
            name: String(product.name ?? ''),
            sku: String(product.sku ?? ''),
            stock: Number(product.stock ?? 0),
            category: String(product.category ?? ''),
            supplier: String(product.supplier ?? ''),
            physical_stock: Number(
                product.physical_stock
                ?? product.stock
                ?? 0
            ),
            notes: String(product.notes ?? ''),
        }))
        : [];

    let currentSearchResults = [];
    let searchTimer = null;
    let searchController = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function isProductSelected(productId) {
        return selectedProducts.some(product =>
            Number(product.id) === Number(productId)
        );
    }

    function hideSearchResults() {
        productSearchResults.classList.add('hidden');
        productSearchResults.innerHTML = '';
        currentSearchResults = [];
    }

    function showSearchMessage(message) {
        productSearchResults.innerHTML = `
            <div class="px-4 py-5 text-center text-sm text-slate-500 dark:text-gray-400">
                ${escapeHtml(message)}
            </div>
        `;

        productSearchResults.classList.remove('hidden');
    }

    function showSearchResults(products) {
        currentSearchResults = products.filter(
            product => !isProductSelected(product.id)
        );

        if (currentSearchResults.length === 0) {
            showSearchMessage(
                'Produk tidak ditemukan atau sudah dipilih.'
            );

            return;
        }

        productSearchResults.innerHTML =
            currentSearchResults
                .map(product => `
                    <button
                        type="button"
                        data-product-id="${product.id}"
                        class="product-search-result flex w-full items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-slate-50 dark:border-gray-700 dark:hover:bg-gray-700"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">
                                ${escapeHtml(product.name)}
                            </p>

                            <p class="mt-1 font-mono text-xs text-slate-400">
                                ${escapeHtml(product.sku)}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ${escapeHtml(
                                    product.category
                                    || 'Tanpa kategori'
                                )}
                                ·
                                ${escapeHtml(
                                    product.supplier
                                    || 'Tanpa supplier'
                                )}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-xs text-slate-400">
                                Stok Sistem
                            </p>

                            <p class="font-bold text-slate-700 dark:text-gray-200">
                                ${Number(product.stock)}
                            </p>
                        </div>
                    </button>
                `)
                .join('');

        productSearchResults.classList.remove('hidden');
    }

    async function searchProducts(keyword) {
        const normalizedKeyword = keyword.trim();

        if (normalizedKeyword.length === 0) {
            hideSearchResults();
            return;
        }

        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();

        showSearchMessage('Mencari produk...');

        try {
            const response = await fetch(
                `${searchUrl}?q=${encodeURIComponent(
                    normalizedKeyword
                )}`,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: searchController.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Pencarian produk gagal.'
                );
            }

            const products = await response.json();

            showSearchResults(
                Array.isArray(products)
                    ? products
                    : []
            );
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(error);

            showSearchMessage(
                'Produk belum dapat dicari. Coba kembali.'
            );
        }
    }

    function addProduct(product) {
        if (
            !product ||
            isProductSelected(product.id)
        ) {
            return;
        }

        selectedProducts.push({
            id: Number(product.id),
            name: String(product.name ?? ''),
            sku: String(product.sku ?? ''),
            stock: Number(product.stock ?? 0),
            category: String(product.category ?? ''),
            supplier: String(product.supplier ?? ''),
            physical_stock: Number(
                product.physical_stock
                ?? product.stock
                ?? 0
            ),
            notes: String(product.notes ?? ''),
        });

        productSearch.value = '';
        hideSearchResults();
        renderSelectedProducts();
    }

    function removeProduct(productId) {
        selectedProducts = selectedProducts.filter(
            product =>
                Number(product.id) !== Number(productId)
        );

        renderSelectedProducts();
    }

    function updateProduct(
        productId,
        field,
        value
    ) {
        const product = selectedProducts.find(
            item =>
                Number(item.id) === Number(productId)
        );

        if (!product) {
            return;
        }

        if (field === 'physical_stock') {
            product.physical_stock = Math.max(
                0,
                Number(value || 0)
            );
        }

        if (field === 'notes') {
            product.notes = value;
        }

        updateDifferenceCell(productId);
        renderSummary();
    }

    function updateDifferenceCell(productId) {
        const product = selectedProducts.find(
            item =>
                Number(item.id) === Number(productId)
        );

        const element = document.getElementById(
            `difference-${productId}`
        );

        if (!product || !element) {
            return;
        }

        const difference =
            product.physical_stock - product.stock;

        element.textContent =
            difference > 0
                ? `+${difference}`
                : difference;

        element.className = 'font-bold';

        if (difference < 0) {
            element.classList.add('text-rose-600');
        } else if (difference > 0) {
            element.classList.add(
                'text-emerald-600'
            );
        } else {
            element.classList.add(
                'text-slate-500'
            );
        }
    }

    function renderSelectedProducts() {
        if (selectedProducts.length === 0) {
            emptyState.classList.remove('hidden');

            selectedProductsWrapper.classList.add(
                'hidden'
            );

            selectedProductsBody.innerHTML = '';

            submitButton.disabled = true;

            renderSummary();
            return;
        }

        emptyState.classList.add('hidden');

        selectedProductsWrapper.classList.remove(
            'hidden'
        );

        submitButton.disabled = false;

        selectedProductsBody.innerHTML =
            selectedProducts
                .map((product, index) => {
                    const difference =
                        product.physical_stock
                        - product.stock;

                    let differenceClass =
                        'text-slate-500';

                    if (difference < 0) {
                        differenceClass =
                            'text-rose-600';
                    }

                    if (difference > 0) {
                        differenceClass =
                            'text-emerald-600';
                    }

                    return `
                        <tr>
                            <td class="px-5 py-4">
                                <input
                                    type="hidden"
                                    name="items[${index}][product_id]"
                                    value="${product.id}"
                                >

                                <p class="font-semibold text-slate-900 dark:text-white">
                                    ${escapeHtml(product.name)}
                                </p>

                                <p class="mt-1 font-mono text-xs text-slate-400">
                                    ${escapeHtml(product.sku)}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    ${escapeHtml(
                                        product.category
                                        || 'Tanpa kategori'
                                    )}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-bold text-slate-700 dark:text-gray-200">
                                    ${product.stock}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <input
                                    type="number"
                                    name="items[${index}][physical_stock]"
                                    value="${product.physical_stock}"
                                    min="0"
                                    required
                                    data-product-id="${product.id}"
                                    class="physical-stock-input w-24 rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span
                                    id="difference-${product.id}"
                                    class="font-bold ${differenceClass}"
                                >
                                    ${difference > 0 ? '+' : ''}
                                    ${difference}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <input
                                    type="text"
                                    name="items[${index}][notes]"
                                    value="${escapeHtml(product.notes)}"
                                    maxlength="255"
                                    data-product-id="${product.id}"
                                    class="product-notes-input min-w-52 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Opsional"
                                >
                            </td>

                            <td class="px-5 py-4 text-center">
                                <button
                                    type="button"
                                    data-product-id="${product.id}"
                                    class="remove-product-button rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `;
                })
                .join('');

        document
            .querySelectorAll(
                '.physical-stock-input'
            )
            .forEach(input => {
                input.addEventListener(
                    'input',
                    function () {
                        updateProduct(
                            Number(
                                this.dataset.productId
                            ),
                            'physical_stock',
                            this.value
                        );
                    }
                );
            });

        document
            .querySelectorAll(
                '.product-notes-input'
            )
            .forEach(input => {
                input.addEventListener(
                    'input',
                    function () {
                        updateProduct(
                            Number(
                                this.dataset.productId
                            ),
                            'notes',
                            this.value
                        );
                    }
                );
            });

        document
            .querySelectorAll(
                '.remove-product-button'
            )
            .forEach(button => {
                button.addEventListener(
                    'click',
                    function () {
                        removeProduct(
                            Number(
                                this.dataset.productId
                            )
                        );
                    }
                );
            });

        renderSummary();
    }

    function renderSummary() {
        const positiveTotal =
            selectedProducts.reduce(
                (total, product) => {
                    const difference =
                        product.physical_stock
                        - product.stock;

                    return difference > 0
                        ? total + difference
                        : total;
                },
                0
            );

        const negativeTotal =
            selectedProducts.reduce(
                (total, product) => {
                    const difference =
                        product.physical_stock
                        - product.stock;

                    return difference < 0
                        ? total + difference
                        : total;
                },
                0
            );

        const total =
            positiveTotal + negativeTotal;

        selectedProductCount.textContent =
            selectedProducts.length;

        positiveDifference.textContent =
            positiveTotal > 0
                ? `+${positiveTotal}`
                : '0';

        negativeDifference.textContent =
            negativeTotal;

        totalDifference.textContent =
            total > 0
                ? `+${total}`
                : total;

        totalDifference.className =
            'text-xl font-bold';

        if (total < 0) {
            totalDifference.classList.add(
                'text-rose-600'
            );
        } else if (total > 0) {
            totalDifference.classList.add(
                'text-emerald-600'
            );
        } else {
            totalDifference.classList.add(
                'text-slate-900',
                'dark:text-white'
            );
        }
    }

    productSearch.addEventListener(
        'input',
        function () {
            clearTimeout(searchTimer);

            const keyword = this.value;

            searchTimer = setTimeout(
                function () {
                    searchProducts(keyword);
                },
                250
            );
        }
    );

    productSearchResults.addEventListener(
        'click',
        function (event) {
            const button = event.target.closest(
                '.product-search-result'
            );

            if (!button) {
                return;
            }

            const productId = Number(
                button.dataset.productId
            );

            const product =
                currentSearchResults.find(
                    item =>
                        Number(item.id)
                        === productId
                );

            addProduct(product);
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                !productSearch.contains(event.target)
                &&
                !productSearchResults.contains(
                    event.target
                )
            ) {
                hideSearchResults();
            }
        }
    );

    resetFormButton.addEventListener(
        'click',
        function () {
            if (selectedProducts.length === 0) {
                return;
            }

            const confirmed = confirm(
                'Hapus seluruh produk yang sudah dipilih?'
            );

            if (!confirmed) {
                return;
            }

            selectedProducts = [];
            renderSelectedProducts();
        }
    );

    stockOpnameForm.addEventListener(
        'submit',
        function (event) {
            if (selectedProducts.length === 0) {
                event.preventDefault();

                alert(
                    'Pilih minimal satu produk sebelum menyimpan stock opname.'
                );

                return;
            }

            submitButton.disabled = true;
            submitButton.textContent =
                'Menyimpan...';
        }
    );

    renderSelectedProducts();
});
</script>
@endsection