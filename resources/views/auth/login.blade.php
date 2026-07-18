<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stockify</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-100 font-[Inter] text-slate-900">
    <main class="flex min-h-screen items-center justify-center p-5">

        <div class="w-full max-w-md">
            {{-- Identitas aplikasi --}}
            <div class="mb-5 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-black text-white shadow-lg shadow-blue-200">
                    S
                </div>

                <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">
                    Stockify
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Sistem manajemen inventaris
                </p>
            </div>

            {{-- Card login --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Masuk ke akun Anda
                    </h2>

                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                        Masukkan email dan password untuk melanjutkan.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Email
                        </label>

                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="nama@contoh.com">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Password
                        </label>

                        <input type="password" name="password" required
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Masukkan password">
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                        Ingat saya
                    </label>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <p class="text-center text-xs text-slate-400">
                        Gunakan akun yang telah dibuat oleh Administrator.
                    </p>
                </div>
            </div>

            <p class="mt-5 text-center text-xs text-slate-400">
                © {{ date('Y') }} Stockify Inventory System
            </p>
        </div>
    </main>
</body>
</html>