@extends('layouts.public')

@section('title', 'Beranda - Layanan Mandiri Desa')

@section('content')
<div x-data="{
    trackingInput: '',
    submitTracking() {
        if (!this.trackingInput.trim()) return;
        window.location.href = '{{ route('tracking') }}?kode=' + encodeURIComponent(this.trackingInput.trim());
    }
}">

    <!-- HERO SECTION -->
    <section class="relative bg-gradient-to-b from-emerald-900 via-emerald-800 to-emerald-900 text-white overflow-hidden py-16 sm:py-24">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-700/60 border border-emerald-500/40 text-xs font-semibold text-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Pelayanan Publik Cepat, Mudah, dan Transparan
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        Administrasi Desa Sukamaju <br class="hidden sm:inline">
                        <span class="text-emerald-300">Kini Serba Digital</span>
                    </h1>

                    <p class="text-base sm:text-lg text-emerald-100/90 max-w-2xl leading-relaxed">
                        Ajukan surat keterangan usaha, domisili, SKTM, pengantar SKCK, dan permohonan lainnya secara online. Pantau status pengajuan langsung dari rumah tanpa perlu bolak-balik ke kantor desa.
                    </p>

                    <!-- QUICK TRACKING BOX -->
                    <div class="bg-white/10 backdrop-blur-md p-2.5 sm:p-3 rounded-2xl border border-white/20 max-w-xl">
                        <form @submit.prevent="submitTracking()" class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" 
                                       x-model="trackingInput" 
                                       placeholder="Ketik Nomor Pengajuan (cth: ADM-20261005-000124)" 
                                       class="w-full pl-10 pr-4 py-3 bg-white text-slate-800 placeholder-slate-400 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            </div>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-md flex items-center justify-center gap-2">
                                <span>Lacak Pengajuan</span>
                            </button>
                        </form>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 pt-2 text-xs text-emerald-200">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Surat Resmi Terbit QR Code</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Gratis Tanpa Pungutan Liar</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Sesuai UU PDP No. 27/2022</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARD: STAT QUICK RECAP -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 text-slate-800 shadow-2xl border border-emerald-100">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Statistik Pelayanan Hari Ini</h3>
                                <p class="text-xs text-slate-500">Pembaruan otomatis dari sistem desa</p>
                            </div>
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">Real-time</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 my-6">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-xs font-medium text-slate-500">Total Penduduk</p>
                                <p class="text-2xl font-extrabold text-slate-900 mt-1">5.248</p>
                                <span class="text-[11px] text-emerald-600 font-semibold">1.412 KK terdaftar</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                                <p class="text-xs font-medium text-emerald-800">Surat Selesai</p>
                                <p class="text-2xl font-extrabold text-emerald-900 mt-1">152</p>
                                <span class="text-[11px] text-emerald-700 font-semibold">Bulan Oktober 2026</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100">
                                <p class="text-xs font-medium text-sky-800">Sedang Diproses</p>
                                <p class="text-2xl font-extrabold text-sky-900 mt-1">17</p>
                                <span class="text-[11px] text-sky-700 font-semibold">Verifikasi loket</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                                <p class="text-xs font-medium text-amber-800">Rata-rata Waktu</p>
                                <p class="text-2xl font-extrabold text-amber-900 mt-1">1 Hari</p>
                                <span class="text-[11px] text-amber-700 font-semibold">Penyelesaian cepat</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <a href="{{ route('resident.create') }}" class="w-full block text-center bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3 px-4 rounded-xl text-sm transition-all shadow-sm">
                                Ajukan Surat Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: ALUR PELAYANAN 4 LANGKAH -->
    <section class="py-16 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Mudah & Terstruktur</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">4 Langkah Mudah Pengajuan Surat</h2>
                <p class="text-sm text-slate-500 mt-2">Seluruh tahapan permohonan dapat dipantau riwayatnya secara transparan melalui portal.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-500 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-lg mb-4 group-hover:bg-emerald-700 group-hover:text-white transition-colors">
                        1
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1.5">Pilih Jenis Layanan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Pilih jenis surat keterangan yang dibutuhkan serta baca persyaratan berkas yang diminta.</p>
                </div>

                <!-- Step 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-500 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-lg mb-4 group-hover:bg-emerald-700 group-hover:text-white transition-colors">
                        2
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1.5">Isi Form & Unggah Berkas</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Lengkapi keperluan permohonan dan unggah foto/scan KTP, KK, atau surat pengantar RT/RW.</p>
                </div>

                <!-- Step 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-500 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-lg mb-4 group-hover:bg-emerald-700 group-hover:text-white transition-colors">
                        3
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1.5">Verifikasi & Approval</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Petugas loket memvalidasi dokumen dan meneruskan ke Kepala Desa untuk persetujuan resmi.</p>
                </div>

                <!-- Step 4 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-500 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-lg mb-4 group-hover:bg-emerald-700 group-hover:text-white transition-colors">
                        4
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1.5">Terbit Surat & QR Code</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Unduh surat digital siap cetak lengkap tanda tangan resmi dan QR Code validasi keaslian.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: KATALOG LAYANAN POPULER -->
    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{
            services: [],
            init() {
                if (window.siadesaStore) {
                    this.services = window.siadesaStore.getServices();
                }
            }
        }">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Katalog Online</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Layanan Administrasi Populer</h2>
                </div>
                <a href="{{ route('services') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 group">
                    <span>Lihat Semua Layanan</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <template x-for="item in services.slice(0, 6)" :key="item.id">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:shadow-lg hover:border-emerald-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="item.category"></span>
                                <span class="text-xs text-slate-400 font-medium" x-text="item.estimation"></span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2 hover:text-emerald-700 transition-colors" x-text="item.name"></h3>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4" x-text="item.description"></p>
                            
                            <div class="text-[11px] text-slate-500 space-y-1 mb-4">
                                <p class="font-semibold text-slate-700">Persyaratan Wajib:</p>
                                <template x-for="(req, idx) in item.requirements.slice(0, 3)" :key="idx">
                                    <div class="flex items-center gap-1.5 text-slate-600">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span class="truncate" x-text="req"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <a :href="'/layanan/' + item.id" class="text-xs font-semibold text-slate-600 hover:text-emerald-700">Detail Syarat</a>
                            <a :href="'/warga/pengajuan/baru?layanan=' + item.id" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs">Ajukan</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- SECTION: BANNER TRACKING & QR VALIDATION PROMO -->
    <section class="py-12 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-8 sm:p-12 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="space-y-3 max-w-xl">
                    <span class="px-3 py-1 rounded-full bg-emerald-200/60 text-emerald-800 text-xs font-bold">Fitur Keaslian Dokumen</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-950">Validasi Surat Resmi dengan Pemindaian QR Code</h3>
                    <p class="text-xs sm:text-sm text-emerald-900/80 leading-relaxed">
                        Pihak perbankan, kepolisian, atau instansi terkait dapat langsung memverifikasi keabsahan surat yang dikeluarkan Desa Sukamaju secara instan tanpa khawatir adanya surat palsu.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('tracking', ['kode' => 'ADM-20261005-000124']) }}" class="px-6 py-3 rounded-xl bg-white text-emerald-800 font-bold text-xs sm:text-sm border border-emerald-200 hover:bg-emerald-100 transition-all text-center shadow-xs">
                        Lihat Contoh Tracking
                    </a>
                    <a href="{{ route('verify.doc', ['code' => 'SIADESA-DOC-20261005-8F3A2B9C']) }}" class="px-6 py-3 rounded-xl bg-emerald-700 text-white font-bold text-xs sm:text-sm hover:bg-emerald-800 transition-all text-center shadow-md">
                        Simulasi Scan QR
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
