@extends('layouts.app', ['role' => 'kades'])
@section('title', 'Approval Kepala Desa')
@section('page_title', 'Menunggu Persetujuan Pimpinan')
@section('content')
<div class="space-y-6" x-data="{
    applications: [],
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchApprovals().then(list => {
                this.applications = (list || []).filter(a => a.status === 'MENUNGGU PERSETUJUAN');
            });
        }
    }
}">
<div class="bg-gradient-to-r from-emerald-900 to-emerald-950 p-6 sm:p-8 rounded-3xl text-white shadow-md">
<p class="text-xs uppercase font-bold tracking-wider text-emerald-300">Ruang Kerja Pimpinan Desa</p>
<h2 class="text-2xl font-extrabold mt-1">Persetujuan & Tanda Tangan Surat</h2>
<p class="text-xs text-emerald-100/80 mt-1 max-w-xl">Hanya permohonan yang telah diverifikasi lengkap oleh petugas loket yang masuk ke meja persetujuan ini.</p>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
<div class="p-6 border-b border-slate-100 flex justify-between items-center">
<h3 class="font-extrabold text-slate-900 text-base">Daftar Menunggu Tanda Tangan (<span x-text="applications.length"></span>)</h3>
<span class="text-xs text-slate-400">Verifikasi Loket Selesai</span>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left text-xs sm:text-sm">
<thead>
<tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
<th class="py-3.5 px-6">Tiket</th>
<th class="py-3.5 px-6">Nama Pemohon</th>
<th class="py-3.5 px-6">Jenis Surat</th>
<th class="py-3.5 px-6">Catatan Verifikator</th>
<th class="py-3.5 px-6 text-right">Keputusan</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<template x-for="item in applications" :key="item.id">
<tr class="hover:bg-slate-50/80 transition-colors">
<td class="py-4 px-6 font-mono font-bold text-slate-900" x-text="item.tracking_number"></td>
<td class="py-4 px-6"><p class="font-bold text-slate-800" x-text="item.applicant_name"></p><p class="text-[11px] text-slate-400 font-mono" x-text="item.applicant_nik"></p></td>
<td class="py-4 px-6"><span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" x-text="item.service_code"></span> <span class="font-semibold text-slate-800 ml-1" x-text="item.service_name"></span></td>
<td class="py-4 px-6 text-slate-600 italic" x-text="item.verifier_notes || 'Lolos verifikasi loket' "></td>
<td class="py-4 px-6 text-right">
<a :href="'/kades/review/' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs">
Tinjau & Setujui →
</a>
</td>
</tr>
</template>
</tbody>
</table>
</div>
<template x-if="applications.length === 0">
<div class="p-12 text-center text-slate-400 text-xs">
<svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
Tidak ada pengajuan yang menunggu persetujuan saat ini.
</div>
</template>
</div>
</div>
@endsection
