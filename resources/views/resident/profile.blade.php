@extends('layouts.app', ['role' => 'warga'])

@section('title', 'Lengkapi Data Diri Warga')
@section('page_title', 'Kelengkapan Profil Penduduk')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    profile: {
        nik: '',
        kk: '',
        name: '',
        birth_place: '',
        birth_date: '',
        gender: 'Laki-Laki',
        address: '',
        rt: '',
        rw: '',
        religion: 'Islam',
        marital: 'Kawin',
        job: 'Wiraswasta',
        phone: ''
    },
    fieldErrors: {},
    errorMsg: '',
    successMsg: '',
    loading: false,
    init() {
        if (window.siadesaStore) {
            const current = window.siadesaStore.getCurrentUser();
            let res = null;
            if (current && current.nik) {
                const residents = window.siadesaStore.getResidents();
                res = residents.find(r => r.nik === current.nik);
            }
            if (current || res) {
                this.profile.nik = (current && current.nik) ? current.nik : (res ? res.nik : '');
                this.profile.kk = (current && current.kk) ? current.kk : (res ? (res.kk || '') : '');
                this.profile.name = (current && current.name) ? current.name : (res ? (res.name || '') : '');
                this.profile.birth_place = (current && current.birth_place) ? current.birth_place : (res ? (res.birth_place || '') : '');
                this.profile.birth_date = (current && current.birth_date) ? current.birth_date : (res ? (res.birth_date || '') : '');
                this.profile.gender = (current && current.gender) ? current.gender : (res ? (res.gender || 'Laki-Laki') : 'Laki-Laki');
                this.profile.address = (current && current.address) ? current.address : (res ? (res.address || '') : '');
                this.profile.rt = (current && current.rt) ? current.rt : (res ? (res.rt || '001') : '001');
                this.profile.rw = (current && current.rw) ? current.rw : (res ? (res.rw || '001') : '001');
                this.profile.religion = (current && current.religion) ? current.religion : (res ? (res.religion || 'Islam') : 'Islam');
                this.profile.marital = (current && current.marital) ? current.marital : (res ? (res.marital || 'Kawin') : 'Kawin');
                this.profile.job = (current && current.job) ? current.job : (res ? (res.job || 'Wiraswasta') : 'Wiraswasta');
                this.profile.phone = (current && current.phone) ? current.phone : (res ? (res.phone || '') : '');
            }
        }
    },
    cleanDigits(field) {
        if (this.profile[field]) {
            this.profile[field] = String(this.profile[field]).replace(/[^0-9]/g, '');
        }
    },
    saveProfile() {
        this.errorMsg = '';
        this.successMsg = '';
        this.fieldErrors = {};

        // Sanitize & clean input
        this.profile.nik = String(this.profile.nik || '').replace(/[^0-9]/g, '').trim();
        this.profile.kk = String(this.profile.kk || '').replace(/[^0-9]/g, '').trim();
        this.profile.name = (this.profile.name || '').trim();
        this.profile.birth_place = (this.profile.birth_place || '').trim();
        this.profile.address = (this.profile.address || '').trim();
        this.profile.phone = (this.profile.phone || '').trim();
        if (this.profile.rt) this.profile.rt = String(this.profile.rt).replace(/[^0-9]/g, '').padStart(3, '0').slice(-3);
        if (this.profile.rw) this.profile.rw = String(this.profile.rw).replace(/[^0-9]/g, '').padStart(3, '0').slice(-3);

        const missing = [];

        if (!this.profile.kk || this.profile.kk.length !== 16) {
            this.fieldErrors.kk = 'Nomor KK harus tepat 16 digit angka (saat ini: ' + (this.profile.kk ? this.profile.kk.length : 0) + ' digit)';
            missing.push('Nomor KK (16 digit)');
        }
        if (!this.profile.name) {
            this.fieldErrors.name = 'Nama lengkap wajib diisi!';
            missing.push('Nama Lengkap');
        }
        if (!this.profile.birth_place) {
            this.fieldErrors.birth_place = 'Tempat lahir wajib diisi!';
            missing.push('Tempat Lahir');
        }
        if (!this.profile.birth_date) {
            this.fieldErrors.birth_date = 'Tanggal lahir wajib dipilih!';
            missing.push('Tanggal Lahir');
        }
        if (!this.profile.address) {
            this.fieldErrors.address = 'Alamat domisili wajib diisi!';
            missing.push('Alamat');
        }
        if (!this.profile.rt) {
            this.fieldErrors.rt = 'RT wajib diisi!';
            missing.push('RT');
        }
        if (!this.profile.rw) {
            this.fieldErrors.rw = 'RW wajib diisi!';
            missing.push('RW');
        }
        if (!this.profile.phone) {
            this.fieldErrors.phone = 'Nomor WhatsApp wajib diisi!';
            missing.push('No. WhatsApp');
        }

        if (missing.length > 0) {
            this.errorMsg = 'Mohon lengkapi kolom yang wajib diisi: ' + missing.join(', ') + '.';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        this.loading = true;
        if (window.siadesaStore) {
            window.siadesaStore.updateProfile(this.profile.nik, this.profile).then(res => {
                if (res && res.success) {
                    this.successMsg = 'Data profil berhasil disimpan dan divalidasi sistem!';
                    setTimeout(() => {
                        window.location.href = res.redirect || '{{ route('resident.dashboard') }}';
                    }, 1000);
                } else {
                    this.errorMsg = (res && res.message) ? res.message : 'Terjadi kendala saat menyimpan data.';
                    this.loading = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        } else {
            this.errorMsg = 'Sistem penyimpanan sedang memuat, silakan coba lagi.';
            this.loading = false;
        }
    }
}">

    <!-- Alert Edukasi PRD BR-02 -->
    <div class="p-5 rounded-3xl bg-emerald-50 border border-emerald-200 flex gap-4 text-emerald-950 shadow-xs">
        <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <h3 class="font-extrabold text-sm sm:text-base">Kepatuhan Profil Penduduk (PRD Aturan BR-02)</h3>
            <p class="text-xs text-emerald-800/90 mt-1 leading-relaxed">
                Masyarakat wajib melengkapi biodata kependudukan (NIK, Nomor KK, TTL, RT/RW, dan No. WA) sebelum dapat mengajukan permohonan surat keterangan agar surat yang diterbitkan sah secara hukum administrasi.
            </p>
        </div>
    </div>

    <!-- Alert Sukses Top -->
    <template x-if="successMsg">
        <div class="p-4 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-900 text-xs font-bold text-center flex items-center justify-center gap-2 shadow-xs">
            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span x-text="successMsg"></span>
        </div>
    </template>

    <!-- Alert Error Top -->
    <template x-if="errorMsg">
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span x-text="errorMsg"></span>
        </div>
    </template>

    <!-- FORM BIODATA LENGKAP -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form @submit.prevent="saveProfile()" class="space-y-5">
            
            <div class="border-b border-slate-100 pb-3">
                <h4 class="font-extrabold text-slate-900 text-base">Identitas Nomor Kependudukan</h4>
                <p class="text-xs text-slate-500">Nomor identitas resmi yang tertera pada Kartu Keluarga & e-KTP.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Nomor Induk Kependudukan (NIK) *</label>
                    <input type="text" x-model="profile.nik" readonly class="w-full px-4 py-3 bg-slate-100 text-slate-600 font-mono font-bold border border-slate-200 rounded-xl text-sm cursor-not-allowed">
                    <p class="text-[10px] text-slate-400 mt-1">NIK terdaftar permanen sebagai ID Akun Anda.</p>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-xs font-bold text-slate-700">Nomor Kartu Keluarga (KK) *</label>
                        <span class="text-[10px] font-mono" :class="(profile.kk && profile.kk.length === 16) ? 'text-emerald-600 font-bold' : 'text-slate-400'" x-text="(profile.kk ? profile.kk.length : 0) + ' / 16 digit'"></span>
                    </div>
                    <input type="text" 
                           x-model="profile.kk" 
                           @input="cleanDigits('kk')"
                           maxlength="16" 
                           placeholder="Masukkan 16 digit nomor KK" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.kk ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.kk">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.kk"></p>
                    </template>
                </div>
            </div>

            <div class="border-b border-slate-100 pb-3 pt-2">
                <h4 class="font-extrabold text-slate-900 text-base">Biodata Pribadi</h4>
                <p class="text-xs text-slate-500">Data ini akan tercetak otomatis pada lembar surat keterangan resmi.</p>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Nama Lengkap (Sesuai KTP) *</label>
                <input type="text" 
                       x-model="profile.name" 
                       placeholder="Nama Lengkap Pemohon" 
                       class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2"
                       :class="fieldErrors.name ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                <template x-if="fieldErrors.name">
                    <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.name"></p>
                </template>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Tempat Lahir *</label>
                    <input type="text" 
                           x-model="profile.birth_place" 
                           placeholder="Contoh: Bogor" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.birth_place ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.birth_place">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.birth_place"></p>
                    </template>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Tanggal Lahir *</label>
                    <input type="date" 
                           x-model="profile.birth_date" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.birth_date ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.birth_date">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.birth_date"></p>
                    </template>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Jenis Kelamin *</label>
                    <select x-model="profile.gender" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Laki-Laki">Laki-Laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Agama</label>
                    <select x-model="profile.religion" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                        <option>Islam</option>
                        <option>Kristen</option>
                        <option>Katolik</option>
                        <option>Hindu</option>
                        <option>Buddha</option>
                        <option>Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Status Perkawinan</label>
                    <select x-model="profile.marital" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                        <option>Belum Kawin</option>
                        <option>Kawin</option>
                        <option>Cerai Hidup</option>
                        <option>Cerai Mati</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Pekerjaan</label>
                    <input type="text" x-model="profile.job" placeholder="Wiraswasta / Karyawan / dll" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                </div>
            </div>

            <div class="border-b border-slate-100 pb-3 pt-2">
                <h4 class="font-extrabold text-slate-900 text-base">Alamat Domisili & Kontak</h4>
                <p class="text-xs text-slate-500">Wilayah tempat tinggal di Desa Sukamaju.</p>
            </div>

            <div class="grid sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-xs font-bold text-slate-700 block mb-1">Jalan / Nama Kampung *</label>
                    <input type="text" 
                           x-model="profile.address" 
                           placeholder="Contoh: Jl. Merpati No. 14 / Kp. Sukamaju" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.address ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.address">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.address"></p>
                    </template>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">RT (3 Digit) *</label>
                    <input type="text" 
                           x-model="profile.rt" 
                           @input="cleanDigits('rt')"
                           maxlength="3" 
                           placeholder="002" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm text-center font-mono focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.rt ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.rt">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.rt"></p>
                    </template>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">RW (3 Digit) *</label>
                    <input type="text" 
                           x-model="profile.rw" 
                           @input="cleanDigits('rw')"
                           maxlength="3" 
                           placeholder="005" 
                           class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm text-center font-mono focus:bg-white focus:outline-none focus:ring-2"
                           :class="fieldErrors.rw ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                    <template x-if="fieldErrors.rw">
                        <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.rw"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Nomor WhatsApp / HP Aktif *</label>
                <input type="text" 
                       x-model="profile.phone" 
                       @input="cleanDigits('phone')"
                       placeholder="081298765432" 
                       class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2"
                       :class="fieldErrors.phone ? 'border-rose-400 focus:ring-rose-400 bg-rose-50/30' : 'border-slate-200 focus:ring-emerald-500'">
                <template x-if="fieldErrors.phone">
                    <p class="text-[11px] text-rose-600 font-medium mt-1" x-text="fieldErrors.phone"></p>
                </template>
                <p class="text-[11px] text-slate-400 mt-1">Petugas loket desa akan menghubungi nomor ini jika terdapat berkas yang perlu diperbaiki.</p>
            </div>

            <!-- Error Banner Bottom (Langsung terlihat di atas tombol Simpan) -->
            <template x-if="errorMsg">
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span x-text="errorMsg"></span>
                </div>
            </template>

            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <a href="{{ route('resident.dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700">Kembali ke Dashboard</a>
                <button type="submit" :disabled="loading" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span x-show="!loading">Simpan & Lengkapi Data Profil</span>
                    <span x-show="loading">Menyimpan Biodata...</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
