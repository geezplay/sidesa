@php
$colors = [
    'DIAJUKAN' => 'bg-sky-50 text-sky-700 border-sky-200',
    'DIVERIFIKASI' => 'bg-blue-50 text-blue-700 border-blue-200',
    'MENUNGGU PERSETUJUAN' => 'bg-amber-50 text-amber-700 border-amber-200',
    'PERLU PERBAIKAN' => 'bg-amber-50 text-amber-700 border-amber-200',
    'DISETUJUI' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'DIPROSES' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    'SELESAI' => 'bg-emerald-600 text-white border-emerald-600',
    'DITOLAK' => 'bg-rose-50 text-rose-700 border-rose-200',
    'DRAFT' => 'bg-slate-100 text-slate-600 border-slate-200',
];
$cls = $colors[$status ?? ''] ?? 'bg-slate-100 text-slate-600 border-slate-200';
@endphp
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $cls }}">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    {{ $status ?? '-' }}
</span>
