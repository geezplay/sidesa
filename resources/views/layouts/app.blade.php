<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SIADESA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800" x-data="{
    sidebarOpen: false,
    logoutModalOpen: false,
    accessDeniedModalOpen: false,
    deniedMessage: '',
    expectedRole: '{{ $role ?? 'warga' }}',
    user: { name: 'Memuat...', label: 'Pengguna', avatarText: '..' },
    site: { app_name: 'SIADESA', village_name: 'Desa Sukamaju', logo_url: '/images/logo-desa.svg' },
    authorized: false,
    init() {
        if (window.siadesaStore) {
            this.site = window.siadesaStore.getSiteSettings();
            const current = window.siadesaStore.getCurrentUser();
            
            // Cek apakah sudah login
            if (!current) {
                window.location.href = '{{ route('login') }}';
                return;
            }

            // Cek apakah role yang login sesuai dengan halaman role yang dibuka
            if (current.role !== this.expectedRole) {
                const redirectMap = {
                    warga: '{{ route('resident.dashboard') }}',
                    verifikator: '{{ route('admin.verifications') }}',
                    admin: '{{ route('admin.dashboard') }}',
                    kades: '{{ route('kades.dashboard') }}'
                };
                window.location.href = redirectMap[current.role] || '{{ route('login') }}';
                return;
            }

            this.user = current;
            this.authorized = true;
        }
    },
    openLogoutModal() {
        this.logoutModalOpen = true;
    },
    confirmLogout() {
        if (window.siadesaStore) {
            window.siadesaStore.logout();
        } else {
            window.location.href = '{{ route('login') }}';
        }
    }
}">

    <div x-show="authorized" class="min-h-screen flex" style="display: none;">
        <!-- Sidebar Desktop -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-emerald-950 text-white flex-shrink-0 border-r border-emerald-900">
            <!-- Brand -->
            <div class="h-16 flex items-center gap-3 px-6 border-b border-white/10 bg-emerald-900/40">
                <img :src="site.logo_url || '/images/logo-desa.svg'" :alt="site.village_name || 'Logo Desa'" class="w-10 h-10 object-contain drop-shadow-md flex-shrink-0">
                <div class="min-w-0">
                    <h1 class="font-extrabold text-white text-base leading-tight tracking-tight truncate" x-text="site.app_name || 'SIADESA'"></h1>
                    <p class="text-[10px] text-emerald-300 font-medium uppercase tracking-wider truncate" x-text="site.village_name || 'Desa Sukamaju'"></p>
                </div>
            </div>

            <!-- Role Badge -->
            <div class="p-4 mx-3 my-3 bg-white/5 rounded-xl border border-white/10">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Sesi Terotentikasi</p>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <p class="font-bold text-sm text-white truncate" x-text="user.name"></p>
                <p class="text-xs text-slate-300 truncate" x-text="user.label"></p>
                <p class="text-[10px] font-mono text-emerald-300/80 mt-1 truncate" x-text="'ID: ' + (user.nik || user.id)"></p>
            </div>

            <!-- Navigation based on Role -->
            <nav class="flex-1 px-3 space-y-1 overflow-y-auto text-sm font-medium">
                @if(($role ?? '') === 'warga')
                    <a href="{{ route('resident.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('resident.dashboard') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('resident.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('resident.create') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Pengajuan Baru
                    </a>
                    <a href="{{ route('resident.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('resident.history') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Riwayat & Tracking
                    </a>
                    <a href="{{ route('resident.profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('resident.profile') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil & Data Diri
                    </a>
                    <a href="{{ route('resident.services') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('resident.services') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Katalog Layanan
                    </a>
                @elseif(($role ?? '') === 'admin' || ($role ?? '') === 'verifikator')
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Dashboard Admin
                    </a>
                    <a href="{{ route('admin.verifications') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.verification*') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verifikasi Pengajuan
                    </a>
                    <a href="{{ route('admin.residents') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.residents') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Master Penduduk
                    </a>
                    <a href="{{ route('admin.services') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.services') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Kelola Layanan
                    </a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.settings') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan Website
                    </a>
                    <a href="{{ route('admin.accounts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.accounts') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Kelola Akun Petugas
                    </a>
                @elseif(($role ?? '') === 'kades')
                    <a href="{{ route('kades.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('kades.*') ? 'bg-emerald-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Menunggu Persetujuan
                    </a>
                @endif
            </nav>

            <!-- Bottom Exit Logout -->
            <div class="p-3 border-t border-white/10">
                <button type="button" @click="openLogoutModal()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-rose-300 hover:bg-rose-500/20 hover:text-rose-100 font-semibold text-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar / Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h2 class="font-bold text-slate-900 text-lg sm:text-xl">@yield('page_title', 'Dashboard')</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-800 rounded-full border border-emerald-200 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span x-text="'Login Aktif: ' + user.label"></span>
                    </div>

                    <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-emerald-700 text-white font-bold flex items-center justify-center text-xs shadow-xs" x-text="user.avatarText"></div>
                        <div class="hidden md:block text-left">
                            <p class="text-xs font-bold text-slate-900 leading-tight" x-text="user.name"></p>
                            <button type="button" @click="openLogoutModal()" class="text-[11px] text-rose-600 hover:underline font-semibold">Keluar</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Dashboard Footer -->
            <footer class="bg-white border-t border-slate-200 px-6 py-4 text-xs text-slate-500 flex flex-col sm:flex-row justify-between gap-2 items-center">
                <span>SIADESA — Sistem Informasi Administrasi Desa v1.1</span>
                <span>Standar Kepatuhan UU PDP No. 27/2022</span>
            </footer>
        </div>
    </div>

    <!-- Loading Fallback if checking auth -->
    <div x-show="!authorized" class="min-h-screen flex items-center justify-center bg-slate-50">
        <div class="text-center p-6 bg-white rounded-3xl border border-slate-200 shadow-sm max-w-xs w-full">
            <div class="w-10 h-10 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-xs font-bold text-slate-700">Memeriksa Hak Akses Role...</p>
        </div>
    </div>

    <!-- MODAL KONFIRMASI LOGOUT ELEGAN (Menggantikan confirm browser yang kaku) -->
    <div x-show="logoutModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.outside="logoutModalOpen = false" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="bg-white rounded-3xl p-6 sm:p-7 max-w-sm w-full shadow-2xl border border-slate-200 text-center space-y-4">
            
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>

            <div class="space-y-1">
                <h3 class="text-base font-extrabold text-slate-900">Keluar dari Sesi?</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Apakah Anda yakin ingin keluar dari akun <strong class="text-slate-700" x-text="user.name"></strong>?
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2.5 pt-2">
                <button type="button" 
                        @click="logoutModalOpen = false" 
                        class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                    Batal
                </button>
                <button type="button" 
                        @click="confirmLogout()" 
                        class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                    <span>Ya, Keluar</span>
                </button>
            </div>
        </div>
    </div>

</body>
</html>
