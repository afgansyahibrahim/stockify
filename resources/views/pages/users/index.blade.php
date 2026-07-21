@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen w-full bg-slate-50 p-4 sm:p-6 dark:bg-gray-900">
        <div class="space-y-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Pengguna</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Kelola akun Admin, Manager, dan Staff.
                    </p>
                </div>
            </div>

            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            <div id="tambah-pengguna" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-bold text-slate-900">Tambah Pengguna Baru</h2>
                <p class="mt-1 text-sm text-slate-500">Buat akun baru dan tentukan hak aksesnya.</p>

                <form method="POST" action="{{ route('users.store') }}" class="mt-5">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Nama pengguna"
                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="nama@email.com"
                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                            <select name="role" required
                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="staff">Staff</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <input type="password" name="password" required
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" required
                                placeholder="Ulangi password"
                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex items-end">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="is_active" value="1" checked
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                Akun aktif
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Daftar Pengguna</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <div class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-slate-900">
                                            {{ $user->name }}

                                            @if ($user->id === auth()->id())
                                                <span class="ml-1 text-xs font-medium text-blue-600">(Anda)</span>
                                            @endif
                                        </p>

                                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $user->role === 'admin'
                                            ? 'bg-violet-100 text-violet-700'
                                            : ($user->role === 'manager'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>

                            <details class="mt-4 rounded-lg bg-slate-50 px-4 py-3">
                                <summary class="cursor-pointer text-sm font-semibold text-blue-600">
                                    Ubah pengguna
                                </summary>

                                <form method="POST" action="{{ route('users.update', $user) }}" class="mt-4">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                                            <input type="text" name="name" value="{{ $user->name }}" required
                                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                                            <input type="email" name="email" value="{{ $user->email }}" required
                                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                                            <select name="role" class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                                                <option value="staff" @selected($user->role === 'staff')>Staff</option>
                                                <option value="manager" @selected($user->role === 'manager')>Manager</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                                Password Baru <span class="font-normal text-slate-400">(opsional)</span>
                                            </label>
                                            <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                                Konfirmasi Password Baru
                                            </label>
                                            <input type="password" name="password_confirmation"
                                                class="w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm">
                                        </div>

                                        <div class="flex items-end">
                                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="is_active" value="1"
                                                    @checked($user->is_active)
                                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                Akun aktif
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                data-stockify-confirm-form="delete-user-{{ $user->id }}"
                                                data-stockify-confirm="Hapus pengguna {{ $user->name }}?"
                                                data-stockify-confirm-title="Hapus pengguna"
                                                data-stockify-confirm-label="Ya, Hapus"
                                                data-stockify-confirm-variant="danger"
                                                class="rounded-lg border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                                                Hapus
                                            </button>
                                        @endif

                                        <button type="submit"
                                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>

                                @if ($user->id !== auth()->id())
                                    <form id="delete-user-{{ $user->id }}" method="POST"
                                        action="{{ route('users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </details>
                        </div>
                    @empty
                        <div class="p-10 text-center text-slate-500">
                            Belum ada pengguna.
                        </div>
                    @endforelse
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection