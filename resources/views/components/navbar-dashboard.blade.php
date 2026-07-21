<nav
    class="fixed left-0 right-0 top-0 z-50 border-b border-slate-200 bg-white dark:border-gray-700 dark:bg-gray-800"
>
    <div class="flex h-16 items-center justify-between px-4 lg:px-6">

        {{-- Bagian kiri --}}
        <div class="flex items-center gap-3">

            {{-- Tombol sidebar --}}
            <button
                id="toggleSidebarMobile"
                type="button"
                aria-controls="sidebar"
                aria-expanded="false"
                class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-700"
            >
                <span class="sr-only">Buka sidebar</span>

                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
            </button>

            {{-- Identitas aplikasi --}}
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold text-white"
                >
                    S
                </div>

                <div>
                    <p
                        class="font-bold leading-tight text-slate-900 dark:text-white"
                    >
                        Stockify
                    </p>

                    <p
                        class="text-xs text-slate-500 dark:text-gray-400"
                    >
                        Sistem Inventaris
                    </p>
                </div>
            </a>
        </div>

        {{-- Bagian kanan --}}
        <div class="flex items-center gap-3">

            {{-- Dark mode --}}
            <button
    id="stockify-theme-toggle"
    type="button"
    aria-label="Ubah tema"
    aria-pressed="false"
    class="rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
>
    <span class="sr-only">Ubah tema</span>

    {{-- Ikon bulan: tampil saat mode terang --}}
    <svg
        id="stockify-theme-dark-icon"
        class="h-5 w-5"
        fill="currentColor"
        viewBox="0 0 20 20"
        aria-hidden="true"
    >
        <path
            d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
        ></path>
    </svg>

    {{-- Ikon matahari: tampil saat mode gelap --}}
    <svg
        id="stockify-theme-light-icon"
        class="hidden h-5 w-5"
        fill="currentColor"
        viewBox="0 0 20 20"
        aria-hidden="true"
    >
        <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm0 4a4 4 0 100 8 4 4 0 000-8zm7 5a1 1 0 100-2h-1a1 1 0 100 2h1zM5 10a1 1 0 00-1-1H3a1 1 0 100 2h1a1 1 0 001-1zm5 5a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z"
        ></path>
    </svg>
</button>

            {{-- Identitas pengguna --}}
            <div class="hidden text-right sm:block">
                <p
                    class="text-sm font-semibold leading-tight text-slate-900 dark:text-white"
                >
                    {{ auth()->user()->name }}
                </p>

                <p
                    class="mt-1 text-xs capitalize text-slate-500 dark:text-gray-400"
                >
                    {{ auth()->user()->role }}
                </p>
            </div>

            {{-- Avatar --}}
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-200"
            >
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            {{-- Logout --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
                data-stockify-confirm="Keluar dari Stockify sekarang?"
                data-stockify-confirm-title="Konfirmasi keluar"
                data-stockify-confirm-label="Ya, Keluar"
                data-stockify-confirm-variant="danger"
            >
                @csrf

                <button
                    type="submit"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                >
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>