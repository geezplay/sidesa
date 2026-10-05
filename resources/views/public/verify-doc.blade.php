@extends('layouts.public')

@section('title', 'Verifikasi Keaslian Surat Resmi')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen" x-data="{
    code: '{{ $code }}',
    matchedApp: null,
    valid: false,
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.verifyDocument(this.code).then(res => {
                if (res && res.success && res.valid && res.document) {
                    const d = res.document;
                    this.matchedApp = {
                        id: d.id,
                        letter_number: d.letter_number,
                        service_name: d.service_name,
                        applicant_name: d.applicant_name_censored,
                        updated_at: d.issued_at,
                        qr_uuid: d.qr_uuid,
                        signer: d.signer_head,
                        village: d.village_name
                    };
                    this.valid = true;
                } else {
                    this.valid = false;
                }
            });
        }
    }
}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-xl mx-auto mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Pusat Validasi Surat Resmi Digital
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Hasil Pemindaian QR Code</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Sesuai Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27/2022)</p>
        </div>

        <template x-if="valid">
            <div class="bg-white rounded-3xl p-6 sm:p-10 border-2 border-emerald-500 shadow-xl space-y-6">
                
                <!-- Status Badge Banner -->
                <div class="bg-emerald-50 rounded-2xl p-4 sm:p-6 border border-emerald-200 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Verifikasi Berhasil</span>
                        <h2 class="text-xl font-extrabold text-emerald-950">DOKUMEN ASLI & TERVERIFIKASI</h2>
                        <p class="text-xs text-emerald-800/80">Diterbitkan secara sah oleh Pemerintah Desa Sukamaju, Kab. Bogor</p>
                    </div>
                </div>

                <!-- Document Details (Sensored for privacy) -->
                <div class="space-y-3 text-xs sm:text-sm">
                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Nomor Surat Resmi</span>
                        <span class="font-extrabold text-slate-900 font-mono" x-text="matchedApp.letter_number"></span>
                    </div>

                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Jenis Dokumen</span>
                        <span class="font-bold text-emerald-800" x-text="matchedApp.service_name"></span>
                    </div>

                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Nama Pemohon (Tersensor)</span>
                        <span class="font-bold text-slate-800">
                            <!-- Inisial Privacy Sensor -->
                            <span x-text="matchedApp.applicant_name.split(' ').map(w => w[0] + '***').join(' ')"></span>
                        </span>
                    </div>

                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Tanggal Diterbitkan</span>
                        <span class="font-semibold text-slate-800" x-text="matchedApp.updated_at"></span>
                    </div>

                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Pejabat Pengesah</span>
                        <span class="font-bold text-slate-800" x-text="(matchedApp.signer || '-') + ' (' + (matchedApp.village || 'Desa') + ')'"></span>
                    </div>

                    <div class="flex justify-between py-2.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Kode Validasi Unik</span>
                        <span class="font-mono text-xs text-slate-500" x-text="matchedApp.qr_uuid"></span>
                    </div>
                </div>

                <!-- Footer Privacy Notice -->
                <div class="bg-slate-50 rounded-2xl p-4 text-[11px] text-slate-500 leading-relaxed border border-slate-200">
                    <p class="font-semibold text-slate-700 mb-0.5">Catatan Keamanan & Privasi:</p>
                    Informasi nomor induk kependudukan (NIK) dan alamat pemohon disamarkan demi melindungi privasi warga. Surat resmi fisik asli wajib memiliki tanda tangan dan stempel basah atau cap barcode resmi pemerintah desa.
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-3 pt-2">
                    <a :href="'/admin/surat/cetak/' + matchedApp.id" target="_blank" class="px-6 py-3 bg-emerald-700 text-white font-bold rounded-xl text-xs hover:bg-emerald-800 transition-colors text-center shadow-xs">
                        Lihat Format Cetak Surat
                    </a>
                    <a href="{{ route('home') }}" class="px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-200 transition-colors text-center">
                        Kembali ke Beranda
                    </a>
                </div>

            </div>
        </template>

        <template x-if="!valid">
            <div class="bg-white rounded-3xl p-8 sm:p-12 border-2 border-rose-300 shadow-xl text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h2 class="text-2xl font-extrabold text-rose-950">DOKUMEN TIDAK TERDAFTAR</h2>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    Kode validasi <span class="font-mono font-bold text-slate-800" x-text="code"></span> tidak ditemukan dalam basis data arsip resmi Desa Sukamaju. Waspada terhadap potensi pemalsuan dokumen.
                </p>
                <div class="pt-4">
                    <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition-colors">
                        Kembali ke Halaman Depan
                    </a>
                </div>
            </div>
        </template>

    </div>
</div>
@endsection
