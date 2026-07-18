@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                    K
                </span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Master Data</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                Kategori Produk
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Kelompokkan produk agar pencarian dan laporan inventaris lebih rapi.
            </p>
        </div>

        <button id="open-add-category" type="button"
            class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-900/15 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-900/50 dark:bg-rose-900/15 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Total Kategori</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $categories->count() }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Kategori Aktif</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $categories->where('is_active', true)->count() }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Produk Terkelompok</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $categories->sum('products_count') }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-slate-100 p-4 dark:border-gray-700">
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m2.35-5.15a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                </svg>

                <input id="category-search" type="text"
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Cari nama atau deskripsi kategori...">
            </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-700">
            @forelse($categories as $category)
                <div class="category-row px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-gray-700/40"
                    data-search="{{ strtolower($category->name . ' ' . $category->description) }}">

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </span>

                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $category->name }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                                    {{ $category->description ?: 'Tidak ada deskripsi.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-gray-700 dark:text-gray-200">
                                {{ $category->products_count }} Produk
                            </span>

                            @if($category->is_active)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Aktif
                                </span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-gray-700 dark:text-gray-300">
                                    Nonaktif
                                </span>
                            @endif

                            <button type="button"
                                class="edit-category rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-900/20 dark:text-blue-300"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}"
                                data-description="{{ $category->description }}"
                                data-status="{{ $category->is_active ? '1' : '0' }}">
                                Edit
                            </button>

                            <button type="button"
                                class="delete-category rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-900/20 dark:text-rose-300"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <p class="font-semibold text-slate-700 dark:text-gray-200">Belum ada kategori.</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Tambahkan kategori pertama untuk mulai mengelompokkan produk.</p>
                </div>
            @endforelse
        </div>

        <div id="category-empty-result" class="hidden px-5 py-16 text-center">
            <p class="font-semibold text-slate-700 dark:text-gray-200">Kategori tidak ditemukan.</p>
        </div>
    </div>
</div>

{{-- Modal tambah / edit --}}
<div id="category-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <form id="category-form" action="{{ route('categories.store') }}" method="POST">
            @csrf
            <input id="category-method" type="hidden" name="_method" value="POST">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 id="category-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">Tambah Kategori</h2>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-gray-400">Isi data kategori produk.</p>
                </div>

                <button type="button" class="close-category-modal rounded-lg p-2 text-slate-400 hover:bg-slate-100">
                    ✕
                </button>
            </div>

            <div class="space-y-4 p-5">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Nama Kategori</label>
                    <input id="category-name-input" name="name" type="text" required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Deskripsi</label>
                    <textarea id="category-description-input" name="description" rows="4"
                        class="block w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-200">Status</label>
                    <select id="category-status-input" name="is_active"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                <button type="button" class="close-category-modal rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    Batal
                </button>

                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal hapus --}}
<div id="delete-category-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-800">
        <form id="delete-category-form" method="POST">
            @csrf
            @method('DELETE')

            <div class="p-6 text-center">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hapus kategori?</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                    Kategori <span id="delete-category-name" class="font-semibold text-slate-700 dark:text-gray-200"></span> akan dihapus.
                </p>
            </div>

            <div class="flex justify-center gap-3 border-t border-slate-100 px-5 py-4 dark:border-gray-700">
                <button id="cancel-category-delete" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
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
        const searchInput = document.getElementById('category-search');
        const rows = document.querySelectorAll('.category-row');
        const emptyResult = document.getElementById('category-empty-result');

        searchInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            let totalVisible = 0;

            rows.forEach(function (row) {
                const visible = row.dataset.search.includes(keyword);

                row.classList.toggle('hidden', !visible);

                if (visible) {
                    totalVisible++;
                }
            });

            emptyResult.classList.toggle('hidden', totalVisible !== 0);
        });

        const modal = document.getElementById('category-modal');
        const form = document.getElementById('category-form');
        const methodInput = document.getElementById('category-method');

        const title = document.getElementById('category-modal-title');
        const nameInput = document.getElementById('category-name-input');
        const descriptionInput = document.getElementById('category-description-input');
        const statusInput = document.getElementById('category-status-input');

        document.getElementById('open-add-category').addEventListener('click', function () {
            title.textContent = 'Tambah Kategori';
            form.action = '{{ route('categories.store') }}';
            methodInput.value = 'POST';

            nameInput.value = '';
            descriptionInput.value = '';
            statusInput.value = '1';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

        document.querySelectorAll('.edit-category').forEach(function (button) {
            button.addEventListener('click', function () {
                title.textContent = 'Edit Kategori';
                form.action = '{{ url('categories') }}/' + button.dataset.id;
                methodInput.value = 'PUT';

                nameInput.value = button.dataset.name;
                descriptionInput.value = button.dataset.description;
                statusInput.value = button.dataset.status;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('.close-category-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });

        const deleteModal = document.getElementById('delete-category-modal');
        const deleteForm = document.getElementById('delete-category-form');

        document.querySelectorAll('.delete-category').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('delete-category-name').textContent = button.dataset.name;
                deleteForm.action = '{{ url('categories') }}/' + button.dataset.id;

                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
            });
        });

        document.getElementById('cancel-category-delete').addEventListener('click', function () {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        });
    });
</script>
@endsection