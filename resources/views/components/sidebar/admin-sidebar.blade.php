<x-sidebar-dashboard>

    {{-- Semua role --}}
    <x-sidebar-menu-dashboard
        routeName="dashboard"
        title="Dashboard"
    />

    {{-- Semua role dapat melihat produk --}}
    <x-sidebar-menu-dashboard
        routeName="products.index"
        title="Produk"
    />

    {{-- Kategori hanya Admin dan Manager --}}
    @if(in_array(auth()->user()->role, ['admin', 'manager'], true))
        <x-sidebar-menu-dashboard
            routeName="categories.index"
            title="Kategori"
        />
    @endif

    {{-- Semua role dapat melihat supplier --}}
    <x-sidebar-menu-dashboard
        routeName="suppliers.index"
        title="Supplier"
    />

    {{-- Semua role --}}
    <x-sidebar-menu-dashboard
        routeName="stock.in"
        title="Barang Masuk"
    />

    <x-sidebar-menu-dashboard
        routeName="stock.out"
        title="Barang Keluar"
    />

    <x-sidebar-menu-dashboard
        routeName="stock-opnames.index"
        title="Stock Opname"
    />

    {{-- Staff --}}
    @if(auth()->user()->role === 'staff')
        <x-sidebar-menu-dashboard
            routeName="stock.my-history"
            title="Pengajuan Saya"
        />
        <x-sidebar-menu-dashboard
            routeName="stock.history"
            title="Riwayat Stok"
        />
    @endif

    {{-- Admin dan Manager --}}
    @if(in_array(auth()->user()->role, ['admin', 'manager'], true))
        <x-sidebar-menu-dashboard
            routeName="approvals.index"
            title="Persetujuan"
        />

        <x-sidebar-menu-dashboard
            routeName="stock.history"
            title="Riwayat Mutasi"
        />
    @endif

    {{-- Admin --}}
    @if(auth()->user()->role === 'admin')
        <x-sidebar-menu-dashboard
            routeName="reports.index"
            title="Laporan"
        />

        <x-sidebar-menu-dashboard
            routeName="users.index"
            title="Pengguna"
        />
        @if(auth()->user()->role === 'admin')
        <x-sidebar-menu-dashboard
            routeName="activity-logs.index"
            title="Log Aktivitas"
        />
    @endif
    @endif

</x-sidebar-dashboard>
