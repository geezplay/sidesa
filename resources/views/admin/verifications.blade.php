@extends('layouts.app', ['role' => 'admin'])

@section('title', 'Verifikasi Dokumen Pelayanan')
@section('page_title', 'Verifikasi Berkas Pengajuan')

@section('content')
<div class="space-y-6" x-data="{
    applications: [],
    filter: 'pending',
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchStaffApplications().then(list => {
                this.applications = list || [];
            });
        }
    },
    get queueList() {
        if (this.filter === 'pending') {
            return this.applications.filter(a => a.status === 'DIAJUKAN');
        } else if (this.filter === 'revision') {
            return this.applications.filter(a => a.status === 'PERLU PERBAIKAN');
        } else {
            return this.applications;
        }
    }
}">

    <!-- Filter Tab Card Metrik -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <button @click="filter = 'pending'" 
                :class="filter === 'pending' ? 'border-emerald-500 bg-emerald-50 text-emerald-950 shadow-xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'" 
                class="p-5 rounded-3xl border text-left transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Perlu Diverifikasi</p>
            <p class="text-3xl font-extrabold mt-1 text-emerald-800" x-text="applications.filter(a => a.status === 'DIAJUKAN').length"></p>
            <p class="text-[11px] text-emerald-700 font-medium mt-1">Pengajuan baru masuk</p>
        </button>

        <button @click="filter = 'revision'" 
                :class="filter === 'revision' ? 'border-amber-500 bg-amber-50 text-amber-950 shadow-xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'" 
                class="p-5 rounded-3xl border text-left transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu Perbaikan</p>
            <p class="text-3xl font-extrabold mt-1 text-amber-800" x-text="applications.filter(a => a.status === 'PERLU PERBAIKAN').length"></p>
            <p class="text-[11px] text-amber-700 font-medium mt-1">Instruksi perbaikan dikirim</p>
        </button>

        <button @click="filter = 'all'" 
                :class="filter === 'all' ? 'border-blue-500 bg-blue-50 text-blue-950 shadow-xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'" 
                class="p-5 rounded-3xl border text-left transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Semua Berkas Pelayanan</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900" x-text="applications.length"></p>
            <p class="text-[11px] text-slate-500 font-medium mt-1">Total seluruh permohonan</p>
        </button>
    </div>

    <!-- Tabel Antrean Verifikasi -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Antrean Pemeriksaan Dokumen Masuk</h3>
                <p class="text-xs text-slate-500">Pemeriksaan kesesuaian KTP, Kartu Keluarga, dan berkas persyaratan pemohon.</p>
            </div>
            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200" x-text="queueList.length + ' Dokumen Ditampilkan'"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3.5 px-6">Tiket & Tanggal</th>
                        <th class="py-3.5 px-6">Pemohon (Warga)</th>
                        <th class="py-3.5 px-6">Jenis Surat</th>
                        <th class="py-3.5 px-6">Status Terkini</th>
                        <th class="py-3.5 px-6 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in queueList" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-mono font-bold text-slate-900" x-text="item.tracking_number"></p>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="item.created_at"></p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-900" x-text="item.applicant_name"></p>
                                <p class="text-[11px] text-slate-500 font-mono" x-text="'NIK: ' + item.applicant_nik"></p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-800" x-text="item.service_name"></p>
                                <p class="text-[11px] text-slate-400 truncate max-w-xs" x-text="item.purpose"></p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                      :class="{
                                          'bg-sky-50 text-sky-700 border-sky-200': item.status === 'DIAJUKAN',
                                          'bg-blue-50 text-blue-700 border-blue-200': item.status === 'DIVERIFIKASI',
                                          'bg-amber-50 text-amber-700 border-amber-200': item.status === 'PERLU PERBAIKAN' || item.status === 'MENUNGGU PERSETUJUAN',
                                          'bg-indigo-50 text-indigo-700 border-indigo-200': item.status === 'DIPROSES',
                                          'bg-emerald-600 text-white border-emerald-600': item.status === 'SELESAI',
                                          'bg-rose-50 text-rose-700 border-rose-200': item.status === 'DITOLAK'
                                      }">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="item.status"></span>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a :href="'/admin/verifikasi/' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all inline-flex items-center gap-1.5">
                                    <span>Periksa Berkas</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <template x-if="queueList.length === 0">
            <div class="p-12 text-center text-slate-400 text-xs">
                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Tidak ada pengajuan pada kategori filter ini.
            </div>
        </template>
    </div>

</div>
@endsection
