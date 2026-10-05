@extends('layouts.app', ['role' => 'warga'])

@section('title', 'Dashboard Masyarakat')
@section('page_title', 'Portal Pelayanan Warga')

@section('content')
<div class="space-y-6" x-data="{
    user: null,
    isProfileComplete: true,
    applications: [],
    recentApps: [],
    stats: { total: 0, diproses: 0, selesai: 0, perbaikan: 0 },
    init() {
        if (window.siadesaStore) {
            this.user = window.siadesaStore.getCurrentUser();
            this.isProfileComplete = this.user ? !!this.user.is_profile_complete : false;

            window.siadesaStore.fetchMyApplications().then(all => {
                this.applications = all || [];
                this.stats.total = this.applications.length;
                this.stats.diproses = this.applications.filter(a => ['DIAJUKAN', 'DIVERIFIKASI', 'DIPROSES', 'MENUNGGU PERSETUJUAN'].includes(a.status)).length;
                this.stats.selesai = this.applications.filter(a => a.status === 'SELESAI').length;
                this.stats.perbaikan = this.applications.filter(a => a.status === 'PERLU PERBAIKAN').length;
            });
        }
    }
}">

    <!-- WARNING BANNER JIKA PROFIL BELUM LENGKAP (PRD BR-02) -->
    <template x-if="!isProfileComplete">
        <div class="bg-amber-50 border-2 border-amber-400 rounded-3xl p-6 text-amber-900 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-amber-950">Biodata Kependudukan Belum Lengkap (Aturan BR-02)</h3>
                    <p class="text-xs text-amber-800 mt-0.5 max-w-2xl leading-relaxed">
                        Anda belum dapat membuat permohonan surat baru karena Nomor Kartu Keluarga (KK), Tempat/Tanggal Lahir, atau Alamat RT/RW belum tercatat dalam sistem. Silakan lengkapi formulir sekarang.
                    </p>
                </div>
            </div>
            <a href="{{ route('resident.profile') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs whitespace-nowrap transition-colors">
                Lengkapi Biodata Sekarang →
            </a>
        </div>
    </template>

    <!-- Welcome Banner Warga -->
    <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-md">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold border" 
                      :class="isProfileComplete ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-amber-400/20 text-amber-300 border-amber-400/30'">
                    <span x-text="isProfileComplete ? 'Profil Warga: Lengkap & Terverifikasi' : 'Profil Warga: Menunggu Kelengkapan Biodata'"></span>
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Halo, <span x-text="user ? user.name : 'Warga Desa'"></span>!</h1>
            <p class="text-emerald-100/80 text-xs sm:text-sm max-w-xl leading-relaxed">
                NIK: <span class="font-mono font-semibold" x-text="user ? user.nik : '-'"></span> 
                <template x-if="user && user.address">
                    <span>| Alamat: <span x-text="user.address"></span></span>
                </template>
                <br>
                Ajukan surat administrasi baru atau pantau alur verifikasi berkas Anda secara transparan.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <!-- Jika profil lengkap bisa ajukan -->
            <template x-if="isProfileComplete">
                <a href="{{ route('resident.create') }}" class="px-5 py-3 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-emerald-950 font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Ajukan Surat Baru</span>
                </a>
            </template>
            <template x-if="!isProfileComplete">
                <a href="{{ route('resident.profile') }}" class="px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-amber-950 font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center gap-2" title="Lengkapi profil dahulu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Lengkapi Biodata Dulu</span>
                </a>
            </template>
            
            <a href="{{ route('resident.profile') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition-all">
                Kelola Profil
            </a>
        </div>
    </div>

    <!-- METRIC CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-slate-500">Total Diajukan</p>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" x-text="stats.total"></p>
            <span class="text-[11px] text-slate-400">Semua permohonan</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-sky-700">Sedang Berjalan</p>
            <p class="text-2xl sm:text-3xl font-extrabold text-sky-800 mt-1" x-text="stats.diproses"></p>
            <span class="text-[11px] text-sky-600 font-medium">Dalam verifikasi loket</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-amber-700">Perlu Perbaikan</p>
            <p class="text-2xl sm:text-3xl font-extrabold text-amber-800 mt-1" x-text="stats.perbaikan"></p>
            <span class="text-[11px] text-amber-600 font-medium">Menunggu upload ulang</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs font-semibold text-emerald-700">Surat Selesai</p>
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-800 mt-1" x-text="stats.selesai"></p>
            <span class="text-[11px] text-emerald-600 font-medium">Siap cetak / unduh</span>
        </div>
    </div>

    <!-- TABLE PENGAJUAN TERBARU WARGA -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Permohonan Surat Saya</h3>
                <p class="text-xs text-slate-500">Daftar surat yang diajukan oleh akun Anda</p>
            </div>
            <a href="{{ route('resident.history') }}" class="text-xs font-bold text-emerald-700 hover:underline">Lihat Semua Riwayat →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3.5 px-6">No. Pengajuan</th>
                        <th class="py-3.5 px-6">Jenis Surat</th>
                        <th class="py-3.5 px-6">Tanggal Kirim</th>
                        <th class="py-3.5 px-6">Status Terkini</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in applications.slice(0, 5)" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900" x-text="item.tracking_number"></td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-800" x-text="item.service_name"></p>
                                <p class="text-[11px] text-slate-400 truncate max-w-xs" x-text="item.purpose"></p>
                            </td>
                            <td class="py-4 px-6 text-slate-600" x-text="item.created_at"></td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
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
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a :href="'/tracking?kode=' + item.tracking_number" class="text-xs font-bold text-emerald-700 hover:underline">
                                    Lacak Timeline
                                </a>
                                <template x-if="item.status === 'SELESAI' || item.status === 'DIPROSES'">
                                    <a :href="'/admin/surat/cetak/' + item.id" target="_blank" class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold hover:bg-emerald-200">
                                        Cetak
                                    </a>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <template x-if="applications.length === 0">
            <div class="p-8 text-center text-xs text-slate-500">
                <p>Belum ada permohonan surat yang diajukan oleh akun Anda.</p>
                <template x-if="isProfileComplete">
                    <a href="{{ route('resident.create') }}" class="mt-2 inline-block font-bold text-emerald-700 hover:underline">Mulai Buat Pengajuan Baru (Tahap 3)</a>
                </template>
            </div>
        </template>
    </div>

</div>
@endsection
