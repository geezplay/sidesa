@extends('layouts.app', ['role' => 'verifikator'])
@section('title', 'Antrean Verifikasi Dokumen')
@section('page_title', 'Loket Verifikasi Pelayanan')
@section('content')
<div class="space-y-6" x-data="{
    applications: [],
    filter: 'pending',
    init() {
        if (window.siadesaStore) {
            this.applications = window.siadesaStore.getApplications();
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
<div class="grid grid-cols-3 gap-4">
<button @click="filter = 'pending'" :class="filter === 'pending' ? 'border-emerald-500 bg-emerald-50 text-emerald-950' : 'border-slate-200 bg-white text-slate-700'" class="p-4 rounded-2xl border text-left transition-all">
<p class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu Verifikasi</p>
<p class="text-2xl font-extrabold mt-1" x-text="applications.filter(a => a.status === 'DIAJUKAN').length"></p>
</button>
<button @click="filter = 'revision'" :class="filter === 'revision' ? 'border-amber-500 bg-amber-50 text-amber-950' : 'border-slate-200 bg-white text-slate-700'" class="p-4 rounded-2xl border text-left transition-all">
<p class="text-xs font-bold uppercase tracking-wider text-slate-500">Perlu Perbaikan</p>
<p class="text-2xl font-extrabold mt-1" x-text="applications.filter(a => a.status === 'PERLU PERBAIKAN').length"></p>
</button>
<button @click="filter = 'all'" :class="filter === 'all' ? 'border-blue-500 bg-blue-50 text-blue-950' : 'border-slate-200 bg-white text-slate-700'" class="p-4 rounded-2xl border text-left transition-all">
<p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Semua Berkas</p>
<p class="text-2xl font-extrabold mt-1" x-text="applications.length"></p>
</button>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
<div class="p-6 border-b border-slate-100 flex justify-between items-center">
<div><h3 class="font-extrabold text-slate-900 text-base">Antrean Berkas Pengajuan Masuk</h3><p class="text-xs text-slate-500">Pemeriksaan kelengkapan KTP, KK, dan syarat spesifik</p></div>
<span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200" x-text="queueList.length + ' Berkas'"></span>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left text-xs sm:text-sm">
<thead>
<tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
<th class="py-3.5 px-6">Tiket & Tanggal</th>
<th class="py-3.5 px-6">Pemohon</th>
<th class="py-3.5 px-6">Jenis Layanan</th>
<th class="py-3.5 px-6">Status Saat Ini</th>
<th class="py-3.5 px-6 text-right">Aksi Verifikasi</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<template x-for="item in queueList" :key="item.id">
<tr class="hover:bg-slate-50/80 transition-colors">
<td class="py-4 px-6"><p class="font-mono font-bold text-slate-900" x-text="item.tracking_number"></p><p class="text-[11px] text-slate-400" x-text="item.created_at"></p></td>
<td class="py-4 px-6"><p class="font-bold text-slate-900" x-text="item.applicant_name"></p><p class="text-[11px] text-slate-500 font-mono" x-text="item.applicant_nik"></p></td>
<td class="py-4 px-6"><p class="font-semibold text-slate-800" x-text="item.service_name"></p><p class="text-[11px] text-slate-400 truncate max-w-xs" x-text="item.purpose"></p></td>
<td class="py-4 px-6">
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
      :class="{
          'bg-sky-50 text-sky-700 border-sky-200': item.status === 'DIAJUKAN',
          'bg-amber-50 text-amber-700 border-amber-200': item.status === 'PERLU PERBAIKAN' || item.status === 'MENUNGGU PERSETUJUAN',
          'bg-indigo-50 text-indigo-700 border-indigo-200': item.status === 'DIPROSES',
          'bg-emerald-600 text-white border-emerald-600': item.status === 'SELESAI',
          'bg-rose-50 text-rose-700 border-rose-200': item.status === 'DITOLAK'
      }">
<span x-text="item.status"></span>
</span>
</td>
<td class="py-4 px-6 text-right">
<a :href="'/verifikator/review/' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs">
Periksa Berkas →
</a>
</td>
</tr>
</template>
</tbody>
</table>
</div>
</div>
</div>
@endsection
