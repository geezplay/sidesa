@extends('layouts.public')

@section('title', 'Lacak Status Pengajuan Surat')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen" x-data="{
    code: '{{ $code }}',
    result: null,
    searched: false,
    init() {
        if (this.code) {
            this.search();
        }
    },
    search() {
        if (!this.code.trim()) return;
        this.searched = true;
        if (window.siadesaStore) {
            window.siadesaStore.trackApplication(this.code.trim()).then(res => {
                this.result = res;
            });
        }
    }
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-8">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Tracking Real-time</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Lacak Status Pengajuan Surat</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">
                Masukkan kode tiket tracking unik yang Anda terima saat mengirimkan permohonan.
            </p>
        </div>

        <!-- Search Box -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm mb-8">
            <form @submit.prevent="search()" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="code" 
                           placeholder="Contoh: ADM-20261005-000124" 
                           class="w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold tracking-wider uppercase placeholder:normal-case placeholder:font-normal focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="px-8 py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari Progres</span>
                </button>
            </form>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span>Coba nomor demo:</span>
                <button @click="code = 'ADM-20261005-000124'; search()" class="text-emerald-700 hover:underline font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">ADM-20261005-000124 (Proses)</button>
                <button @click="code = 'ADM-20261004-000119'; search()" class="text-amber-700 hover:underline font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">ADM-20261004-000119 (Revisi)</button>
                <button @click="code = 'ADM-20261003-000098'; search()" class="text-emerald-800 hover:underline font-semibold bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300">ADM-20261003-000098 (Selesai)</button>
            </div>
        </div>

        <!-- Search Result Card -->
        <template x-if="result">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md space-y-6">
                
                <!-- Header Result -->
                <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-6 border-b border-slate-100 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-slate-400">NOMOR PENGAJUAN:</span>
                            <span class="text-sm font-extrabold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200" x-text="result.tracking_number"></span>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-900" x-text="result.service_name"></h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pemohon: <span class="font-semibold text-slate-700" x-text="result.applicant_name"></span> (<span x-text="result.applicant_nik"></span>)</p>
                    </div>

                    <div class="text-right">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border"
                              :class="{
                                  'bg-sky-50 text-sky-700 border-sky-200': result.status === 'DIAJUKAN',
                                  'bg-blue-50 text-blue-700 border-blue-200': result.status === 'DIVERIFIKASI',
                                  'bg-amber-50 text-amber-700 border-amber-200': result.status === 'MENUNGGU PERSETUJUAN' || result.status === 'PERLU PERBAIKAN',
                                  'bg-indigo-50 text-indigo-700 border-indigo-200': result.status === 'DIPROSES',
                                  'bg-emerald-600 text-white border-emerald-600': result.status === 'SELESAI',
                                  'bg-rose-50 text-rose-700 border-rose-200': result.status === 'DITOLAK'
                              }">
                            <span class="w-2 h-2 rounded-full bg-current"></span>
                            <span x-text="result.status"></span>
                        </span>
                        <p class="text-[11px] text-slate-400 mt-1">Diajukan: <span x-text="result.created_at"></span></p>
                    </div>
                </div>

                <!-- Alert jika ada perbaikan -->
                <template x-if="result.status === 'PERLU PERBAIKAN'">
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex gap-3 text-amber-900">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <p class="font-bold text-xs uppercase tracking-wider">Perhatian: Berkas Perlu Diperbaiki</p>
                            <p class="text-xs mt-1" x-text="result.verifier_notes"></p>
                            <a :href="'/warga/pengajuan/riwayat'" class="inline-block mt-2 text-xs font-bold text-amber-800 underline">Masuk ke Portal Warga untuk Perbaiki Berkas</a>
                        </div>
                    </div>
                </template>

                <!-- Dokumen terbit jika SELESAI / DIPROSES -->
                <template x-if="result.letter_number">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Surat Resmi Diterbitkan</span>
                            <p class="font-bold text-slate-900 text-sm">Nomor Surat: <span class="font-mono text-emerald-800" x-text="result.letter_number"></span></p>
                            <p class="text-xs text-slate-500 mt-0.5">Kode Validasi: <span class="font-mono" x-text="result.qr_uuid"></span></p>
                        </div>
                        <div class="flex gap-2">
                            <a :href="'/verifikasi-surat/' + result.qr_uuid" class="px-4 py-2 bg-white text-emerald-800 border border-emerald-300 rounded-xl text-xs font-bold hover:bg-emerald-100 transition-colors">
                                Cek Keaslian QR
                            </a>
                            <a :href="'/admin/surat/cetak/' + result.id" target="_blank" class="px-4 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 transition-colors shadow-xs">
                                Pratinjau Surat
                            </a>
                        </div>
                    </div>
                </template>

                <!-- TIMELINE VERTICAL -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Riwayat Tahapan Pelayanan</h3>
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        <template x-for="(step, idx) in result.timeline" :key="idx">
                            <div class="relative">
                                <!-- Marker -->
                                <div class="absolute -left-[29px] top-0.5 w-4 h-4 rounded-full border-2 border-white shadow-xs"
                                     :class="{
                                         'bg-emerald-600': step.status === 'done',
                                         'bg-sky-500 animate-pulse': step.status === 'active',
                                         'bg-amber-500': step.status === 'warning',
                                         'bg-rose-500': step.status === 'danger',
                                         'bg-slate-300': step.status === 'pending'
                                     }"></div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900" x-text="step.title"></h4>
                                        <span class="text-[11px] text-slate-400 font-medium" x-text="step.time"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed" x-text="step.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </template>

        <!-- Not Found -->
        <template x-if="searched && !result">
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="font-bold text-slate-800 text-base">Nomor Pengajuan Tidak Ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Pastikan kode yang Anda masukkan sesuai format (contoh: ADM-20261005-000124).</p>
            </div>
        </template>

    </div>
</div>
@endsection
