<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Stockify - Sistem Inventaris"
    >

    <meta
        name="generator"
        content="Laravel"
    >

    {{-- Terapkan tema sebelum halaman ditampilkan --}}
    <script>
        (function () {
            const storageKey = 'color-theme';
            const savedTheme =
                localStorage.getItem(storageKey);

            const systemUsesDark =
                window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;

            const shouldUseDark =
                savedTheme === 'dark' ||
                (!savedTheme && systemUsesDark);

            document.documentElement.classList.toggle(
                'dark',
                shouldUseDark
            );

            document.documentElement.style.colorScheme =
                shouldUseDark ? 'dark' : 'light';
        })();
    </script>

    <title>Stockify</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link
        rel="canonical"
        href="{{ request()->fullUrl() }}"
    >

    @if(isset($page->params['robots']))
        <meta
            name="robots"
            content="{{ $page->params['robots'] }}"
        >
    @endif

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="icon"
        type="image/png"
        href="/favicon.ico"
    >

    <meta
        name="theme-color"
        content="#ffffff"
    >

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

@php
    $whiteBg =
        isset($params['white_bg']) &&
        $params['white_bg'];
@endphp

<body
    class="{{ $whiteBg
        ? 'bg-white dark:bg-gray-900'
        : 'bg-gray-50 dark:bg-gray-800'
    }}"
>
    <x-navbar-dashboard />

    <div
        class="flex min-h-screen overflow-hidden bg-gray-50 pt-16 dark:bg-gray-900"
    >
        <x-sidebar.admin-sidebar />

        <div
            id="main-content"
            class="relative h-full w-full overflow-y-auto bg-gray-50 dark:bg-gray-900 lg:ml-64"
        >
            <main>
                @yield('content')
            </main>

            <x-footer-dashboard />
        </div>
    </div>

    <script
        async
        defer
        src="https://buttons.github.io/buttons.js"
    ></script>

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.2/datepicker.min.js"
    ></script>
</body>
</html>