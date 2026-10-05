@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Pengaturan Website & Identitas Desa')
@section('page_title', 'Pengaturan Website & Logo')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    site: {
        app_name: 'SIADESA',
        village_name: 'Desa Sukamaju',
        village_subname: 'Kec. Jonggol, Kab. Bogor',
        logo_url: '/images/logo-desa.svg',
        head_name: 'Drs. H. Mulyono',
        head_nip: '19680315 199203 1 004',
        phone: '(0251) 123-4567',
        email: 'pemdes@sukamaju.desa.id',
        address: 'Jl. Raya Desa Sukamaju No. 01'
    },
    toastMsg: '',
    toastSuccess: true,
    init() {
        if (window.siadesaStore) {
            this.site = window.siadesaStore.getSiteSettings();
        }
    },
    showToast(msg, ok = true) {
        this.toastMsg = msg;
        this.toastSuccess = ok;
        setTimeout(() => { this.toastMsg = ''; }, 3500);
    },
    handleLogoUpload(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            this.showToast('File logo harus berupa gambar (PNG, JPG, SVG)!', false);
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            this.showToast('Ukuran file logo maksimal 2MB!', false);
            return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
            this.site.logo_url = event.target.result;
            this.showToast('Pratinjau logo baru siap. Klik Simpan Perubahan untuk menerapkan.');
        };
        reader.readAsDataURL(file);
    },
    resetLogo() {
        this.site.logo_url = '/images/logo-desa.svg';
        this.showToast('Logo dikembalikan ke lambang default.');
    },
    saveSettings() {
        if (!this.site.app_name.trim() || !this.site.village_name.trim()) {
            this.showToast('Nama Aplikasi dan Nama Desa wajib diisi!', false);
            return;
        }
        if (window.siadesaStore) {
            window.siadesaStore.saveSiteSettings(this.site).then(res => {
                if (!res || res.success === false) {
                    this.showToast((res && res.message) ? res.message : 'Gagal menyimpan pengaturan.', false);
                    return;
                }
                this.showToast('✓ Pengaturan website & logo berhasil disimpan!');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        }
    }
}">

    <!-- Toast Notifikasi -->
    <template x-if="toastMsg">
        <div class="fixed top-20 right-6 z-50 p-4 rounded-2xl shadow-xl border flex items-center gap-3 transition-all"
             :class="toastSuccess ? 'bg-emerald-800 text-white border-emerald-600' : 'bg-rose-800 text-white border-rose-600'">
            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span class="text-xs sm:text-sm font-bold" x-text="toastMsg"></span>
        </div>
    </template>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="font-extrabold text-slate-900 text-lg">Kelola Identitas & Logo Website</h3>
            <p class="text-xs text-slate-500 mt-0.5">Admin dapat mengubah nama aplikasi, nama desa/kelurahan, dan mengganti logo resmi yang tampil di header dan sidebar.</p>
        </div>

        <form @submit.prevent="saveSettings()" class="space-y-6 text-xs">
            
            <!-- Bagian 1: Logo Website -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-6">
                <div class="relative w-24 h-24 rounded-2xl bg-white border-2 border-emerald-200 p-2 flex items-center justify-center shadow-xs flex-shrink-0">
                    <img :src="site.logo_url" alt="Logo Desa" class="w-full h-full object-contain">
                </div>
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <h4 class="font-bold text-slate-900 text-sm">Logo Resmi Pemerintahan Desa</h4>
                    <p class="text-slate-500 text-[11px] leading-relaxed">
                        Format yang didukung: PNG transparan, JPG, atau SVG. Ukuran maksimal 2MB. Logo ini akan langsung muncul di sidebar dashboard, navbar publik, kop surat resmi, dan formulir login.
                    </p>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 pt-1">
                        <label class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold cursor-pointer transition-colors shadow-2xs">
                            <span>Ganti File Logo</span>
                            <input type="file" accept="image/*" @change="handleLogoUpload($event)" class="hidden">
                        </label>
                        <button type="button" @click="resetLogo()" class="px-3 py-2 bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 rounded-xl font-semibold transition-colors">
                            Reset ke Default
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Nama Website & Wilayah -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-slate-900 text-sm border-b pb-2">Nama & Informasi Utama</h4>
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nama Singkatan Aplikasi / Website *</label>
                        <input type="text" x-model="site.app_name" placeholder="SIADESA" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400 mt-1">Teks merek utama di pojok kiri atas navbar.</p>
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nama Desa / Kelurahan *</label>
                        <input type="text" x-model="site.village_name" placeholder="Desa Sukamaju" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400 mt-1">Nama unit wilayah pemerintahan desa.</p>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Kecamatan & Kabupaten</label>
                    <input type="text" x-model="site.village_subname" placeholder="Kec. Jonggol, Kab. Bogor" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Bagian 3: Pejabat Pengesah & Kontak -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-slate-900 text-sm border-b pb-2">Pimpinan Desa & Kontak Pelayanan</h4>
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nama Kepala Desa / Lurah</label>
                        <input type="text" x-model="site.head_name" placeholder="Drs. H. Mulyono" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NIP Kepala Desa</label>
                        <input type="text" x-model="site.head_nip" placeholder="19680315 199203 1 004" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nomor Telepon / WhatsApp Kantor</label>
                        <input type="text" x-model="site.phone" placeholder="(0251) 123-4567" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Alamat Email Dinas</label>
                        <input type="text" x-model="site.email" placeholder="pemdes@sukamaju.desa.id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Alamat Lengkap Kantor Desa</label>
                    <input type="text" x-model="site.address" placeholder="Jl. Raya Desa Sukamaju No. 01 Kode Pos 16830" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perubahan Website</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
