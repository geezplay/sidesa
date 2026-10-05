@extends('layouts.app', ['role' => 'warga'])
@section('title', 'Formulir Pengajuan Surat')
@section('page_title', 'Pengajuan Layanan Baru')
@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    user: null,
    serviceId: '{{ $serviceId }}',
    service: null,
    purpose: '',
    business_name: '',
    business_type: '',
    business_address: '',
    files: {
        ktp: null,
        kk: null,
        pengantar: null
    },
    successTicket: null,
    submitting: false,
    errorMsg: '',
    init() {
        if (window.siadesaStore) {
            this.user = window.siadesaStore.getCurrentUser();
            
            // Cek apakah profil sudah lengkap (PRD BR-02)
            if (this.user && !this.user.is_profile_complete) {
                window.location.href = '{{ route('resident.profile') }}';
                return;
            }

            const list = window.siadesaStore.getServices();
            this.service = list.find(s => s.id === this.serviceId) || list[0];
            this.serviceId = this.service.id;
        }
    },
    changeService(e) {
        const list = window.siadesaStore.getServices();
        this.service = list.find(s => s.id === e.target.value);
    },
    handleFile(key, event) {
        const file = event.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            this.errorMsg = 'Ukuran berkas ' + file.name + ' melebihi batas 2MB!';
            event.target.value = '';
            return;
        }
        this.errorMsg = '';
        this.files[key] = {
            file: file,
            name: file.name,
            size: (file.size / 1024).toFixed(1) + ' KB',
            type: file.type || 'file'
        };
    },
    removeFile(key) {
        this.files[key] = null;
        const input = document.getElementById('file-' + key);
        if (input) input.value = '';
    },
    submitForm(e) {
        e.preventDefault();
        this.errorMsg = '';
        if (!this.purpose.trim()) { this.errorMsg = 'Kolom keperluan pengajuan wajib diisi.'; return; }
        if (!this.files.ktp || !this.files.kk) {
            this.errorMsg = 'Mohon unggah berkas KTP dan KK persyaratan wajib!';
            return;
        }
        if (!window.siadesaStore) return;

        const fileList = [];
        if (this.files.ktp) fileList.push(this.files.ktp);
        if (this.files.kk) fileList.push(this.files.kk);
        if (this.files.pengantar) fileList.push(this.files.pengantar);

        this.submitting = true;
        window.siadesaStore.addApplication({
            service_id: this.service.id,
            purpose: this.purpose,
            business_name: this.business_name,
            business_type: this.business_type,
            business_address: this.business_address,
            files: fileList
        }).then(created => {
            this.submitting = false;
            if (created && created.tracking_number) {
                this.successTicket = created.tracking_number;
                window.scrollTo({top:0, behavior:'smooth'});
            } else {
                this.errorMsg = (created && created.message) ? created.message : 'Gagal mengirim permohonan. Coba lagi.';
            }
        });
    }
}">
<template x-if="successTicket">
<div class="bg-emerald-50 border-2 border-emerald-500 rounded-3xl p-6 sm:p-8 text-center shadow-sm">
<div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></div>
<h2 class="text-xl font-extrabold text-emerald-950">Permohonan Berhasil Dikirim!</h2>
<p class="text-xs text-emerald-800 mt-1">Simpan nomor tiket untuk tracking mandiri:</p>
<p class="font-mono font-extrabold text-lg text-emerald-800 bg-white inline-block px-4 py-1.5 rounded-xl border border-emerald-300 mt-2" x-text="successTicket"></p>
<div class="flex flex-col sm:flex-row justify-center gap-2 mt-4">
<a :href="'/tracking?kode=' + successTicket" class="px-5 py-2.5 bg-emerald-700 text-white text-xs font-bold rounded-xl hover:bg-emerald-800">Lacak Sekarang</a>
<a href="{{ route('resident.history') }}" class="px-5 py-2.5 bg-white text-emerald-800 border border-emerald-300 text-xs font-bold rounded-xl hover:bg-emerald-100">Ke Riwayat Saya</a>
</div>
</div>
</template>
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
<div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-5">
<div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">1</div>
<div><h3 class="font-bold text-slate-900">Pilih & Isi Formulir Permohonan</h3><p class="text-xs text-slate-500">Data pemohon otomatis disinkronkan dari profil kependudukan Anda.</p></div>
</div>
<template x-if="errorMsg"><div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMsg"></div></template>
<form @submit="submitForm($event)" class="space-y-4">
<div><label class="text-xs font-bold text-slate-700">Jenis Surat / Layanan *</label>
<select @change="changeService($event)" :value="serviceId" class="mt-1.5 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
<template x-for="s in (window.siadesaStore ? window.siadesaStore.getServices() : [])" :key="s.id"><option :value="s.id" :selected="s.id===serviceId" x-text="s.name + ' (' + s.code + ')'"></option></template>
</select></div>
<template x-if="service"><div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
<p><span class="font-bold text-slate-800">Estimasi:</span> <span x-text="service.estimation"></span> | <span class="font-bold text-slate-800">Approval:</span> <span x-text="service.requires_approval ? 'Kepala Desa' : 'Langsung Admin'"></span></p>
<p class="font-bold text-slate-800">Persyaratan Berkas:</p>
<ul class="list-disc ml-4"><template x-for="r in service.requirements" :key="r"><li x-text="r"></li></template></ul>
</div></template>

<!-- Preview Identitas Pemohon Aktif -->
<template x-if="user">
<div class="grid sm:grid-cols-2 gap-4">
<div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
<p class="font-bold text-slate-900" x-text="user.name"></p>
<p class="text-slate-500 font-mono" x-text="'NIK: ' + user.nik"></p>
<p class="text-slate-500 truncate" x-text="user.address + (user.rt ? ', RT ' + user.rt + ' / RW ' + user.rw : '')"></p>
</div>
<div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs flex flex-col justify-between">
<div>
<p class="font-bold text-emerald-950">Status Data Penduduk</p>
<p class="text-emerald-800 font-mono" x-text="'No. KK: ' + (user.kk || '-')"></p>
</div>
<p class="text-emerald-700 font-semibold text-[11px]">✓ Profil Lengkap (Memenuhi Syarat BR-02)</p>
</div>
</div>
</template>

<div><label class="text-xs font-bold text-slate-700">Keperluan / Peruntukan Surat *</label><textarea x-model="purpose" rows="3" placeholder="Contoh: Pengajuan KUR Bank BRI untuk modal usaha warung sembako..." class="mt-1.5 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea></div>
<template x-if="service && service.id==='sku'"><div class="grid sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200">
<div class="sm:col-span-2"><p class="text-xs font-bold text-emerald-900">Detail Tambahan Usaha (Khusus SKU)</p></div>
<div><label class="text-xs font-bold text-slate-700">Nama Usaha</label><input x-model="business_name" placeholder="Warung Berkah Budi" class="mt-1 w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm"></div>
<div><label class="text-xs font-bold text-slate-700">Jenis Usaha</label><input x-model="business_type" placeholder="Sembako / Kuliner" class="mt-1 w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm"></div>
<div class="sm:col-span-2"><label class="text-xs font-bold text-slate-700">Alamat Usaha</label><input x-model="business_address" placeholder="Jl. Merpati No. 14" class="mt-1 w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm"></div>
</div></template>
<div>
    <label class="text-xs font-bold text-slate-700 block mb-1">Upload Berkas Persyaratan (PDF / JPG / PNG, Maks 2MB) *</label>
    <div class="mt-2 grid sm:grid-cols-3 gap-3 text-xs">
        
        <!-- File KTP -->
        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col justify-between"
             :class="files.ktp ? 'bg-emerald-50 border-emerald-400' : 'bg-slate-50 border-dashed border-slate-300 hover:border-emerald-400'">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-slate-800">1. Foto / Scan KTP *</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :class="files.ktp ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-600'" x-text="files.ktp ? 'Terunggah' : 'Wajib'"></span>
                </div>
                <template x-if="files.ktp">
                    <div class="text-[11px] text-emerald-900 bg-white p-2 rounded-xl border border-emerald-200 truncate">
                        <p class="font-bold truncate" x-text="files.ktp.name"></p>
                        <p class="text-[10px] text-slate-500" x-text="files.ktp.size"></p>
                    </div>
                </template>
                <template x-if="!files.ktp">
                    <p class="text-[11px] text-slate-400 mb-2">Pilih file e-KTP fisik asli yang jelas dan terang.</p>
                </template>
            </div>
            <div class="mt-3 flex gap-1.5">
                <label class="flex-1 text-center py-2 px-3 bg-white hover:bg-slate-100 text-emerald-700 font-bold border border-emerald-300 rounded-xl cursor-pointer text-[11px] transition-colors shadow-2xs">
                    <span x-text="files.ktp ? 'Ganti File' : 'Pilih File KTP'"></span>
                    <input id="file-ktp" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="handleFile('ktp', $event)" class="hidden">
                </label>
                <template x-if="files.ktp">
                    <button type="button" @click="removeFile('ktp')" class="p-2 text-rose-600 hover:bg-rose-100 rounded-xl" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- File KK -->
        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col justify-between"
             :class="files.kk ? 'bg-emerald-50 border-emerald-400' : 'bg-slate-50 border-dashed border-slate-300 hover:border-emerald-400'">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-slate-800">2. Kartu Keluarga (KK) *</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :class="files.kk ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-600'" x-text="files.kk ? 'Terunggah' : 'Wajib'"></span>
                </div>
                <template x-if="files.kk">
                    <div class="text-[11px] text-emerald-900 bg-white p-2 rounded-xl border border-emerald-200 truncate">
                        <p class="font-bold truncate" x-text="files.kk.name"></p>
                        <p class="text-[10px] text-slate-500" x-text="files.kk.size"></p>
                    </div>
                </template>
                <template x-if="!files.kk">
                    <p class="text-[11px] text-slate-400 mb-2">Foto / scan Kartu Keluarga tampak menyeluruh.</p>
                </template>
            </div>
            <div class="mt-3 flex gap-1.5">
                <label class="flex-1 text-center py-2 px-3 bg-white hover:bg-slate-100 text-emerald-700 font-bold border border-emerald-300 rounded-xl cursor-pointer text-[11px] transition-colors shadow-2xs">
                    <span x-text="files.kk ? 'Ganti File' : 'Pilih File KK'"></span>
                    <input id="file-kk" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="handleFile('kk', $event)" class="hidden">
                </label>
                <template x-if="files.kk">
                    <button type="button" @click="removeFile('kk')" class="p-2 text-rose-600 hover:bg-rose-100 rounded-xl" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- File Pengantar RT/RW -->
        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col justify-between"
             :class="files.pengantar ? 'bg-emerald-50 border-emerald-400' : 'bg-slate-50 border-dashed border-slate-300 hover:border-emerald-400'">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-slate-800">3. Surat Pengantar RT/RW</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :class="files.pengantar ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-600'" x-text="files.pengantar ? 'Terunggah' : 'Tambahan'"></span>
                </div>
                <template x-if="files.pengantar">
                    <div class="text-[11px] text-emerald-900 bg-white p-2 rounded-xl border border-emerald-200 truncate">
                        <p class="font-bold truncate" x-text="files.pengantar.name"></p>
                        <p class="text-[10px] text-slate-500" x-text="files.pengantar.size"></p>
                    </div>
                </template>
                <template x-if="!files.pengantar">
                    <p class="text-[11px] text-slate-400 mb-2">Surat pengantar bertanda tangan ketua RT/RW setempat.</p>
                </template>
            </div>
            <div class="mt-3 flex gap-1.5">
                <label class="flex-1 text-center py-2 px-3 bg-white hover:bg-slate-100 text-emerald-700 font-bold border border-emerald-300 rounded-xl cursor-pointer text-[11px] transition-colors shadow-2xs">
                    <span x-text="files.pengantar ? 'Ganti File' : 'Pilih Pengantar'"></span>
                    <input id="file-pengantar" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="handleFile('pengantar', $event)" class="hidden">
                </label>
                <template x-if="files.pengantar">
                    <button type="button" @click="removeFile('pengantar')" class="p-2 text-rose-600 hover:bg-rose-100 rounded-xl" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </template>
            </div>
        </div>

    </div>
</div>
<button type="submit" :disabled="submitting" class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl text-sm shadow-md transition-all">
    <span x-show="!submitting">Kirim Permohonan Sekarang</span>
    <span x-show="submitting">Mengirim permohonan...</span>
</button>
</form>
</div>
</div>
@endsection
