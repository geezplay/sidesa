<div x-data="{
    open: false,
    currentRole: 'warga',
    roles: [
        { key: 'warga', name: 'Masyarakat', desc: 'Budi Santoso (Pemohon)', url: '{{ route('resident.dashboard') }}', color: 'emerald' },
        { key: 'verifikator', name: 'Verifikator', desc: 'Hendra Wijaya (Pemeriksa Berkas)', url: '{{ route('verifikator.dashboard') }}', color: 'blue' },
        { key: 'kades', name: 'Kepala Desa', desc: 'Drs. H. Mulyono (Approval Surat)', url: '{{ route('kades.dashboard') }}', color: 'purple' },
        { key: 'admin', name: 'Admin Desa', desc: 'Rian Pratama (Kelola Penduduk & Konfigurasi)', url: '{{ route('admin.dashboard') }}', color: 'slate' }
    ],
    init() {
        if (window.siadesaStore) {
            this.currentRole = window.siadesaStore.getCurrentRole();
        }
    },
    switchRole(r) {
        if (window.siadesaStore) {
            window.siadesaStore.setCurrentRole(r.key);
        }
        window.location.href = r.url;
    },
    resetData() {
        if (confirm('Reset ulang data demo (pengajuan, warga, layanan) ke nilai awal?')) {
            if (window.siadesaStore) {
                window.siadesaStore.resetToDefault();
                window.location.reload();
            }
        }
    }
}" class="fixed bottom-4 right-4 z-50 no-print">

    <!-- Toggle Button -->
    <button @click="open = !open" 
            class="flex items-center gap-2 bg-emerald-900 text-white px-4 py-2.5 rounded-full shadow-xl hover:bg-emerald-800 border-2 border-emerald-500/30 transition-all font-medium text-xs sm:text-sm">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="font-semibold">Demo Role Switcher</span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <!-- Modal Dropdown -->
    <div x-show="open" 
         @click.outside="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 mb-2">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Simulasi Multi-Role</p>
                <p class="text-[11px] text-slate-500">Pilih peran untuk menguji alur kerja PRD</p>
            </div>
            <button @click="resetData()" title="Reset Dataset Demo" class="text-[11px] text-rose-600 hover:text-rose-700 font-semibold underline">Reset Data</button>
        </div>

        <div class="space-y-1.5 mt-3">
            <template x-for="r in roles" :key="r.key">
                <button @click="switchRole(r)"
                        class="w-full text-left p-2.5 rounded-xl border transition-all flex items-center justify-between group"
                        :class="currentRole === r.key ? 'bg-emerald-50 border-emerald-500 text-emerald-950 font-bold' : 'border-slate-100 hover:border-slate-300 hover:bg-slate-50 text-slate-700'">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" x-text="r.name"></span>
                            <span x-show="currentRole === r.key" class="text-[10px] bg-emerald-700 text-white px-1.5 py-0.5 rounded-md font-bold">Aktif</span>
                        </div>
                        <p class="text-[11px] text-slate-500" x-text="r.desc"></p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </template>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <a href="{{ route('home') }}" class="text-emerald-700 hover:underline font-semibold">Ke Halaman Depan</a>
            <span>SIADESA v1.1</span>
        </div>
    </div>
</div>
