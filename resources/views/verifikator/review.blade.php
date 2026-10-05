@extends('layouts.app', ['role' => 'verifikator'])
@section('title', 'Pemeriksaan Dokumen')
@section('page_title', 'Periksa Kelengkapan Berkas')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    appId: '{{ $id }}',
    app: null,
    notes: '',
    actionMsg: '',
    init() {
        if (window.siadesaStore) {
            this.app = window.siadesaStore.getApplicationById(this.appId);
        }
    },
    act(newStatus) {
        if (newStatus === 'PERLU PERBAIKAN' || newStatus === 'DITOLAK') {
            if (!this.notes.trim()) {
                this.actionMsg = 'Catatan / alasan wajib diisi saat meminta perbaikan atau menolak.';
                return;
            }
        }
        if (window.siadesaStore) {
            const updated = window.siadesaStore.updateApplicationStatus(this.appId, newStatus, this.notes);
            this.app = updated;
            this.actionMsg = newStatus === 'MENUNGGU PERSETUJUAN'
                ? 'Berkas valid dan diteruskan ke meja Kepala Desa untuk persetujuan.'
                : (newStatus === 'PERLU PERBAIKAN'
                    ? 'Instruksi perbaikan terkirim ke pemohon.'
                    : 'Pengajuan telah ditolak dengan alasan.');
        }
    }
}">
<a href="{{ route('verifikator.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
Kembali ke antrean
</a>
<template x-if="app">
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
<div class="flex flex-col sm:flex-row justify-between gap-4 pb-5 border-b border-slate-100">
<div>
<span class="font-mono font-bold text-sm text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200" x-text="app.tracking_number"></span>
<h2 class="text-xl font-extrabold text-slate-900 mt-2" x-text="app.service_name"></h2>
<p class="text-xs text-slate-500 mt-0.5">Keperluan: <span class="font-semibold text-slate-700" x-text="app.purpose"></span></p>
</div>
<div class="text-sm">
<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border bg-slate-100 text-slate-700 border-slate-200">
<span class="w-2 h-2 rounded-full bg-current"></span><span x-text="app.status"></span>
</span>
</div>
</div>

<div class="grid sm:grid-cols-2 gap-4 my-5 text-xs">
<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
<p class="font-bold text-slate-800 text-sm mb-2">Profil Pemohon</p>
<p x-text="app.applicant_name" class="font-semibold text-slate-900"></p>
<p class="font-mono text-slate-500" x-text="app.applicant_nik"></p>
<p class="mt-1 text-slate-600" x-text="app.applicant_address"></p>
<p class="mt-1 text-slate-600" x-text="'WA: ' + app.applicant_phone"></p>
</div>
<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
<p class="font-bold text-slate-800 text-sm mb-2">Lampiran Berkas (Demo Check)</p>
<div class="space-y-2">
<div class="flex justify-between bg-white p-2.5 rounded-xl border"><span>Scan KTP (PDF/JPG)</span><span class="text-emerald-600 font-bold">Terlampir</span></div>
<div class="flex justify-between bg-white p-2.5 rounded-xl border"><span>Scan KK Terang & Jelas</span><span class="text-emerald-600 font-bold">Terlampir</span></div>
<div class="flex justify-between bg-white p-2.5 rounded-xl border"><span>Surat Pengantar RT/RW</span><span class="text-emerald-600 font-bold">Terlampir</span></div>
</div>
</div>
</div>

<label class="text-xs font-bold text-slate-700">Catatan Verifikasi / Alasan (Wajib jika Tolak atau Minta Perbaikan)</label>
<textarea x-model="notes" rows="3" placeholder="Contoh: Foto KK buram pada bagian tanda tangan, mohon upload ulang versi terang..." class="mt-1.5 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>

<template x-if="actionMsg"><div class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold" x-text="actionMsg"></div></template>

<div class="flex flex-col sm:flex-row gap-2 mt-5">
<button @click="act('MENUNGGU PERSETUJUAN')" class="flex-1 px-4 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-md transition-all">Verifikasi Sesuai, Teruskan ke Kades</button>
<button @click="act('PERLU PERBAIKAN')" class="flex-1 px-4 py-3 bg-white hover:bg-amber-50 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs transition-all">Minta Perbaikan Berkas</button>
<button @click="act('DITOLAK')" class="flex-1 px-4 py-3 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 font-bold rounded-xl text-xs transition-all">Tolak Pengajuan</button>
</div>
</div>
</template>
</div>
@endsection
