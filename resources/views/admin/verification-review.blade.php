@extends('layouts.app', ['role' => 'admin'])

@section('title', 'Pemeriksaan Dokumen Pelayanan')
@section('page_title', 'Periksa Berkas & Persyaratan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    appId: '{{ $id }}',
    app: null,
    notes: '',
    actionMsg: '',
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchApplicationById(this.appId).then(a => {
                this.app = a;
            });
        }
    },
    act(newStatus) {
        if (newStatus === 'PERLU PERBAIKAN' || newStatus === 'DITOLAK') {
            if (!this.notes.trim()) {
                this.actionMsg = 'Catatan / alasan wajib diisi saat meminta perbaikan atau menolak.';
                return;
            }
        }
        if (window.siadesaStore) {
            window.siadesaStore.updateApplicationStatus(this.appId, newStatus, this.notes).then(updated => {
                if (updated && updated.id) {
                    this.app = updated;
                    this.actionMsg = newStatus === 'MENUNGGU PERSETUJUAN'
                        ? 'Berkas dinyatakan valid dan langsung diteruskan ke Kepala Desa untuk persetujuan.'
                        : (newStatus === 'PERLU PERBAIKAN'
                            ? 'Instruksi perbaikan telah dikirim ke pemohon.'
                            : 'Pengajuan telah ditolak dengan alasan resmi.');
                } else {
                    this.actionMsg = (updated && updated.message) ? updated.message : 'Gagal memproses verifikasi.';
                }
            });
        }
    }
}">

    <a href="{{ route('admin.verifications') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke antrean verifikasi
    </a>

    <template x-if="app">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-5 border-b border-slate-100">
                <div>
                    <span class="font-mono font-bold text-sm text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200" x-text="app.tracking_number"></span>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-2" x-text="app.service_name"></h2>
                    <p class="text-xs text-slate-500 mt-0.5">Keperluan: <span class="font-semibold text-slate-700" x-text="app.purpose"></span></p>
                </div>
                <div class="text-sm">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border bg-slate-100 text-slate-700 border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-current"></span>
                        <span x-text="app.status"></span>
                    </span>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 text-xs">
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <p class="font-bold text-slate-800 text-sm mb-2.5">Biodata Pemohon</p>
                    <p x-text="app.applicant_name" class="font-bold text-slate-900 text-sm"></p>
                    <p class="font-mono text-slate-500 mt-0.5" x-text="'NIK: ' + app.applicant_nik"></p>
                    <p class="mt-1 text-slate-600" x-text="'Alamat: ' + app.applicant_address"></p>
                    <p class="mt-1 text-slate-600" x-text="'WhatsApp: ' + app.applicant_phone"></p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <p class="font-bold text-slate-800 text-sm mb-2.5">Lampiran Berkas Persyaratan</p>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-slate-200">
                            <span>Scan e-KTP Pemohon</span>
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded text-[10px] border border-emerald-200">Terlampir</span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-slate-200">
                            <span>Kartu Keluarga (KK)</span>
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded text-[10px] border border-emerald-200">Terlampir</span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-slate-200">
                            <span>Surat Pengantar RT/RW</span>
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded text-[10px] border border-emerald-200">Terlampir</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Catatan Verifikasi / Alasan (Wajib jika Perlu Perbaikan atau Tolak)</label>
                <textarea x-model="notes" rows="3" placeholder="Contoh: Berkas telah diperiksa sesuai data kependudukan..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <template x-if="actionMsg">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold" x-text="actionMsg"></div>
            </template>

            <div class="flex flex-col sm:flex-row gap-2.5 pt-2 border-t border-slate-100">
                <button @click="act('MENUNGGU PERSETUJUAN')" class="flex-1 px-4 py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-md transition-all">
                    ✓ Berkas Sesuai, Teruskan ke Kepala Desa
                </button>
                <button @click="act('PERLU PERBAIKAN')" class="flex-1 px-4 py-3.5 bg-white hover:bg-amber-50 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs transition-all">
                    ⚠ Minta Perbaikan Berkas
                </button>
                <button @click="act('DITOLAK')" class="flex-1 px-4 py-3.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 font-bold rounded-xl text-xs transition-all">
                    ✕ Tolak Pengajuan
                </button>
            </div>
        </div>
    </template>

</div>
@endsection
