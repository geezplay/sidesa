@extends('layouts.app', ['role' => 'warga'])
@section('title', 'Riwayat Pengajuan')
@section('page_title', 'Riwayat & Pelacakan Surat')
@section('content')
<div class="space-y-6" x-data="{
    user: null,
    applications: [],
    revisingId: null,
    reviseNotes: '',
    init() {
        if (window.siadesaStore) {
            this.user = window.siadesaStore.getCurrentUser();
            window.siadesaStore.fetchMyApplications().then(all => {
                this.applications = all || [];
            });
        }
    },
    resubmit(id) {
        if (window.siadesaStore) {
            window.siadesaStore.updateApplicationStatus(id, 'DIAJUKAN', 'Dokumen perbaikan telah diunggah kembali oleh pemohon.').then(() => {
                window.siadesaStore.fetchMyApplications().then(all => {
                    this.applications = all || [];
                    this.revisingId = null;
                });
            });
        }
    }
}">
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="font-extrabold text-slate-900 text-base mb-1">Daftar Semua Permohonan Saya</h3>
        <p class="text-xs text-slate-500">Pantau nomor tiket, tindak lanjuti permintaan perbaikan dokumen, atau unduh surat resmi.</p>
    </div>
    <a href="{{ route('resident.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 shadow-xs">
        + Buat Pengajuan Baru
    </a>
</div>

<div class="space-y-4">
<template x-for="item in applications" :key="item.id">
<div class="p-5 rounded-2xl border border-slate-200 hover:border-slate-300 transition-all space-y-3">
<div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
<div>
<div class="flex items-center gap-2">
<span class="font-mono font-bold text-xs text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200" x-text="item.tracking_number"></span>
<span class="text-xs text-slate-400" x-text="item.created_at"></span>
</div>
<h4 class="font-bold text-slate-900 text-base mt-1" x-text="item.service_name"></h4>
<p class="text-xs text-slate-500" x-text="item.purpose"></p>
</div>
<div>
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
      :class="{
          'bg-sky-50 text-sky-700 border-sky-200': item.status === 'DIAJUKAN',
          'bg-blue-50 text-blue-700 border-blue-200': item.status === 'DIVERIFIKASI',
          'bg-amber-50 text-amber-700 border-amber-200': item.status === 'MENUNGGU PERSETUJUAN' || item.status === 'PERLU PERBAIKAN',
          'bg-indigo-50 text-indigo-700 border-indigo-200': item.status === 'DIPROSES',
          'bg-emerald-600 text-white border-emerald-600': item.status === 'SELESAI',
          'bg-rose-50 text-rose-700 border-rose-200': item.status === 'DITOLAK'
      }">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    <span x-text="item.status"></span>
</span>
</div>
</div>
<!-- Alert Perlu Perbaikan -->
<template x-if="item.status === 'PERLU PERBAIKAN'">
<div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
<p class="font-bold">Catatan Petugas Verifikator:</p>
<p class="mt-0.5" x-text="item.verifier_notes"></p>
<button @click="revisingId = item.id" class="mt-2 px-3 py-1.5 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700">Upload Ulang / Perbaiki Berkas Sekarang</button>
<div x-show="revisingId === item.id" class="mt-3 p-3 bg-white rounded-lg border border-amber-300">
<p class="font-semibold text-slate-700">Simulasi Unggah Berkas Baru:</p>
<input type="file" class="mt-1 block text-xs">
<div class="mt-2 flex gap-2">
<button @click="resubmit(item.id)" class="px-3 py-1 bg-emerald-700 text-white rounded text-xs font-bold">Kirim Perbaikan</button>
<button @click="revisingId = null" class="px-3 py-1 bg-slate-200 text-slate-700 rounded text-xs">Batal</button>
</div>
</div>
</div>
</template>
<div class="pt-3 border-t border-slate-100 flex flex-wrap justify-between items-center gap-2 text-xs">
<span class="text-slate-400">Pembaruan: <span x-text="item.updated_at"></span></span>
<div class="flex gap-2">
<a :href="'/tracking?kode=' + item.tracking_number" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-semibold">Timeline Detail</a>
<template x-if="item.status === 'SELESAI' || item.status === 'DIPROSES'">
<a :href="'/admin/surat/cetak/' + item.id" target="_blank" class="px-3 py-1.5 bg-emerald-700 text-white rounded-lg hover:bg-emerald-800 font-bold">Cetak Surat & QR</a>
</template>
</div>
</div>
</div>
</template>

<template x-if="applications.length === 0">
    <div class="p-8 text-center text-xs text-slate-500">
        <p>Anda belum memiliki riwayat pengajuan surat.</p>
    </div>
</template>
</div>
</div>
</div>
@endsection
