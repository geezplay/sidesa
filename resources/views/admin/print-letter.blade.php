<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Surat Resmi - SIADESA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background: white !important; font-size: 12pt; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none !important; margin: 0 !important; border: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans py-8" x-data="{
    appId: '{{ $id }}',
    app: null,
    init() {
        if (window.siadesaStore) {
            window.siadesaStore.fetchApplicationById(this.appId).then(a => {
                this.app = a;
            });
        }
    },
    print() {
        window.print();
    }
}">

    <!-- Top Action Bar (No Print) -->
    <div class="max-w-[210mm] mx-auto mb-6 flex justify-between items-center no-print px-4">
        <a href="javascript:history.back()" class="text-xs font-bold text-slate-600 hover:text-emerald-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <button @click="print()" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Surat / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official A4 Paper Sheet -->
    <div class="sheet max-w-[210mm] min-h-[297mm] mx-auto bg-white p-12 sm:p-16 rounded-2xl shadow-xl border border-slate-200 text-slate-900 leading-relaxed text-sm">
        
        <template x-if="app">
            <div>
                <!-- KOP SURAT PEMERINTAH DESA -->
                <div class="border-b-4 border-double border-slate-900 pb-4 mb-8 text-center relative">
                    <div class="w-16 h-16 absolute left-0 top-0 flex items-center justify-center font-extrabold text-2xl border-2 border-slate-900 rounded-xl">
                        S
                    </div>
                    <div class="px-20">
                        <p class="font-bold text-base uppercase tracking-widest text-slate-800">Pemerintah Kabupaten Bogor</p>
                        <p class="font-bold text-base uppercase tracking-wider text-slate-800">Kecamatan Jonggol</p>
                        <h1 class="font-extrabold text-2xl uppercase tracking-tight text-slate-950">Kantor Kepala Desa Sukamaju</h1>
                        <p class="text-xs text-slate-600 mt-0.5">Jl. Raya Desa Sukamaju No. 01 Kode Pos 16830 | Email: pemdes@sukamaju.desa.id</p>
                    </div>
                </div>

                <!-- TITLE SURAT -->
                <div class="text-center my-6">
                    <h2 class="font-extrabold text-base uppercase underline tracking-wide" x-text="app.service_name"></h2>
                    <p class="text-xs text-slate-700 mt-1 font-mono">Nomor: <span class="font-bold" x-text="app.letter_number || '503/012/SKU/X/2026'"></span></p>
                </div>

                <!-- BODY SURAT -->
                <div class="space-y-4 text-justify mt-8">
                    <p>
                        Yang bertanda tangan di bawah ini Kepala Desa Sukamaju, Kecamatan Jonggol, Kabupaten Bogor, menerangkan bahwa:
                    </p>

                    <div class="space-y-2 pl-8 text-sm">
                        <div class="grid grid-cols-12">
                            <span class="col-span-4 font-semibold">Nama Lengkap</span>
                            <span class="col-span-1">:</span>
                            <span class="col-span-7 font-bold uppercase" x-text="app.applicant_name"></span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-4 font-semibold">NIK</span>
                            <span class="col-span-1">:</span>
                            <span class="col-span-7 font-mono" x-text="app.applicant_nik"></span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-4 font-semibold">Alamat Domisili</span>
                            <span class="col-span-1">:</span>
                            <span class="col-span-7" x-text="app.applicant_address"></span>
                        </div>
                        <template x-if="app.business_name">
                            <div class="grid grid-cols-12">
                                <span class="col-span-4 font-semibold">Nama Usaha / Merk</span>
                                <span class="col-span-1">:</span>
                                <span class="col-span-7 font-bold text-slate-900" x-text="app.business_name"></span>
                            </div>
                        </template>
                        <div class="grid grid-cols-12">
                            <span class="col-span-4 font-semibold">Keperluan</span>
                            <span class="col-span-1">:</span>
                            <span class="col-span-7 italic" x-text="app.purpose"></span>
                        </div>
                    </div>

                    <p class="pt-2">
                        Benar bahwa nama yang bersangkutan di atas adalah penduduk yang bertempat tinggal di wilayah Desa Sukamaju dan tercatat berkelakuan baik serta aktif dalam kegiatan kemasyarakatan.
                    </p>

                    <p>
                        Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya oleh yang berkepentingan.
                    </p>
                </div>

                <!-- SIGNATURE & QR VALIDATION SECTION -->
                <div class="grid grid-cols-2 mt-16 pt-8 items-end">
                    
                    <!-- Left: QR Code Validation Box (PRD Bab 42) -->
                    <div class="border border-slate-300 rounded-xl p-3 max-w-[220px] bg-slate-50 text-center">
                        <!-- Simulated QR Code Grid -->
                        <div class="w-24 h-24 mx-auto bg-white p-1.5 border border-slate-300 rounded flex flex-col justify-between">
                            <div class="flex justify-between">
                                <div class="w-6 h-6 bg-slate-900"></div>
                                <div class="w-2 h-2 bg-slate-900 mt-2"></div>
                                <div class="w-6 h-6 bg-slate-900"></div>
                            </div>
                            <div class="flex justify-center gap-1">
                                <div class="w-2 h-2 bg-slate-900"></div>
                                <div class="w-3 h-3 bg-emerald-700"></div>
                                <div class="w-2 h-2 bg-slate-900"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="w-6 h-6 bg-slate-900"></div>
                                <div class="w-2 h-2 bg-slate-900 mb-1"></div>
                                <div class="w-6 h-6 bg-slate-900"></div>
                            </div>
                        </div>
                        <p class="text-[9px] font-bold text-slate-700 mt-1.5 uppercase">Scan QR Keabsahan</p>
                        <p class="text-[8px] font-mono text-slate-400 break-all" x-text="app.qr_uuid || 'SIADESA-DOC-20261005'"></p>
                    </div>

                    <!-- Right: Official Head Signature -->
                    <div class="text-center space-y-1">
                        <p class="text-xs">Sukamaju, 05 Oktober 2026</p>
                        <p class="font-bold text-xs uppercase">Kepala Desa Sukamaju</p>
                        
                        <!-- Signature Space -->
                        <div class="h-20 flex items-center justify-center">
                            <span class="text-emerald-800 text-xs italic font-semibold border-b border-dashed border-emerald-400 px-4 py-1 bg-emerald-50/50 rounded">
                                [ Ditandatangani secara Digital ]
                            </span>
                        </div>

                        <p class="font-extrabold text-sm uppercase underline">Drs. H. Mulyono</p>
                        <p class="text-xs text-slate-600 font-mono">NIP. 19680315 199203 1 004</p>
                    </div>

                </div>

            </div>
        </template>

    </div>

</body>
</html>
