@extends('layouts.public')

@section('title', 'Masuk ke Portal Layanan')

@section('content')
<div class="py-12 bg-slate-50" x-data="{
    loginId: '',
    password: '',
    errorMsg: '',
    loading: false,
    handleLogin(e) {
        e.preventDefault();
        this.errorMsg = '';
        if (!this.loginId.trim() || !this.password.trim()) {
            this.errorMsg = 'ID Pengguna (NIK/Username) dan Kata Sandi wajib diisi!';
            return;
        }

        this.loading = true;
        if (window.siadesaStore) {
            window.siadesaStore.authenticate(this.loginId, this.password).then(res => {
                if (res.success) {
                    window.location.href = res.redirect;
                } else {
                    this.errorMsg = res.message || 'ID atau Kata Sandi salah!';
                    this.loading = false;
                }
            });
        } else {
            this.errorMsg = 'Sistem sedang memuat script, silakan coba sesaat lagi.';
            this.loading = false;
        }
    }
}">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Left Branding Side -->
            <div class="hidden lg:flex lg:col-span-5 flex-col justify-between p-10 rounded-3xl bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950 text-white shadow-xl">
                <div>
                    <div class="flex items-center gap-3 mb-8">
                        <img src="/images/logo-desa.svg" alt="Logo Desa Sukamaju" class="w-10 h-10 object-contain drop-shadow-md flex-shrink-0">
                        <div>
                            <p class="font-extrabold text-lg leading-none">SIADESA</p>
                            <p class="text-xs text-emerald-300">Desa Sukamaju, Kab. Bogor</p>
                        </div>
                    </div>
                    <h2 class="text-3xl font-extrabold leading-tight mb-4">Sistem Akses Resmi & Terotentikasi</h2>
                    <p class="text-emerald-100/80 text-sm leading-relaxed mb-6">
                        Setiap pengguna harus memasukkan <strong>ID Pengguna (NIK / Username)</strong> dan <strong>Kata Sandi</strong> yang terdaftar untuk dapat mengakses modul pelayanan sesuai perannya.
                    </p>
                </div>

                <!-- Registration Highlight Callout for Public -->
                <div class="space-y-3 text-xs">
                    <div class="bg-emerald-500/20 rounded-2xl p-4 border border-emerald-400/30">
                        <div class="flex items-center gap-2 text-emerald-300 font-bold mb-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Masyarakat Baru?</span>
                        </div>
                        <p class="text-emerald-100/90 leading-relaxed mb-3">
                            Daftarkan NIK Anda dalam 1 menit, lalu lengkapi biodata kependudukan (KK, TTL, RT/RW) untuk langsung mengajukan permohonan surat.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-emerald-900 font-extrabold rounded-xl hover:bg-emerald-50 transition-colors shadow-xs">
                            <span>Daftar Akun Warga Baru</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-emerald-300">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Enkripsi Sesi & Sesuai UU PDP No. 27/2022</span>
                    </div>
                </div>
            </div>

            <!-- Right Form Side -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <!-- Mobile / Top Register CTA Banner -->
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <p class="text-xs font-bold text-emerald-950">Belum memiliki akun warga desa?</p>
                            <p class="text-[11px] text-emerald-700">Masyarakat dapat langsung mendaftar online.</p>
                        </div>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs whitespace-nowrap">
                            Daftar Warga Baru →
                        </a>
                    </div>

                    <div class="mb-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Otentikasi Pengguna</span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Masuk ke SIADESA</h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Masukkan ID Akun Anda dan Kata Sandi untuk membuka ruang kerja.
                        </p>
                    </div>

                    <!-- Alert Error -->
                    <template x-if="errorMsg">
                        <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-rose-800 text-xs font-semibold">
                            <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span x-text="errorMsg"></span>
                        </div>
                    </template>

                    <!-- Form Login -->
                    <form @submit="handleLogin($event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                ID Pengguna / NIK KTP / Username Petugas *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <input type="text" 
                                       x-model="loginId" 
                                       placeholder="Contoh: 3201121508900001 atau verifikator / kades / admin" 
                                       class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="text-xs font-bold text-slate-700">Kata Sandi / Password *</label>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input type="password" 
                                       x-model="password" 
                                       placeholder="Masukkan kata sandi akun Anda" 
                                       class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    :disabled="loading"
                                    class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl text-sm transition-all shadow-md flex items-center justify-center gap-2">
                                <span x-show="!loading">Masuk ke Akun Saya</span>
                                <span x-show="loading">Memeriksa Kredensial...</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
