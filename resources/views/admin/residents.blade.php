@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Master Data Penduduk')
@section('page_title', 'Database Kependudukan Desa')
@section('content')

<script>
window.residentsManager = function() {
    return {
        residents: [],
        search: '',
        modalOpen: false,
        editModalOpen: false,
        importModalOpen: false,
        importText: '',
        importPreview: [],
        importResult: '',
        errorMessage: '',
        editErrorMessage: '',
        importFileName: '',
        importHeaders: [],
        importRows: [],
        importMapping: { nik: '', kk: '', name: '', rt: '', rw: '' },
        newRes: { nik: '', name: '', rt: '001', rw: '001', kk: '', birth_place: '', birth_date: '', gender: 'Laki-Laki', address: '', religion: 'Islam', marital: 'Kawin', job: 'Wiraswasta', phone: '' },
        editRes: { nik: '', name: '', rt: '', rw: '', kk: '', birth_place: '', birth_date: '', gender: 'Laki-Laki', address: '', religion: 'Islam', marital: 'Kawin', job: '', phone: '' },
        
        checkHasAccount(nik) {
            return window.siadesaStore ? window.siadesaStore.isResidentRegistered(nik) : false;
        },
        init() {
            if (window.siadesaStore) {
                window.siadesaStore.fetchResidents().then(list => {
                    this.residents = list || [];
                });
            }
        },
        get filtered() {
            const q = (this.search || '').toLowerCase();
            return this.residents.filter(r => 
                (r.name || '').toLowerCase().includes(q) ||
                (r.nik || '').includes(q) ||
                (r.kk || '').includes(q)
            );
        },
        saveResident() {
            this.errorMessage = '';
            if (!this.newRes.nik || this.newRes.nik.length !== 16 || isNaN(this.newRes.nik)) {
                this.errorMessage = 'Nomor NIK wajib tepat 16 digit angka!';
                return;
            }
            if (!this.newRes.name.trim()) {
                this.errorMessage = 'Nama lengkap penduduk wajib diisi!';
                return;
            }
            if (window.siadesaStore) {
                window.siadesaStore.addResident({...this.newRes}).then(res => {
                    if (!res || res.success === false) {
                        this.errorMessage = (res && res.message) ? res.message : 'Gagal menambah penduduk.';
                        return;
                    }
                    this.residents = window.siadesaStore.getResidents();
                    this.modalOpen = false;
                    this.newRes = { nik: '', name: '', rt: '001', rw: '001', kk: '', birth_place: '', birth_date: '', gender: 'Laki-Laki', address: '', religion: 'Islam', marital: 'Kawin', job: 'Wiraswasta', phone: '' };
                });
            }
        },
        openEdit(r) {
            this.editErrorMessage = '';
            this.editRes = { ...r };
            this.editModalOpen = true;
        },
        saveEdit() {
            this.editErrorMessage = '';
            if (!this.editRes.name.trim()) {
                this.editErrorMessage = 'Nama lengkap penduduk wajib diisi!';
                return;
            }
            if (window.siadesaStore) {
                window.siadesaStore.updateResident(this.editRes.nik, this.editRes).then(res => {
                    if (!res || res.success === false) {
                        this.editErrorMessage = (res && res.message) ? res.message : 'Gagal memperbarui data.';
                        return;
                    }
                    this.residents = window.siadesaStore.getResidents();
                    this.editModalOpen = false;
                });
            }
        },
        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.importFileName = file.name || '';
            this.importResult = '';
            try {
                const lower = (file.name || '').toLowerCase();
                if (lower.endsWith('.csv')) {
                    const text = await file.text();
                    this.importText = text;
                    this.parseImportText();
                } else {
                    const buffer = await file.arrayBuffer();
                    const workbook = window.XLSX.read(buffer, { type: 'array' });
                    const firstSheet = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[firstSheet];
                    const rows = window.XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '', raw: false });
                    this.parseSheetRows(rows);
                }
            } catch (e) {
                this.importPreview = [];
                this.importRows = [];
                this.importHeaders = [];
                this.importResult = 'File tidak dapat dibaca. Gunakan template Excel (.xlsx) atau file .csv.';
            }
        },
        parseSheetRows(rows) {
            const cleaned = (rows || []).filter(r => Array.isArray(r) && r.some(c => String(c || '').trim() !== ''));
            if (cleaned.length < 2) {
                this.importHeaders = [];
                this.importRows = [];
                this.importPreview = [];
                this.importResult = 'File kosong. Isi data mulai baris ke-2 di bawah header.';
                return;
            }
            const normalize = (value) => String(value || '').trim().toLowerCase().replace(/[^a-z]/g, '');
            const headers = cleaned[0].map((h, index) => ({ index, label: String(h || '').trim(), key: normalize(h) }));
            this.importHeaders = headers;
            this.importRows = cleaned.slice(1);
            const findByKeys = (keys) => headers.find(h => keys.includes(h.key));
            this.importMapping = {
                nik: (findByKeys(['nik', 'nikktp', 'nomorindukkependudukan']) || {}).index ?? '',
                kk: (findByKeys(['kk', 'nokk', 'nomorkk', 'nomorkartukeluarga', 'kartukeluarga']) || {}).index ?? '',
                name: (findByKeys(['name', 'nama', 'namalengkap', 'namasesuaiktp']) || {}).index ?? '',
                rt: (findByKeys(['rt', 'rtrw']) || {}).index ?? '',
                rw: (findByKeys(['rw']) || {}).index ?? ''
            };
            this.buildPreviewFromMapping();
        },
        parseImportText() {
            const text = (this.importText || '').trim();
            if (!text) {
                this.importHeaders = [];
                this.importRows = [];
                this.importPreview = [];
                this.importResult = 'Tempel data CSV terlebih dahulu, lalu klik Pratinjau.';
                return;
            }
            const delimiter = text.includes(';') && !text.includes(',') ? ';' : ',';
            const lines = text.split('\n').map(line => line.split(delimiter).map(cell => cell.trim().replace(/^["']|["']$/g, '')));
            this.parseSheetRows(lines);
        },
        cleanCell(value, field) {
            let text = String(value ?? '').trim().replace(/^["']|["']$/g, '');
            if (field === 'nik' || field === 'kk') {
                text = text.replace(/[^0-9]/g, '');
            }
            if (field === 'rt' || field === 'rw') {
                text = text.replace(/[^0-9]/g, '').padStart(3, '0').slice(-3);
                if (!text || text === '000') text = '001';
            }
            return text;
        },
        buildPreviewFromMapping() {
            this.importResult = '';
            const rows = this.importRows || [];
            const map = this.importMapping || {};
            if (map.nik === '' || map.name === '' || map.rt === '' || map.rw === '') {
                this.importPreview = [];
                this.importResult = 'Petakan dulu kolom file ke NIK, Nomor KK, Nama, RT, dan RW.';
                return;
            }
            const parsed = [];
            const invalidRows = [];
            rows.forEach((row, rowNumber) => {
                const nik = this.cleanCell(row[map.nik], 'nik');
                const kk = map.kk !== '' ? this.cleanCell(row[map.kk], 'kk') : '';
                const name = this.cleanCell(row[map.name], 'name');
                const rt = this.cleanCell(row[map.rt], 'rt');
                const rw = this.cleanCell(row[map.rw], 'rw');
                if (nik.length === 16 && name) {
                    parsed.push({ nik, kk, name, rt: rt || '001', rw: rw || '001' });
                } else if (nik || name) {
                    invalidRows.push(rowNumber + 2);
                }
            });
            this.importPreview = parsed;
            if (parsed.length === 0) {
                this.importResult = 'Tidak ada baris valid. NIK harus 16 digit angka dan nama tidak boleh kosong.';
            } else if (invalidRows.length > 0) {
                this.importResult = parsed.length + ' baris siap diimport. ' + invalidRows.length + ' baris dilewati karena NIK/nama tidak valid (baris ' + invalidRows.slice(0, 8).join(', ') + ').';
            } else {
                this.importResult = parsed.length + ' baris valid siap diimport.';
            }
        },
        loadSampleData() {
            this.importHeaders = [
                { index: 0, label: 'nik', key: 'nik' },
                { index: 1, label: 'kk', key: 'kk' },
                { index: 2, label: 'name', key: 'name' },
                { index: 3, label: 'rt', key: 'rt' },
                { index: 4, label: 'rw', key: 'rw' }
            ];
            this.importRows = [
                ['3201121010950003', '3201121005110022', 'Rina Herlina', '002', '003'],
                ['3201122506880008', '3201121807090044', 'Agus Setiawan', '001', '004'],
                ['3201120909990005', '3201121203150088', 'Dimas Pratama', '003', '002']
            ];
            this.importMapping = { nik: 0, kk: 1, name: 2, rt: 3, rw: 4 };
            this.buildPreviewFromMapping();
        },
        applyImport() {
            if (this.importPreview.length === 0) return;
            if (window.siadesaStore) {
                window.siadesaStore.importResidents(this.importPreview).then(res => {
                    this.residents = window.siadesaStore.getResidents();
                    if (res && res.success) {
                        this.importResult = '✓ Sukses! ' + res.importedCount + ' data baru ditambahkan ke master, ' + res.updatedCount + ' data diperbarui.';
                        this.importPreview = [];
                        this.importRows = [];
                        this.importHeaders = [];
                        this.importText = '';
                    } else {
                        this.importResult = (res && res.message) ? res.message : 'Gagal mengimpor data.';
                    }
                });
            }
        }
    };
};
</script>

<div class="space-y-6" x-data="residentsManager()">

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
<div>
<h3 class="font-extrabold text-slate-900 text-lg">Basis Data Penduduk Desa Sukamaju</h3>
<p class="text-xs text-slate-500">Master data kependudukan awal (Nama, NIK, RT/RW). Penduduk wajib terdaftar di sini sebelum dapat registrasi akun di website.</p>
</div>
<div class="flex flex-wrap gap-2">
<a href="/templates/template_data_penduduk.xlsx" download="template_data_penduduk.xlsx" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold border border-emerald-300 shadow-xs flex items-center gap-1.5 transition-colors">
<svg class="w-4 h-4 text-emerald-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>
<span>Download Template Excel (.xlsx)</span>
</a>
<button @click="importModalOpen = true" class="px-4 py-2.5 bg-white text-emerald-800 border border-emerald-300 hover:bg-emerald-50 rounded-xl text-xs font-bold shadow-xs flex items-center gap-2 transition-colors">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
<span>Import Data (Excel / CSV)</span>
</button>
<button @click="modalOpen = true" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-2 transition-colors">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
<span>Tambah Penduduk</span>
</button>
</div>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-4 sm:p-6">
<div class="mb-4 flex flex-col sm:flex-row gap-2">
<input type="text" x-model="search" placeholder="Cari NIK, No KK, atau Nama Penduduk..." class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
<div class="text-xs text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 flex items-center gap-2 whitespace-nowrap">
<span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Sudah Daftar Akun
<span class="w-2.5 h-2.5 rounded-full bg-amber-400 ml-2"></span> Belum Daftar (Siap Registrasi)
</div>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left text-xs sm:text-sm">
<thead>
<tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
<th class="py-3 px-4">NIK (KTP)</th>
<th class="py-3 px-4">Nama Lengkap</th>
<th class="py-3 px-4">RT / RW</th>
<th class="py-3 px-4">Status Akun Website</th>
<th class="py-3 px-4 text-right">Aksi</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<template x-for="r in filtered" :key="r.nik">
<tr class="hover:bg-slate-50 transition-colors">
<td class="py-3.5 px-4 font-mono font-bold text-slate-900" x-text="r.nik"></td>
<td class="py-3.5 px-4">
<p class="font-bold text-slate-800" x-text="r.name"></p>
<p class="text-[11px] text-slate-400" x-text="r.address || ('RT ' + (r.rt||'-') + ' / RW ' + (r.rw||'-'))"></p>
</td>
<td class="py-3.5 px-4 text-slate-700 font-mono font-semibold" x-text="(r.rt || '-') + ' / ' + (r.rw || '-')"></td>
<td class="py-3.5 px-4">
<template x-if="checkHasAccount(r.nik)">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
<svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
<span>Sudah Punya Akun</span>
</span>
</template>
<template x-if="!checkHasAccount(r.nik)">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
<span>Belum Daftar</span>
</span>
</template>
</td>
<td class="py-3.5 px-4 text-right">
<button @click="openEdit(r)" class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded-lg text-[11px] font-bold transition-colors inline-flex items-center gap-1">
<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
<span>Edit</span>
</button>
</td>
</tr>
</template>
</tbody>
</table>
</div>
</div>

<!-- Modal Tambah Penduduk Tunggal -->
<div x-show="modalOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
<div @click.outside="modalOpen = false" class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
<h3 class="font-bold text-slate-900 text-base">Tambah Data Penduduk Induk</h3>
<p class="text-xs text-slate-500">Input data dasar kependudukan agar warga yang bersangkutan dapat mendaftarkan akun di website.</p>
<template x-if="errorMessage">
    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span x-text="errorMessage"></span>
    </div>
</template>
<div class="space-y-3 text-xs">
<div><label class="font-bold text-slate-700">NIK (16 Digit) *</label><input x-model="newRes.nik" maxlength="16" placeholder="3201xxxxxxxxxxxx" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono text-xs"></div>
<div><label class="font-bold text-slate-700">Nama Lengkap (Sesuai KTP) *</label><input x-model="newRes.name" placeholder="Nama Lengkap" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl text-xs"></div>
<div class="grid grid-cols-2 gap-2">
<div><label class="font-bold text-slate-700">RT (3 Digit) *</label><input x-model="newRes.rt" maxlength="3" placeholder="001" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl text-center font-mono text-xs"></div>
<div><label class="font-bold text-slate-700">RW (3 Digit) *</label><input x-model="newRes.rw" maxlength="3" placeholder="001" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl text-center font-mono text-xs"></div>
</div>
</div>
<div class="flex justify-end gap-2 pt-2 border-t">
<button @click="modalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
<button @click="saveResident()" class="px-4 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800">Simpan Penduduk</button>
</div>
</div>
</div>

<!-- Modal Edit Data Penduduk -->
<div x-show="editModalOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
<div @click.outside="editModalOpen = false" class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
<div class="flex justify-between items-center">
<h3 class="font-bold text-slate-900 text-base">Edit Data Penduduk</h3>
<span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-bold" x-text="editRes.nik"></span>
</div>
<template x-if="editErrorMessage">
    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="editErrorMessage"></div>
</template>
<div class="space-y-3 text-xs">
<div><label class="font-bold text-slate-700">Nama Lengkap *</label><input x-model="editRes.name" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl"></div>
<div class="grid grid-cols-2 gap-2">
<div><label class="font-bold text-slate-700">RT (3 Digit)</label><input x-model="editRes.rt" maxlength="3" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl text-center font-mono"></div>
<div><label class="font-bold text-slate-700">RW (3 Digit)</label><input x-model="editRes.rw" maxlength="3" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl text-center font-mono"></div>
</div>
<div class="grid grid-cols-2 gap-2">
<div><label class="font-bold text-slate-700">Nomor KK (Opsional)</label><input x-model="editRes.kk" maxlength="16" placeholder="3201xxxxxxxxxxxx" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono"></div>
<div><label class="font-bold text-slate-700">No. WhatsApp (Opsional)</label><input x-model="editRes.phone" placeholder="0812..." class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl"></div>
</div>
<div><label class="font-bold text-slate-700">Alamat Domisili</label><input x-model="editRes.address" placeholder="Kp. Sukamaju..." class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl"></div>
</div>
<div class="flex justify-end gap-2 pt-2 border-t">
<button @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
<button @click="saveEdit()" class="px-4 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800">Simpan Perubahan</button>
</div>
</div>
</div>

<!-- Modal Import Excel / CSV: nik, kk, name, rt, rw -->
<div x-show="importModalOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
<div @click.outside="importModalOpen = false" class="bg-white rounded-3xl p-6 sm:p-7 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[92vh] overflow-y-auto">
<div class="flex justify-between items-start pb-3 border-b border-slate-100">
<div>
<h3 class="font-extrabold text-slate-900 text-base">Import Data Penduduk</h3>
<p class="text-xs text-slate-500 mt-0.5">Upload file <strong>Excel (.xlsx) atau CSV (.csv)</strong> — setiap kolom terpisah rapi: <strong>nik | kk | name | rt | rw</strong>.</p>
</div>
<button @click="importModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
</div>

<!-- 3 Langkah Mudah -->
<div class="grid sm:grid-cols-3 gap-2 text-xs">
<div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
<p class="font-extrabold text-slate-900 mb-1">1. Unduh Template</p>
<p class="text-slate-500 leading-relaxed">Klik tombol di bawah, isi NIK, No. KK, Nama, RT, RW — di kolom terpisah (A, B, C, D, E).</p>
<a href="/templates/template_data_penduduk.xlsx" download="template_data_penduduk.xlsx" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold transition-colors">
<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>
<span>Template .xlsx</span>
</a>
</div>
<div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
<p class="font-extrabold text-slate-900 mb-1">2. Upload File</p>
<p class="text-slate-500 leading-relaxed">Pilih file Excel/CSV yang sudah diisi. Sistem otomatis membaca kolomnya.</p>
<button type="button" @click="loadSampleData()" class="mt-2 text-[11px] font-bold text-emerald-700 hover:text-emerald-900 underline">atau klik: isi contoh data</button>
</div>
<div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
<p class="font-extrabold text-slate-900 mb-1">3. Petakan & Import</p>
<p class="text-slate-500 leading-relaxed">Cocokkan kolom file ke NIK, KK, Nama, RT, RW — lalu simpan ke database.</p>
</div>
</div>

<!-- Upload File -->
<div>
<label class="text-xs font-bold text-slate-700 block mb-1.5">Pilih File Excel (.xlsx) atau CSV (.csv)</label>
<input type="file" accept=".xlsx,.xls,.csv" @change="handleFileUpload($event)" class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-700 file:text-white hover:file:bg-emerald-800 cursor-pointer border-2 border-dashed border-slate-300 hover:border-emerald-400 rounded-2xl p-2 bg-slate-50 transition-colors">
<template x-if="importFileName">
<p class="text-[11px] text-slate-500 mt-1">File terpilih: <span class="font-mono font-bold text-slate-700" x-text="importFileName"></span></p>
</template>
</div>

<!-- Pemetaan Kolom -->
<template x-if="importHeaders.length > 0">
<div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 space-y-3">
<p class="text-xs font-extrabold text-emerald-950">Cocokkan Kolom File ke Data Penduduk</p>
<div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
<div>
<label class="font-bold text-slate-700 block mb-1">Kolom NIK *</label>
<select x-model="importMapping.nik" @change="buildPreviewFromMapping()" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl font-mono">
<option value="">— pilih —</option>
<template x-for="h in importHeaders" :key="'nik-' + h.index">
<option :value="h.index" x-text="'Kolom ' + String.fromCharCode(65 + h.index) + ' — ' + h.label"></option>
</template>
</select>
</div>
<div>
<label class="font-bold text-slate-700 block mb-1">Nomor KK (Opsional)</label>
<select x-model="importMapping.kk" @change="buildPreviewFromMapping()" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl font-mono">
<option value="">— kosongkan —</option>
<template x-for="h in importHeaders" :key="'kk-' + h.index">
<option :value="h.index" x-text="'Kolom ' + String.fromCharCode(65 + h.index) + ' — ' + h.label"></option>
</template>
</select>
</div>
<div>
<label class="font-bold text-slate-700 block mb-1">Kolom Nama *</label>
<select x-model="importMapping.name" @change="buildPreviewFromMapping()" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl">
<option value="">— pilih —</option>
<template x-for="h in importHeaders" :key="'name-' + h.index">
<option :value="h.index" x-text="'Kolom ' + String.fromCharCode(65 + h.index) + ' — ' + h.label"></option>
</template>
</select>
</div>
<div>
<label class="font-bold text-slate-700 block mb-1">Kolom RT *</label>
<select x-model="importMapping.rt" @change="buildPreviewFromMapping()" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl font-mono">
<option value="">— pilih —</option>
<template x-for="h in importHeaders" :key="'rt-' + h.index">
<option :value="h.index" x-text="'Kolom ' + String.fromCharCode(65 + h.index) + ' — ' + h.label"></option>
</template>
</select>
</div>
<div>
<label class="font-bold text-slate-700 block mb-1">Kolom RW *</label>
<select x-model="importMapping.rw" @change="buildPreviewFromMapping()" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl font-mono">
<option value="">— pilih —</option>
<template x-for="h in importHeaders" :key="'rw-' + h.index">
<option :value="h.index" x-text="'Kolom ' + String.fromCharCode(65 + h.index) + ' — ' + h.label"></option>
</template>
</select>
</div>
</div>
</div>
</template>

<!-- Tempel Teks CSV Manual (opsional) -->
<details class="text-xs">
<summary class="cursor-pointer font-bold text-slate-600 hover:text-emerald-700">Tidak punya file? Tempel teks CSV manual (opsional)</summary>
<div class="mt-2 space-y-2">
<textarea x-model="importText" rows="4" placeholder="nik,kk,name,rt,rw&#10;3201121508900001,3201122005080015,Budi Santoso,002,005" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
<button type="button" @click="parseImportText()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold">
Pratinjau Teks CSV
</button>
</div>
</details>

<template x-if="importResult">
<div class="p-3 rounded-xl text-xs font-semibold" :class="importResult.includes('✓') || importResult.includes('Sukses') || importResult.includes('siap diimport') || importResult.includes('valid siap') ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-700'" x-text="importResult"></div>
</template>

<!-- Tabel Pratinjau -->
<template x-if="importPreview.length > 0">
<div class="space-y-2">
<p class="text-xs font-bold text-slate-700">Pratinjau Data (<span x-text="importPreview.length"></span> baris valid)</p>
<div class="overflow-x-auto border border-slate-200 rounded-2xl max-h-52 overflow-y-auto">
<table class="w-full text-left text-xs">
<thead>
<tr class="bg-emerald-700 text-white font-bold sticky top-0">
<th class="py-2 px-3">No</th>
<th class="py-2 px-3">NIK</th>
<th class="py-2 px-3">No. KK</th>
<th class="py-2 px-3">Nama</th>
<th class="py-2 px-3">RT / RW</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 bg-white">
<template x-for="(row, idx) in importPreview" :key="idx">
<tr class="hover:bg-emerald-50/50">
<td class="py-1.5 px-3 text-slate-400 text-[11px]" x-text="idx + 1"></td>
<td class="py-1.5 px-3 font-mono font-bold text-slate-900" x-text="row.nik"></td>
<td class="py-1.5 px-3 font-mono text-slate-600" x-text="row.kk || '-'"></td>
<td class="py-1.5 px-3 font-semibold text-slate-800" x-text="row.name"></td>
<td class="py-1.5 px-3 font-mono text-slate-600" x-text="row.rt + ' / ' + row.rw"></td>
</tr>
</template>
</tbody>
</table>
</div>
<button type="button" @click="applyImport()" class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-extrabold shadow-md flex items-center justify-center gap-2 transition-colors">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
<span>Simpan <span x-text="importPreview.length"></span> Data ke Database Desa</span>
</button>
</div>
</template>

</div>
</div>

</div>
@endsection
