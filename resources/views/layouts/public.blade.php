<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIADESA') - Sistem Informasi Administrasi Desa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800" x-data="{
    currentUser: null,
    site: { app_name: 'SIADESA', village_name: 'Desa Sukamaju', village_subname: 'Kec. Jonggol, Kab. Bogor', logo_url: '/images/logo-desa.svg' },
    init() {
        if (window.siadesaStore) {
            this.currentUser = window.siadesaStore.getCurrentUser();
            this.site = window.siadesaStore.getSiteSettings();
        }
    },
    logout() {
        if (window.siadesaStore) {
            window.siadesaStore.logout();
        }
    }
}">

    <header class="bg-white shadow-sm sticky top-0 z-40 border-b border-slate-200">
        <div class="bg-emerald-800 text-white text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="hidden sm:inline">Layanan Pengaduan: (0251) 123-4567</span>
                    <span class="sm:hidden">Call Center Desa</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden md:inline">Senin - Jumat, 08.00 - 14.00 WIB</span>
                    <template x-if="!currentUser">
                        <a href="{{ route('login') }}" class="font-semibold hover:text-emerald-200">Masuk Akun</a>
                    </template>
                    <template x-if="currentUser">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-200" x-text="currentUser.name"></span>
                            <button @click="logout()" class="underline text-rose-300 hover:text-rose-100">Keluar</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img :src="site.logo_url || '/images/logo-desa.svg'" :alt="site.village_name || 'Logo Desa'" class="w-10 h-10 object-contain drop-shadow-sm flex-shrink-0">
                    <div>
                        <p class="font-extrabold text-slate-900 leading-none text-lg tracking-tight" x-text="site.app_name || 'SIADESA'"></p>
                        <p class="text-[11px] text-slate-500 font-medium" x-text="(site.village_name || 'Desa Sukamaju') + ', ' + (site.village_subname || 'Kab. Bogor')"></p>
                    </div>
                </a>
                <div class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg hover:bg-slate-100 hover:text-emerald-700 {{ request()->routeIs('home') ? 'text-emerald-700 bg-emerald-50' : '' }}">Beranda</a>
                    <a href="{{ route('services') }}" class="px-4 py-2 rounded-lg hover:bg-slate-100 hover:text-emerald-700 {{ request()->routeIs('services*') ? 'text-emerald-700 bg-emerald-50' : '' }}">Layanan</a>
                    <a href="{{ route('tracking') }}" class="px-4 py-2 rounded-lg hover:bg-slate-100 hover:text-emerald-700 {{ request()->routeIs('tracking') ? 'text-emerald-700 bg-emerald-50' : '' }}">Lacak Surat</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tracking') }}" class="hidden sm:inline-flex text-sm font-semibold text-emerald-700 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 px-4 py-2 rounded-lg">Lacak</a>
                    
                    <template x-if="!currentUser">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 px-4 py-2 rounded-lg shadow-sm">Masuk / Login</a>
                    </template>
                    <template x-if="currentUser">
                        <a :href="currentUser.redirect || '/warga/dashboard'" class="text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 px-4 py-2 rounded-lg shadow-sm">
                            Ke Dashboard (<span x-text="currentUser.label.split('/')[0]"></span>)
                        </a>
                    </template>
                </div>
            </div>
        </nav>
    </header>

    <main class="min-h-[70vh]">
        @yield('content')
    </main>

    <footer class="bg-emerald-950 text-emerald-50/80 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid md:grid-cols-4 gap-8 text-sm">
            <div class="col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <img :src="site.logo_url || '/images/logo-desa.svg'" :alt="site.village_name || 'Logo Desa'" class="w-10 h-10 object-contain drop-shadow-md flex-shrink-0">
                    <div>
                        <p class="font-extrabold text-white text-lg leading-none" x-text="site.app_name || 'SIADESA'"></p>
                        <p class="text-xs">Sistem Informasi Administrasi Desa</p>
                    </div>
                </div>
                <p class="max-w-sm leading-relaxed">Digitalisasi pelayanan administrasi Desa Sukamaju. Ajukan surat keterangan secara online, pantau progres transparan, dan unduh dokumen resmi ber-QR Code tanpa antre.</p>
            </div>
            <div>
                <p class="font-bold text-white mb-3">Layanan Populer</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('services') }}" class="hover:text-white">Surat Keterangan Usaha</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white">Surat Keterangan Domisili</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white">Surat Keterangan Tidak Mampu</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white">Pengantar SKCK</a></li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-white mb-3">Kontak Kantor</p>
                <p>Jl. Raya Desa Sukamaju No. 01<br>Kec. Jonggol, Kab. Bogor 16830</p>
                <p class="mt-2">Senin - Jumat<br>08.00 - 14.00 WIB</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 py-4 text-xs flex justify-between">
                <span>© 2026 Pemerintah Desa Sukamaju. Hak Cipta Dilindungi.</span>
                <span>v1.1 MVP Phase 1</span>
            </div>
        </div>
    </footer>

</body>
</html>
