@extends('layouts.app', ['role' => 'kades'])
@section('title', 'Tinjauan Persetujuan')
@section('page_title', 'Keputusan Persetujuan Pimpinan')
@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    appId: '{{ $id }}',
    app: null,
    notes: '',
    errorMessage: '',
    decided: false,
    decisionType: '',
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchApplicationById(this.appId).then(a => {
                this.app = a;
            });
        }
    },
    approve() {
        this.errorMessage = '';
        if (window.siadesaStore) {
            window.siadesaStore.updateApplicationStatus(this.appId, 'DISETUJUI', this.notes || 'Disetujui untuk diterbitkan surat resmi.').then(updated => {
                if (updated && updated.id) {
                    this.app = updated;
                    this.decided = true;
                    this.decisionType = 'approved';
                } else {
                    this.errorMessage = (updated && updated.message) ? updated.message : 'Gagal menyetujui permohonan.';
                }
            });
        }
    },
    reject() {
        this.errorMessage = '';
        if (!this.notes.trim()) {
            this.errorMessage = 'Alasan penolakan pimpinan wajib dicatat sebelum menolak permohonan!';
            return;
        }
        if (window.siadesaStore) {
            window.siadesaStore.updateApplicationStatus(this.appId, 'DITOLAK', this.notes).then(updated => {
                if (updated && updated.id) {
                    this.app = updated;
                    this.decided = true;
                    this.decisionType = 'rejected';
                } else {
                    this.errorMessage = (updated && updated.message) ? updated.message : 'Gagal menolak permohonan.';
                }
            });
        }
    }
}">
<a href="{{ route('kades.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
Kembali ke meja approval
</a>

<template x-if="app">
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
<div class="border-b border-slate-100 pb-5">
<span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200" x-text="app.tracking_number"></span>
<h2 class="text-xl font-extrabold text-slate-900 mt-2" x-text="app.service_name"></h2>
<p class="text-xs text-slate-500 mt-0.5">Pemohon: <span class="font-bold text-slate-800" x-text="app.applicant_name"></span> (<span x-text="app.applicant_nik"></span>)</p>
</div>

<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
<p><span class="font-bold text-slate-700">Keperluan Pemohon:</span> <span x-text="app.purpose"></span></p>
<p><span class="font-bold text-slate-700">Catatan Petugas Loket:</span> <span class="text-emerald-700 font-semibold" x-text="app.verifier_notes"></span></p>
<p><span class="font-bold text-slate-700">Alamat Pemohon:</span> <span x-text="app.applicant_address"></span></p>
</div>

<template x-if="!decided">
<div class="space-y-4">
<template x-if="errorMessage">
    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span x-text="errorMessage"></span>
    </div>
</template>
<div>
<label class="text-xs font-bold text-slate-700">Catatan Tambahan Kepala Desa (Opsional bila disetujui, Wajib bila ditolak)</label>
<textarea x-model="notes" rows="2" placeholder="Catatan persetujuan..." class="mt-1.5 w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
</div>

<div class="grid sm:grid-cols-2 gap-3 pt-2">
<button @click="approve()" class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold rounded-xl text-sm shadow-md transition-all">
✓ Setujui & Terbitkan Nomor Surat
</button>
<button @click="reject()" class="w-full py-3.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 font-bold rounded-xl text-sm transition-all">
✕ Tolak Permohonan
</button>
</div>
</div>
</template>

<template x-if="decided">
<div class="p-6 rounded-2xl text-center space-y-3" :class="decisionType === 'approved' ? 'bg-emerald-50 border border-emerald-300' : 'bg-rose-50 border border-rose-300'">
<p class="font-extrabold text-base" :class="decisionType === 'approved' ? 'text-emerald-950' : 'text-rose-950'" x-text="decisionType === 'approved' ? 'Permohonan Berhasil Disetujui!' : 'Permohonan Ditolak'"></p>
<p class="text-xs text-slate-600">Surat otomatis diterbitkan dengan nomor resmi desa dan siap dicetak.</p>
<template x-if="decisionType === 'approved'">
<div class="pt-2 flex justify-center gap-2">
<a :href="'/admin/surat/cetak/' + app.id" target="_blank" class="px-5 py-2.5 bg-emerald-700 text-white text-xs font-bold rounded-xl hover:bg-emerald-800">Cetak Surat Resmi & QR</a>
<a href="{{ route('kades.dashboard') }}" class="px-5 py-2.5 bg-white text-slate-700 border text-xs font-bold rounded-xl hover:bg-slate-50">Selesai</a>
</div>
</template>
</div>
</template>

</div>
</template>
</div>
@endsection
