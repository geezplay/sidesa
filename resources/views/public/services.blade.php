@extends('layouts.public')

@section('title', 'Katalog Layanan Administrasi')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen" x-data="{
    search: '',
    selectedCategory: 'all',
    services: [],
    init() {
        if (window.siadesaStore) {
            this.services = window.siadesaStore.getServices();
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-xs mb-8">
            <div class="max-w-3xl">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Pelayanan Terpadu</span>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-1">Daftar Layanan Administrasi Desa</h1>
                <p class="text-slate-500 text-sm mt-2 leading-relaxed">
                    Pilih jenis surat yang Anda butuhkan. Setiap permohonan memiliki persyaratan dokumen spesifik dan estimasi waktu penyelesaian standar pelayanan minimal desa.
                </p>
            </div>

            <!-- Search & Filter Controls -->
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari jenis surat (cth: Usaha, Domisili, SKTM)..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <button @click="selectedCategory = 'all'" 
                            :class="selectedCategory === 'all' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                        Semua Kategori
                    </button>
                    <button @click="selectedCategory = 'Surat Keterangan'" 
                            :class="selectedCategory === 'Surat Keterangan' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                        Surat Keterangan
                    </button>
                    <button @click="selectedCategory = 'Kependudukan'" 
                            :class="selectedCategory === 'Kependudukan' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                        Kependudukan
                    </button>
                    <button @click="selectedCategory = 'Bantuan Sosial'" 
                            :class="selectedCategory === 'Bantuan Sosial' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                        Bansos
                    </button>
                </div>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="item in filteredServices" :key="item.id">
                <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-emerald-400 hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200" x-text="item.category"></span>
                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="item.estimation"></span>
                            </span>
                        </div>

                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-xs font-extrabold text-emerald-700 bg-emerald-100/60 px-1.5 py-0.5 rounded" x-text="item.code"></span>
                            <h3 class="font-bold text-slate-900 text-base" x-text="item.name"></h3>
                        </div>

                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mb-4" x-text="item.description"></p>

                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-4">
                            <p class="text-[11px] font-bold text-slate-700 mb-1.5">Persyaratan Berkas:</p>
                            <ul class="text-[11px] text-slate-600 space-y-1">
                                <template x-for="(req, idx) in item.requirements" :key="idx">
                                    <li class="flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span x-text="req"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <a :href="'/layanan/' + item.id" class="text-xs font-semibold text-slate-600 hover:text-emerald-700">Detail Alur</a>
                        <a :href="'/warga/pengajuan/baru?layanan=' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all">
                            Ajukan Surat Ini
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty Search -->
        <div x-show="filteredServices.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 my-8">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-bold text-slate-800 text-base">Layanan Tidak Ditemukan</p>
            <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci lain atau pilih "Semua Kategori".</p>
        </div>

    </div>
</div>
@endsection
