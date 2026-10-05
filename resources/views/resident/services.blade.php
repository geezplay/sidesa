@extends('layouts.app', ['role' => 'warga'])

@section('title', 'Katalog Layanan Administrasi')
@section('page_title', 'Katalog Layanan Surat Desa')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    selectedCategory: 'all',
    services: [],
    user: null,
    isProfileComplete: true,
    init() {
        if (window.siadesaStore) {
            this.services = window.siadesaStore.getServices();
            this.user = window.siadesaStore.getCurrentUser();
            this.isProfileComplete = this.user ? !!this.user.is_profile_complete : false;
        }
    },
    get filteredServices() {
        return this.services.filter(s => {
            const matchSearch = s.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                s.description.toLowerCase().includes(this.search.toLowerCase()) ||
                                s.code.toLowerCase().includes(this.search.toLowerCase());
            const matchCat = this.selectedCategory === 'all' || s.category === this.selectedCategory;
            return matchSearch && matchCat;
        });
    }
}">

    <!-- Alert Edukasi Jika Profil Belum Lengkap -->
    <template x-if="!isProfileComplete">
        <div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-5 text-amber-900 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="font-extrabold text-xs sm:text-sm">Lengkapi Profil Terlebih Dahulu (Aturan BR-02)</p>
                    <p class="text-[11px] text-amber-800">Tombol ajukan akan terbuka otomatis setelah biodata Anda lengkap.</p>
                </div>
            </div>
            <a href="{{ route('resident.profile') }}" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold hover:bg-amber-700 whitespace-nowrap">
                Lengkapi Biodata →
            </a>
        </div>
    </template>

    <!-- Header Filter Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Daftar Surat Administrasi Aktif</h3>
                <p class="text-xs text-slate-500">Pilih jenis layanan surat keterangan yang Anda butuhkan untuk proses pelayanan.</p>
            </div>
            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200" x-text="filteredServices.length + ' Layanan Tersedia'"></span>
        </div>

        <!-- Controls Filter & Search -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari jenis surat (contoh: Usaha, Domisili, SKTM)..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex gap-2 overflow-x-auto pb-1 sm:pb-0">
                <button @click="selectedCategory = 'all'" 
                        :class="selectedCategory === 'all' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                    Semua
                </button>
                <button @click="selectedCategory = 'Surat Keterangan'" 
                        :class="selectedCategory === 'Surat Keterangan' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                    Keterangan
                </button>
                <button @click="selectedCategory = 'Kependudukan'" 
                        :class="selectedCategory === 'Kependudukan' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                    Kependudukan
                </button>
                <button @click="selectedCategory = 'Bantuan Sosial'" 
                        :class="selectedCategory === 'Bantuan Sosial' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                    Bansos
                </button>
            </div>
        </div>
    </div>

    <!-- Grid Layanan Berformat Dashboard -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="item in filteredServices" :key="item.id">
            <div class="bg-white rounded-3xl p-6 border border-slate-200 hover:border-emerald-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200" x-text="item.category"></span>
                        <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="item.estimation"></span>
                        </span>
                    </div>

                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-xs font-mono font-extrabold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded" x-text="item.code"></span>
                        <h4 class="font-extrabold text-slate-900 text-base" x-text="item.name"></h4>
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4" x-text="item.description"></p>

                    <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100 mb-4">
                        <p class="text-[11px] font-bold text-slate-700 mb-1.5">Persyaratan Dokumen:</p>
                        <ul class="text-[11px] text-slate-600 space-y-1">
                            <template x-for="(req, idx) in item.requirements" :key="idx">
                                <li class="flex items-start gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span class="truncate" x-text="req"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-500">
                        <span x-text="item.requires_approval ? 'Approval Kades' : 'Approval Loket'"></span>
                    </span>

                    <template x-if="isProfileComplete">
                        <a :href="'/warga/pengajuan/baru?layanan=' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all">
                            Ajukan Surat →
                        </a>
                    </template>
                    <template x-if="!isProfileComplete">
                        <a href="{{ route('resident.profile') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-xs transition-all" title="Lengkapi profil terlebih dahulu">
                            Lengkapi Profil →
                        </a>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty Search -->
    <div x-show="filteredServices.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200">
        <p class="font-bold text-slate-800 text-sm">Layanan Tidak Ditemukan</p>
        <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
    </div>

</div>
@endsection
