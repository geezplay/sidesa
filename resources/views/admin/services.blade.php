@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Kelola Jenis Layanan')
@section('page_title', 'Konfigurasi Jenis Surat & Syarat')
@section('content')
<div class="space-y-6" x-data="{
    services: [],
    modalOpen: false,
    isCreating: false,
    newRequirementText: '',
    toastMessage: '',
    toastSuccess: true,
    form: {
        id: '',
        code: '',
        name: '',
        category: 'Surat Keterangan',
        description: '',
        estimation: '1 Hari Kerja',
        requires_approval: true,
        approver: 'Kepala Desa',
        is_active: true,
        requirements: []
    },
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchServices().then(raw => {
                this.services = (raw || []).map(s => ({
                    ...s,
                    requires_approval: true,
                    approver: 'Kepala Desa',
                    is_active: s.is_active === undefined ? true : s.is_active
                }));
            });
        }
    },
    showToast(msg, success = true) {
        this.toastMessage = msg;
        this.toastSuccess = success;
        setTimeout(() => {
            this.toastMessage = '';
        }, 3500);
    },
    openEditModal(item) {
        this.isCreating = false;
        this.newRequirementText = '';
        this.form = {
            id: item.id,
            code: item.code || '',
            name: item.name || '',
            category: item.category || 'Surat Keterangan',
            description: item.description || '',
            estimation: item.estimation || '1 Hari Kerja',
            requires_approval: true,
            approver: 'Kepala Desa',
            is_active: item.is_active === undefined ? true : item.is_active,
            requirements: Array.isArray(item.requirements) ? [...item.requirements] : []
        };
        this.modalOpen = true;
    },
    openCreateModal() {
        this.isCreating = true;
        this.newRequirementText = '';
        this.form = {
            id: 'srv-' + Date.now(),
            code: '',
            name: '',
            category: 'Surat Keterangan',
            description: '',
            estimation: '1 Hari Kerja',
            requires_approval: true,
            approver: 'Kepala Desa',
            is_active: true,
            requirements: [
                'Foto / Scan e-KTP Pemohon',
                'Foto / Scan Kartu Keluarga (KK)',
                'Surat Pengantar RT/RW Setempat'
            ]
        };
        this.modalOpen = true;
    },
    addRequirement() {
        const text = this.newRequirementText.trim();
        if (!text) return;
        this.form.requirements.push(text);
        this.newRequirementText = '';
    },
    removeRequirement(index) {
        this.form.requirements.splice(index, 1);
    },
    saveService() {
        if (!this.form.name.trim() || !this.form.code.trim()) {
            this.showToast('Nama Layanan dan Kode Surat wajib diisi!', false);
            return;
        }

        this.form.code = this.form.code.toUpperCase().trim();
        this.form.requires_approval = true;
        this.form.approver = 'Kepala Desa';

        const estimationDays = parseInt(String(this.form.estimation).replace(/[^0-9]/g, '')) || 1;
        const payload = {
            code: this.form.code,
            name: this.form.name,
            category: this.form.category,
            description: this.form.description,
            estimation_days: estimationDays,
            is_active: this.form.is_active,
            requirements: this.form.requirements
        };

        if (window.siadesaStore) {
            const op = this.isCreating
                ? window.siadesaStore.createService(payload)
                : window.siadesaStore.updateService(this.form.id, payload);

            op.then(res => {
                if (!res || res.success === false) {
                    this.showToast((res && res.message) ? res.message : 'Gagal menyimpan layanan.', false);
                    return;
                }
                this.services = window.siadesaStore.getServices();
                this.showToast(this.isCreating
                    ? 'Jenis layanan baru ' + this.form.name + ' berhasil ditambahkan!'
                    : 'Konfigurasi ' + this.form.name + ' berhasil diperbarui!');
                this.modalOpen = false;
            });
        }
    }
}">

    <!-- Alert / Toast Notifikasi -->
    <template x-if="toastMessage">
        <div class="fixed top-20 right-6 z-50 p-4 rounded-2xl shadow-xl border flex items-center gap-3 transition-all"
             :class="toastSuccess ? 'bg-emerald-800 text-white border-emerald-600' : 'bg-rose-800 text-white border-rose-600'">
            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span class="text-xs sm:text-sm font-bold" x-text="toastMessage"></span>
        </div>
    </template>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Jenis Layanan Administrasi Aktif</h3>
                <p class="text-xs text-slate-500">Kelola estimasi waktu pengerjaan, persyaratan berkas, dan verifikasi persetujuan Kepala Desa.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200" x-text="services.length + ' Jenis Surat'"></span>
                <button @click="openCreateModal()" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Layanan Baru</span>
                </button>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <template x-for="item in services" :key="item.id">
                <div class="p-5 rounded-2xl border border-slate-200 hover:border-emerald-300 transition-all space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-mono font-extrabold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" x-text="item.code"></span>
                                    <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded" x-text="item.category"></span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base mt-1.5" x-text="item.name"></h4>
                                <p class="text-xs text-slate-500 line-clamp-2 mt-0.5" x-text="item.description"></p>
                            </div>
                            <!-- Badge Approval Kepala Desa (Semua surat wajib approval Kades) -->
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap flex items-center gap-1 flex-shrink-0">
                                <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Approval Kades</span>
                            </span>
                        </div>

                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1.5 mt-3">
                            <p><span class="font-semibold text-slate-700">Estimasi:</span> <span class="font-bold text-slate-900" x-text="item.estimation"></span></p>
                            <p class="font-semibold text-slate-700">Persyaratan Dokumen:</p>
                            <ul class="list-disc ml-4 text-[11px] space-y-0.5">
                                <template x-for="(r, idx) in item.requirements" :key="idx">
                                    <li class="text-slate-600" x-text="r"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs pt-3 border-t border-slate-100">
                        <span class="inline-flex items-center gap-1.5 font-bold" :class="item.is_active !== false ? 'text-emerald-700' : 'text-slate-400'">
                            <span class="w-2 h-2 rounded-full" :class="item.is_active !== false ? 'bg-emerald-600' : 'bg-slate-300'"></span>
                            <span x-text="item.is_active !== false ? 'Status: Aktif' : 'Status: Non-Aktif'"></span>
                        </span>
                        <button @click="openEditModal(item)" class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-slate-200 text-slate-700 font-bold rounded-xl transition-all flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah Konfigurasi</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL UBAH / TAMBAH KONFIGURASI LAYANAN -->
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto" 
         style="display: none;">
        
        <div @click.outside="modalOpen = false" 
             class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-200 space-y-5 my-8">
            
            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-lg" x-text="isCreating ? 'Tambah Jenis Layanan Baru' : 'Ubah Konfigurasi Layanan'"></h3>
                    <p class="text-xs text-slate-500">Perbarui persyaratan, estimasi pengerjaan, dan ketentuan pengesahan surat.</p>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="saveService()" class="space-y-4 text-xs">
                
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Nama Layanan Surat *</label>
                        <input type="text" x-model="form.name" placeholder="Contoh: Surat Keterangan Usaha" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Kode Surat *</label>
                        <input type="text" x-model="form.code" placeholder="SKU" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Kategori Layanan</label>
                        <select x-model="form.category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Surat Keterangan">Surat Keterangan</option>
                            <option value="Kependudukan">Kependudukan</option>
                            <option value="Bantuan Sosial">Bantuan Sosial</option>
                            <option value="Pengantar">Pengantar</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Estimasi Waktu Pelayanan</label>
                        <select x-model="form.estimation" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="1 Hari Kerja">1 Hari Kerja</option>
                            <option value="2 Hari Kerja">2 Hari Kerja</option>
                            <option value="3 Hari Kerja">3 Hari Kerja</option>
                            <option value="5 Hari Kerja">5 Hari Kerja</option>
                            <option value="Selesai di Hari yang Sama">Selesai di Hari yang Sama</option>
                        </select>
                    </div>
                </div>

                <!-- Info Verifikasi & Approval Wajib Kepala Desa -->
                <div class="p-3.5 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 space-y-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <p class="font-extrabold text-xs">Persetujuan & Verifikasi: Kepala Desa (Wajib)</p>
                    </div>
                    <p class="text-[11px] text-purple-800 leading-relaxed">
                        Sesuai ketetapan tata kelola administrasi desa, seluruh permohonan surat wajib diverifikasi berkasnya oleh Admin lalu disetujui & disahkan oleh Kepala Desa agar nomor registrasi resmi dapat terbit.
                    </p>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Deskripsi Layanan</label>
                    <textarea x-model="form.description" rows="2" placeholder="Jelaskan peruntukan dan kegunaan surat keterangan ini..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <!-- Kelola Persyaratan Dokumen -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="font-bold text-slate-700">Daftar Persyaratan Dokumen Pemohon</label>
                        <span class="text-[10px] text-slate-400 font-mono" x-text="form.requirements.length + ' syarat'"></span>
                    </div>

                    <div class="space-y-1.5 max-h-40 overflow-y-auto p-1 bg-slate-50 rounded-xl border border-slate-200 mb-2">
                        <template x-for="(req, idx) in form.requirements" :key="idx">
                            <div class="flex items-center justify-between gap-2 p-2 bg-white rounded-lg border border-slate-200 text-slate-700">
                                <span class="font-medium truncate" x-text="req"></span>
                                <button type="button" @click="removeRequirement(idx)" class="text-rose-500 hover:text-rose-700 p-1 flex-shrink-0" title="Hapus syarat ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                        <div x-show="form.requirements.length === 0" class="p-3 text-center text-[11px] text-slate-400">
                            Belum ada persyaratan dokumen. Tambahkan di bawah.
                        </div>
                    </div>

                    <!-- Input Tambah Syarat -->
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="newRequirementText" 
                               @keydown.enter.prevent="addRequirement()"
                               placeholder="Ketik nama dokumen (contoh: Surat Pengantar RT/RW)..." 
                               class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button" @click="addRequirement()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold whitespace-nowrap">
                            + Tambah
                        </button>
                    </div>
                </div>

                <!-- Status Aktif / Non-Aktif -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <p class="font-bold text-slate-800">Status Layanan di Portal</p>
                        <p class="text-[11px] text-slate-500">Jika aktif, layanan ini akan muncul di katalog publik dan dashboard warga.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
