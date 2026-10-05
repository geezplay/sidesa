@extends('layouts.public')

@section('title', 'Detail Layanan')

@section('content')
<div class="py-10 bg-slate-50 min-h-screen" x-data="{
    service: null,
    serviceId: '{{ $id }}',
    init() {
        if (window.siadesaStore) {
            const list = window.siadesaStore.getServices();
            this.service = list.find(s => s.id === this.serviceId) || list[0];
        }
    }
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <a href="{{ route('services') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar layanan
        </a>

        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm">
            <template x-if="service">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="service.category"></span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Kode: <span x-text="service.code"></span></span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Estimasi: <span x-text="service.estimation"></span>
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-3" x-text="service.name"></h1>
                    <p class="text-sm text-slate-500 leading-relaxed mb-8" x-text="service.description"></p>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <h3 class="font-bold text-slate-900 text-sm mb-3">Persyaratan Wajib Upload</h3>
                            <ul class="space-y-2">
                                <template x-for="(req, idx) in service.requirements" :key="idx">
                                    <li class="flex items-start gap-2 text-xs text-slate-600">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold flex-shrink-0" x-text="idx + 1"></span>
                                        <span class="font-medium" x-text="req"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <h3 class="font-bold text-slate-900 text-sm mb-3">Informasi Persetujuan</h3>
                            <div class="space-y-3 text-xs text-slate-600">
                                <div class="flex justify-between bg-white p-3 rounded-xl border">
                                    <span>Membutuhkan Approval?</span>
                                    <span class="font-bold" :class="service.requires_approval ? 'text-emerald-700' : 'text-blue-700'" x-text="service.requires_approval ? 'Ya, Kepala Desa' : 'Tidak, Langsung oleh Admin'"></span>
                                </div>
                                <div class="flex justify-between bg-white p-3 rounded-xl border">
                                    <span>Biaya Layanan</span>
                                    <span class="font-bold text-emerald-700">GRATIS (Rp 0)</span>
                                </div>
                                <div class="flex justify-between bg-white p-3 rounded-xl border">
                                    <span>Output Dokumen</span>
                                    <span class="font-bold text-slate-800">PDF + Kop & QR Valid</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 bg-emerald-50 -mx-6 -mb-6 sm:-mx-10 sm:-mb-10 px-6 sm:px-10 py-6 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-emerald-900">
                            <p class="font-bold">Belum masuk akun?</p>
                            <p>Anda akan diarahkan melakukan pendaftaran (Registrasi NIK) sebelum mengajukan surat.</p>
                        </div>
                        <a :href="'/warga/pengajuan/baru?layanan=' + service.id" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-md text-center transition-all">
                            Ajukan Sekarang
                        </a>
                    </div>
                </div>
            </template>
        </div>

    </div>
</div>
@endsection
