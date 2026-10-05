@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Dashboard Admin Operasional')
@section('page_title', 'Dashboard Admin Desa')
@section('content')
<script>
window.adminDashboard = function() {
    return {
        applications: [],
        residents: [],
        services: [],
        stats: { totalPenduduk: 0, totalKK: 0, ajuanBaru: 0, menunggu: 0, selesai: 0, ditolak: 0, perbaikan: 0, totalPengajuan: 0 },
        chartData: [],
        init() {
            if (window.siadesaStore) {
                Promise.all([
                    window.siadesaStore.fetchAllApplications(),
                    window.siadesaStore.fetchResidents(),
                    window.siadesaStore.fetchServices()
                ]).then(([apps, residents, services]) => {
                    this.applications = apps || [];
                    this.residents = residents || [];
                    this.services = services || [];

                    this.stats.totalPenduduk = this.residents.length;

                    const kkSet = new Set();
                    this.residents.forEach(r => { if (r.kk) kkSet.add(r.kk); });
                    this.stats.totalKK = kkSet.size;

                    this.stats.totalPengajuan = this.applications.length;
                    this.stats.ajuanBaru = this.applications.filter(a => a.status === 'DIAJUKAN').length;
                    this.stats.menunggu = this.applications.filter(a => a.status === 'MENUNGGU PERSETUJUAN').length;
                    this.stats.selesai = this.applications.filter(a => a.status === 'SELESAI').length;
                    this.stats.ditolak = this.applications.filter(a => a.status === 'DITOLAK').length;
                    this.stats.perbaikan = this.applications.filter(a => a.status === 'PERLU PERBAIKAN').length;

                    const serviceCount = {};
                    this.applications.forEach(a => {
                        const name = a.service_name || a.service_code || 'Lainnya';
                        serviceCount[name] = (serviceCount[name] || 0) + 1;
                    });
                    this.chartData = Object.entries(serviceCount)
                        .map(([name, count]) => ({ name, count }))
                        .sort((a, b) => b.count - a.count);

                    if (this.chartData.length === 0) {
                        this.services.forEach(s => {
                            this.chartData.push({ name: s.name || s.code, count: 0 });
                        });
                    }
                });
            }
        },
        get maxChart() {
            const m = Math.max(...this.chartData.map(d => d.count), 1);
            return m;
        }
    };
};
</script>
<div class="space-y-6" x-data="adminDashboard()">
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
    <p class="text-xs font-semibold text-slate-500">Total Penduduk Terdaftar</p>
    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" x-text="stats.totalPenduduk.toLocaleString()"></p>
    <span class="text-[11px] text-slate-400" x-text="stats.totalKK + ' KK tercatat'"></span>
</div>
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
    <p class="text-xs font-semibold text-sky-700">Pengajuan Baru</p>
    <p class="text-2xl sm:text-3xl font-extrabold text-sky-800 mt-1" x-text="stats.ajuanBaru"></p>
    <span class="text-[11px] text-sky-600 font-medium">Perlu diverifikasi admin</span>
</div>
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
    <p class="text-xs font-semibold text-amber-700">Menunggu Kepala Desa</p>
    <p class="text-2xl sm:text-3xl font-extrabold text-amber-800 mt-1" x-text="stats.menunggu"></p>
    <span class="text-[11px] text-amber-600 font-medium">Approval pimpinan</span>
</div>
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
    <p class="text-xs font-semibold text-emerald-700">Surat Terbit Selesai</p>
    <p class="text-2xl sm:text-3xl font-extrabold text-emerald-800 mt-1" x-text="stats.selesai"></p>
    <span class="text-[11px] text-emerald-600 font-medium" x-text="'Total pengajuan: ' + stats.totalPengajuan"></span>
</div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
<div class="flex justify-between items-center mb-6">
<div><h3 class="font-extrabold text-slate-900 text-base">Statistik Pelayanan per Jenis Surat</h3><p class="text-xs text-slate-500">Berdasarkan data pengajuan aktual dalam sistem</p></div>
</div>
<template x-if="chartData.length > 0">
<div class="space-y-4">
<template x-for="item in chartData" :key="item.name">
<div class="flex items-center gap-3">
<span class="text-xs font-semibold text-slate-600 w-40 truncate" x-text="item.name"></span>
<div class="flex-1 h-8 bg-slate-100 rounded-xl overflow-hidden">
<div class="h-full flex items-center justify-end pr-2 bg-gradient-to-r from-emerald-700 to-emerald-600 rounded-xl transition-all text-white text-[10px] font-bold" :style="'width: ' + (item.count > 0 ? Math.max(item.count / maxChart * 100, 8) : 0) + '%'" x-text="item.count > 0 ? item.count : ''"></div>
</div>
<span class="text-xs font-bold text-slate-900 w-8 text-right" x-text="item.count"></span>
</div>
</template>
</div>
</template>
<template x-if="chartData.length === 0">
<p class="text-xs text-slate-400 text-center py-8">Belum ada data pengajuan surat.</p>
</template>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
<h3 class="font-extrabold text-slate-900 text-base mb-4">Pengajuan Terbaru</h3>
<div class="space-y-3">
<template x-for="item in applications.slice(0,5)" :key="item.id">
<div class="p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 transition-all flex justify-between gap-2">
<div class="min-w-0">
<p class="font-mono text-[11px] font-bold text-emerald-800 truncate" x-text="item.tracking_number"></p>
<p class="font-bold text-xs text-slate-900 truncate" x-text="item.service_name"></p>
<p class="text-[11px] text-slate-500 truncate" x-text="item.applicant_name"></p>
</div>
<span class="text-[10px] font-bold px-2 py-0.5 rounded-full h-fit flex-shrink-0 border"
      :class="{
          'bg-sky-50 text-sky-700 border-sky-200': item.status === 'DIAJUKAN',
          'bg-amber-50 text-amber-700 border-amber-200': item.status === 'MENUNGGU PERSETUJUAN' || item.status === 'PERLU PERBAIKAN',
          'bg-emerald-600 text-white border-emerald-600': item.status === 'SELESAI',
          'bg-indigo-50 text-indigo-700 border-indigo-200': item.status === 'DIPROSES',
          'bg-rose-50 text-rose-700 border-rose-200': item.status === 'DITOLAK'
      }" x-text="item.status"></span>
</div>
</template>
<template x-if="applications.length === 0">
<p class="text-xs text-slate-400 text-center py-4">Belum ada pengajuan surat masuk.</p>
</template>
</div>
</div>
</div>
</div>
@endsection
