@extends('layouts.public')

@section('title', 'Pendaftaran Akun Warga Baru')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen" x-data="{
    nik: '',
    name: '',
    phone: '',
    password: '',
    confirmPassword: '',
    error: '',
    success: false,
    loading: false,
    register(e) {
        e.preventDefault();
        this.error = '';

        if (!this.nik || this.nik.length !== 16 || isNaN(this.nik)) {
            this.error = 'NIK harus tepat 16 digit angka sesuai KTP!';
            return;
        }
        if (!this.name.trim() || !this.phone.trim() || !this.password) {
            this.error = 'Semua kolom bertanda bintang (*) wajib diisi!';
            return;
        }
        if (this.password.length < 6) {
            this.error = 'Kata sandi minimal 6 karakter demi keamanan akun!';
            return;
        }
        if (this.password !== this.confirmPassword) {
            this.error = 'Konfirmasi kata sandi tidak cocok!';
            return;
        }

        this.loading = true;
        if (window.siadesaStore) {
            window.siadesaStore.registerUser({
                nik: this.nik,
                name: this.name,
                phone: this.phone,
                password: this.password
            }).then(res => {
                if (res.success) {
                    this.success = true;
                    setTimeout(() => {
                        window.location.href = res.redirect || '{{ route('resident.profile') }}';
                    }, 1000);
                } else {
                    this.error = res.message || 'Pendaftaran gagal.';
                    this.loading = false;
                }
            });
        }
    }
}">
    <div class="max-w-xl mx-auto px-4 sm:px-6">

        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-xl mx-auto mb-2">S</div>
                <h1 class="text-2xl font-extrabold text-slate-900">Pendaftaran Akun Warga</h1>
                <p class="text-xs text-slate-500 mt-1">Daftarkan akun menggunakan NIK KTP Anda yang telah tercatat di Desa Sukamaju</p>
            </div>

            <!-- Ketentuan Registrasi Terikat Data Kependudukan -->
            <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p class="leading-relaxed">
                    <strong>Syarat Pendaftaran:</strong> NIK Anda harus sudah tercatat dalam <strong>Master Data Penduduk Desa</strong> dan berstatus <strong>Belum Mendaftar Akun</strong> di website ini.
                </p>
            </div>

            <template x-if="success">
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-center">
                    <p class="font-bold text-emerald-900 text-sm">Pendaftaran Berhasil!</p>
                    <p class="text-xs text-emerald-700 mt-1">Akun berhasil dibuat. Mengalihkan Anda ke Dashboard Masyarakat...</p>
                </div>
            </template>

            <template x-if="error">
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span x-text="error"></span>
                </div>
            </template>

            <form @submit="register($event)" class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-700">Nomor Induk Kependudukan (NIK) *</label>
                    <input type="text" 
                           x-model="nik" 
                           maxlength="16"
                           placeholder="Contoh: 3201xxxxxxxxxxxx (16 digit angka)" 
                           class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">NIK KTP akan menjadi ID login Anda.</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700">Nama Lengkap (Sesuai e-KTP) *</label>
                    <input type="text" 
                           x-model="name" 
                           placeholder="Contoh: Siti Aisyah" 
                           class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700">Nomor WhatsApp Aktif *</label>
                    <input type="text" 
                           x-model="phone" 
                           placeholder="0812xxxxxxxx" 
                           class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700">Kata Sandi (Password) *</label>
                        <input type="password" 
                               x-model="password" 
                               placeholder="Min. 6 karakter" 
                               class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700">Ulangi Kata Sandi *</label>
                        <input type="password" 
                               x-model="confirmPassword" 
                               placeholder="Ulangi sandi" 
                               class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            :disabled="loading"
                            class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">Daftar Akun Sekarang</span>
                        <span x-show="loading">Mendaftarkan Akun...</span>
                    </button>
                </div>
            </form>

            <div class="pt-6 mt-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Sudah pernah mendaftar? <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline">Masuk di sini</a>
                </p>
            </div>

        </div>
    </div>
</div>
@endsection
