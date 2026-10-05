PRD — Sistem Informasi Pelayanan Administrasi Desa/Kelurahan Berbasis Web

Nama Produk: SIADESA — Sistem Informasi Administrasi Desa/Kelurahan
Platform: Web Responsive
Target Pengguna: Pemerintah Desa/Kelurahan dan Masyarakat
Versi Dokumen: 1.1
Status: Final Revised / Development Ready
Bahasa: Indonesia
Wilayah: Indonesia

1. Ringkasan Produk

SIADESA adalah sistem informasi berbasis web yang digunakan untuk digitalisasi pelayanan administrasi desa/kelurahan, mulai dari pengajuan surat oleh masyarakat, verifikasi oleh perangkat desa, proses persetujuan, hingga penerbitan dokumen administrasi.

Sistem dirancang untuk mengurangi proses administrasi manual, mempercepat pelayanan masyarakat, meningkatkan transparansi status pengajuan, serta membantu pemerintah desa/kelurahan mengelola data penduduk dan arsip pelayanan secara terstruktur.

Masyarakat dapat mengajukan pelayanan secara online tanpa harus berulang kali datang ke kantor desa/kelurahan.

2. Latar Belakang

Pelayanan administrasi desa/kelurahan pada banyak wilayah masih dilakukan secara manual. Masyarakat harus datang ke kantor, membawa dokumen persyaratan, mengisi formulir, kemudian menunggu proses verifikasi dan penerbitan dokumen.

Permasalahan yang dapat muncul:

antrean pelayanan;
proses pengajuan membutuhkan waktu;
masyarakat kesulitan mengetahui status pengajuan;
dokumen pengajuan sulit dilacak;
arsip masih berbentuk dokumen fisik;
pencatatan pelayanan kurang terintegrasi;
risiko kesalahan input data;
pimpinan kesulitan memperoleh laporan pelayanan secara cepat.

SIADESA hadir sebagai solusi untuk membuat proses tersebut lebih terstruktur dan terdigitalisasi.

3. Tujuan Produk
Tujuan utama

Membangun sistem pelayanan administrasi desa/kelurahan yang:

mudah digunakan masyarakat;
mempercepat proses pengajuan administrasi;
mempermudah perangkat desa melakukan verifikasi;
menyediakan tracking status pengajuan;
menyediakan arsip dokumen digital;
menyediakan dashboard dan laporan;
meningkatkan transparansi pelayanan;
mengurangi penggunaan dokumen fisik;
menjaga keamanan data pengguna dan dokumen.
4. Sasaran Pengguna

Sistem memiliki beberapa role agar setiap pengguna hanya dapat mengakses fungsi yang sesuai.

Role	Pengguna	Fungsi Utama
Super Admin	Administrator sistem	Mengelola keseluruhan sistem
Admin Desa/Kelurahan	Operator	Mengelola data dan pelayanan
Verifikator	Petugas pelayanan	Memeriksa pengajuan
Kepala Desa/Lurah	Pimpinan	Persetujuan pelayanan
Masyarakat	Warga	Mengajukan pelayanan
RT/RW (opsional)	Ketua RT/RW	Verifikasi/rekomendasi warga

Untuk versi MVP, role RT/RW dapat dibuat opsional agar sistem tidak terlalu kompleks.

5. Hak Akses Role
5.1 Super Admin

Super Admin memiliki akses terhadap konfigurasi sistem.

Fitur:
dashboard sistem;
manajemen pengguna;
manajemen role;
manajemen desa/kelurahan;
manajemen jenis pelayanan;
konfigurasi persyaratan;
konfigurasi template surat;
pengaturan sistem;
audit log;
monitoring aktivitas;
backup/restore data (jika diperlukan).
5.2 Admin Desa/Kelurahan

Admin bertugas mengelola operasional pelayanan.

Fitur:
dashboard pelayanan;
data penduduk;
data keluarga;
data pengajuan;
data pelayanan;
verifikasi administrasi;
pengelolaan dokumen;
pencetakan surat;
pengelolaan arsip;
laporan pelayanan;
statistik pelayanan.
5.3 Verifikator

Verifikator fokus pada pemeriksaan pengajuan masyarakat.

Fitur:
melihat pengajuan baru;
memeriksa data pemohon;
memeriksa persyaratan;
melihat dokumen;
menerima pengajuan;
meminta perbaikan;
menolak pengajuan;
meneruskan pengajuan ke pimpinan;
memberikan catatan verifikasi.
5.4 Kepala Desa/Lurah

Pimpinan memiliki fungsi approval.

Fitur:
dashboard;
melihat pengajuan yang menunggu persetujuan;
melihat detail pengajuan;
melihat hasil verifikasi;
menyetujui pengajuan;
menolak pengajuan;
memberikan catatan;
melihat laporan pelayanan;
melihat statistik pelayanan.
5.5 Masyarakat

Masyarakat merupakan pengguna utama dari sisi pemohon.

Fitur:
registrasi akun;
login;
melengkapi profil;
melihat data diri;
mengajukan pelayanan;
upload persyaratan;
melihat status pengajuan;
memperbaiki pengajuan;
melihat riwayat pengajuan;
menerima notifikasi;
mengunduh dokumen yang telah diterbitkan.
5.6 RT/RW — Opsional

Jika pemerintah desa membutuhkan proses rekomendasi RT/RW, role ini dapat digunakan.

Fitur:
login;
melihat pengajuan warga;
memeriksa data;
memberikan rekomendasi;
menolak/revisi pengajuan;
memberikan catatan.
6. Modul Utama Sistem

Sistem dibagi menjadi beberapa modul utama:

Authentication & Authorization
Dashboard
Data Penduduk
Data Keluarga
Pelayanan Administrasi
Pengajuan Surat
Verifikasi
Approval
Penerbitan Dokumen
Notifikasi
Arsip Digital
Laporan
Statistik
Manajemen Pengguna
Pengaturan Sistem
Audit Log
7. Modul Authentication
7.1 Registrasi Masyarakat

Masyarakat dapat membuat akun menggunakan:

NIK (wajib 16 digit angka valid);
nama lengkap;
nomor HP / WhatsApp aktif;
email (opsional);
password (minimal 8 karakter).

Sistem melakukan validasi NIK agar satu NIK hanya dapat digunakan untuk membuat satu akun aktif (BR-01).

7.2 Login

Login menggunakan:

NIK atau email;
password.

Sistem mengarahkan pengguna ke dashboard berdasarkan role.

Contoh:

Masyarakat → Dashboard Masyarakat
Admin → Dashboard Admin
Verifikator → Dashboard Verifikator
Kepala Desa → Dashboard Pimpinan
Super Admin → Dashboard Super Admin

7.3 Keamanan Login & Sesi

Sistem harus menyediakan:

password hashing menggunakan algoritma Bcrypt / Argon2;
pembatasan percobaan login (rate limiting): akun dikunci sementara selama 15 menit setelah 5 kali gagal berturut-turut;
session timeout setelah 120 menit tidak ada aktivitas (idle);
role-based access control (RBAC) pada setiap route/middleware;
logout aman yang memusnahkan session & token;
alur Lupa Password / Reset Password: via OTP 6 digit ke nomor HP/WhatsApp atau link reset ke email dengan masa berlaku 10 menit;
validasi input dan sanitasi untuk mencegah XSS & SQL Injection.
8. Dashboard

Dashboard menampilkan informasi berdasarkan role.

Dashboard Admin

Contoh:

---------------------------------------------
Dashboard
---------------------------------------------

Total Penduduk          5.248
Pengajuan Baru             24
Diproses                    17
Menunggu Persetujuan         8
Selesai                     152

---------------------------------------------
Statistik Pelayanan
---------------------------------------------

Surat Domisili       ██████████ 120
Surat Keterangan     ████████    90
Surat Usaha          ██████      70
Surat Kelahiran      ████        40

Dashboard juga menampilkan:

pengajuan terbaru;
pengajuan yang perlu ditindaklanjuti;
grafik pelayanan;
statistik berdasarkan jenis surat;
statistik berdasarkan periode.
9. Modul Data Penduduk

Admin dapat mengelola database penduduk.

Data utama:
NIK;
Nomor KK;
nama lengkap;
tempat lahir;
tanggal lahir;
jenis kelamin;
alamat;
RT;
RW;
agama;
status perkawinan;
pekerjaan;
pendidikan;
kewarganegaraan;
nomor telepon.
Fitur:
tambah penduduk;
edit;
detail;
pencarian;
filter;
import data;
export data;
nonaktifkan data.
10. Modul Data Keluarga

Data keluarga dikelompokkan berdasarkan Nomor KK.

Informasi:

nomor KK;
kepala keluarga;
alamat;
RT/RW;
anggota keluarga;
jumlah anggota keluarga.

Admin dapat melihat struktur anggota dalam satu keluarga.

11. Modul Pelayanan Administrasi

Admin dapat menentukan jenis pelayanan yang tersedia.

Contoh:

Surat Keterangan
Surat Keterangan Domisili
Surat Keterangan Usaha
Surat Keterangan Tidak Mampu
Surat Keterangan Penghasilan
Surat Keterangan Belum Menikah
Administrasi Kependudukan
Surat Keterangan Kelahiran
Surat Keterangan Kematian
Surat Pengantar Pindah
Pelayanan Lain
Surat Pengantar SKCK
Surat Pengantar Pernikahan
Surat Keterangan Kehilangan
Surat Keterangan Lainnya

Jenis pelayanan harus dapat dikonfigurasi oleh Admin, sehingga sistem tidak bergantung pada daftar surat yang ditentukan secara permanen.

12. Konfigurasi Jenis Pelayanan

Setiap jenis pelayanan memiliki:

Nama Pelayanan
Kode Pelayanan
Deskripsi
Estimasi Waktu
Status Aktif
Persyaratan
Template Surat
Role Verifikator
Membutuhkan Approval?

Contoh:

Surat Keterangan Usaha

Kode       : SKU
Estimasi   : 1 Hari Kerja
Approval   : Kepala Desa
13. Modul Pengajuan Pelayanan

Masyarakat memilih:

Layanan → Jenis Surat → Ajukan

Sistem menampilkan:

informasi layanan;
persyaratan;
estimasi waktu;
formulir;
upload dokumen.
14. Form Pengajuan

Data masyarakat yang sudah tersimpan dapat otomatis ditampilkan.

Contoh:

Nama              : Budi Santoso
NIK               : 1471xxxxxxxxxxxx
Alamat            : Jl. Contoh No. 10
RT/RW             : 002/005

Keperluan:
[................................]

Dokumen Persyaratan:
KTP                [Upload]
KK                 [Upload]
Surat Pengantar    [Upload]

[Ajukan Permohonan]

Sistem harus melakukan validasi sebelum pengajuan dikirim.

15. Nomor Pengajuan dan Nomor Surat

Terdapat 2 jenis nomor identifikasi yang berbeda:

1. Nomor Pengajuan / Tiket (Tracking Code)
Diterbitkan otomatis saat permohonan pertama kali dikirimkan oleh pemohon.
Format: ADM-YYYYMMDD-XXXXXX (Contoh: ADM-20261005-000124)
Fungsi: Digunakan warga dan petugas untuk melacak progres alur permohonan.

2. Nomor Surat Resmi (Official Document Number)
Diterbitkan otomatis oleh sistem saat permohonan disetujui dan berstatus DIPROSES.
Format: disesuaikan dengan pola penomoran desa, misalnya: 001/SKU/X/2026
Fungsi: Menjadi nomor registrasi surat resmi pada buku register dan tercantum pada kop surat serta QR Code validasi.

16. Status Pengajuan

Alur status pengajuan menggunakan model state-machine bercabang (bukan linear satu arah):

                    [ DRAFT ]
                        │
                        ▼
                   [ DIAJUKAN ] ◄──────────────────┐
                        │                          │
                        ▼                          │
                 [ DIVERIFIKASI ]                  │
                   │          │                    │
        (Perlu     │          │ (Ditolak)          │
       Perbaikan)  ▼          ▼                    │
            ┌──────────────┐ [ DITOLAK ]           │
            │ PERLU        │                       │
            │ PERBAIKAN    ├───────────────────────┘ (Revisi kirim ulang)
            └──────────────┘
                   │
                   ▼ (Lolos verifikasi)
            [ MENUNGGU PERSETUJUAN ] (Jika layanan butuh approval kades)
              │                 │
              ▼ (Disetujui)     ▼ (Ditolak kades)
         [ DISETUJUI ]     [ DITOLAK ]
              │
              ▼
         [ DIPROSES ] (Penomoran & cetak/generate surat digital)
              │
              ▼
          [ SELESAI ] (Dokumen siap diunduh pemohon & diarsipkan)

Catatan:
- Status DIBATALKAN dapat dilakukan oleh pemohon hanya saat permohonan masih berstatus DRAFT atau DIAJUKAN (belum masuk tahap verifikasi).
- Jika layanan tidak memerlukan persetujuan Kepala Desa, alur dari DIVERIFIKASI langsung menuju DIPROSES.
17. Tracking Pengajuan

Masyarakat dapat melihat progress.

Contoh:

ADM-20261005-000124

✓ Pengajuan dikirim
      05 Okt 2026 - 09:12

✓ Dokumen diverifikasi
      05 Okt 2026 - 10:15

✓ Disetujui Kepala Desa
      05 Okt 2026 - 13:20

● Dokumen sedang diproses

○ Selesai

Setiap perubahan status dicatat dalam timeline.

18. Verifikasi Administrasi

Verifikator melihat daftar pengajuan.

Contoh:

Nomor	Pemohon	Layanan	Tanggal	Status
ADM-001	Budi	SKU	05/10	Baru
ADM-002	Siti	Domisili	05/10	Verifikasi

Verifikator dapat:

menerima;
meminta perbaikan;
menolak;
meneruskan.
19. Perbaikan Pengajuan

Jika terdapat kesalahan:

Status:
PERLU PERBAIKAN

Catatan Petugas:
"Dokumen KK tidak terlihat jelas.
Silakan upload kembali."

[Perbaiki Pengajuan]

Masyarakat dapat memperbaiki data/dokumen tanpa membuat pengajuan baru.

20. Approval Kepala Desa/Lurah

Jika layanan membutuhkan persetujuan pimpinan, pengajuan masuk ke halaman approval.

Pimpinan dapat melihat:

data pemohon;
jenis pelayanan;
dokumen;
hasil verifikasi;
catatan petugas.

Pilihan:

[Setujui]
[Tolak]

Jika ditolak, wajib memberikan alasan.

21. Pembuatan Dokumen

Setelah disetujui, sistem dapat menghasilkan surat berdasarkan template.

Contoh:

PEMERINTAH DESA XXXXX
KECAMATAN XXXXX
KABUPATEN XXXXX

SURAT KETERANGAN USAHA
Nomor: 001/SKU/X/2026

Yang bertanda tangan di bawah ini...

Nama        : Budi Santoso
NIK         : ...
Alamat      : ...

Menerangkan bahwa...

Dokumen dapat dibuat dalam format:

PDF;
cetak langsung.
22. Tanda Tangan

Untuk tahap awal, sistem dapat menggunakan:

Tanda tangan manual/digital sederhana.

Untuk pengembangan lebih lanjut dapat diintegrasikan dengan sistem tanda tangan elektronik resmi apabila diperlukan.

23. Notifikasi

Masyarakat mendapatkan notifikasi ketika:

pengajuan berhasil;
pengajuan sedang diverifikasi;
pengajuan perlu diperbaiki;
pengajuan disetujui;
pengajuan ditolak;
dokumen selesai.

Media notifikasi:

notifikasi dalam sistem;
email (opsional);
WhatsApp (opsional/integrasi tahap lanjut).
24. Arsip Digital

Semua pengajuan yang selesai disimpan sebagai arsip.

Admin dapat mencari berdasarkan:

nomor pengajuan;
NIK;
nama;
jenis pelayanan;
tanggal;
status.

Arsip dapat digunakan untuk kebutuhan administrasi dan laporan.

25. Laporan

Sistem menyediakan laporan:

Laporan pelayanan
jumlah pengajuan;
jumlah selesai;
jumlah ditolak;
jumlah perlu perbaikan.
Laporan berdasarkan jenis layanan
Periode: Januari–Desember 2026

Surat Domisili          230
Surat Usaha             180
SKTM                    120
Surat Kelahiran          65
Surat Kematian           42
Export

Laporan dapat diekspor ke:

PDF;
Excel/CSV.
26. Statistik

Dashboard dapat menampilkan:

jumlah penduduk;
jumlah KK;
jumlah pelayanan;
pelayanan per bulan;
pelayanan berdasarkan jenis;
pelayanan berdasarkan status;
rata-rata waktu penyelesaian.

Contoh:

Pelayanan per Bulan

Jan ███████
Feb █████████
Mar █████
Apr ███████████
Mei ██████████
27. Audit Log

Setiap aktivitas penting dicatat.

Contoh:

05-10-2026 10:12
Admin Andi
Mengubah status pengajuan ADM-001
DIAJUKAN → DIVERIFIKASI

Audit log berguna untuk:

keamanan;
monitoring;
investigasi;
transparansi.
28. Manajemen Pengguna

Admin dapat:

membuat akun petugas;
mengubah role;
menonaktifkan akun;
reset password;
melihat aktivitas pengguna.

Struktur:

User
 └── Role
      ├── Super Admin
      ├── Admin
      ├── Verifikator
      ├── Kepala Desa
      ├── RT/RW
      └── Masyarakat
29. Struktur Menu

Struktur navigasi disesuaikan dengan peran (Role-Based Access Control):

Masyarakat:
├── Dashboard
├── Katalog Layanan
├── Pengajuan Baru
├── Pengajuan Saya (Tracking Aktif)
├── Riwayat & Arsip Surat
├── Notifikasi
└── Profil & Data Pribadi

Verifikator (Petugas Pelayanan):
├── Dashboard Pelayanan
├── Antrean Verifikasi (Pengajuan Baru)
├── Pengajuan Perlu Perbaikan
├── Riwayat Verifikasi
└── Profil

Kepala Desa / Lurah:
├── Dashboard Pimpinan
├── Menunggu Persetujuan (Approval)
├── Riwayat Persetujuan
├── Laporan & Statistik
└── Profil

Admin Desa/Kelurahan:
├── Dashboard Operasional
├── Master Data
│   ├── Data Penduduk
│   ├── Data Keluarga (KK)
│   └── Data RT/RW
├── Pelayanan
│   ├── Semua Pengajuan
│   ├── Kelola Jenis Pelayanan & Syarat
│   ├── Pembuatan / Cetak Surat
│   └── Arsip Digital
├── Laporan & Statistik
├── Manajemen Akun Petugas
└── Pengaturan Desa & Kop Surat

Super Admin:
├── Dashboard Sistem
├── Manajemen Desa / Kelurahan
├── Manajemen Pengguna Global & Role
├── Audit Log Global
└── Pengaturan Sistem

RT/RW (Opsional):
├── Dashboard RT/RW
├── Rekomendasi Warga
└── Data Warga RT/RW
30. Halaman Publik

Website juga memiliki halaman yang dapat diakses tanpa login.

Beranda

Berisi:

identitas desa/kelurahan;
informasi pelayanan;
layanan yang tersedia;
cara menggunakan sistem;
berita/pengumuman;
kontak kantor.
Informasi Pelayanan

Masyarakat dapat melihat:

Surat Keterangan Usaha

Persyaratan:
✓ KTP
✓ KK
✓ Surat Pengantar

Estimasi:
1 Hari Kerja

[Ajukan Sekarang]

Jika belum login:

pengguna diarahkan ke halaman login/registrasi.

31. UI/UX Requirements

Sistem harus menggunakan desain yang:

sederhana;
modern;
profesional;
mudah dipahami masyarakat;
responsive;
mobile-friendly;
aksesibel;
memiliki navigasi yang konsisten.
Rekomendasi visual & Design Tokens

Primary Color: Hijau pemerintahan/desa (Emerald Green)
Secondary Color: Putih Bersih (Clean White)

Palet Warna (Tailwind Reference):
- Primary (Emerald): emerald-700 (#047857) untuk brand utama, header, dan aksi primer.
- Primary Hover: emerald-800 (#065f46) untuk hover tombol utama.
- Primary Light/Tint: emerald-50 (#ecfdf5) untuk background badge, aksen tabel, dan alert sukses.
- Surface / Cards: #ffffff (Putih murni).
- Background App: slate-50 (#f8fafc) abu-abu terang sejuk dan tidak silau.
- Border: slate-200 (#e2e8f0) garis pemisah halus dan elegan.
- Text Utama: slate-800 (#1e293b) kontras tinggi mudah dibaca.
- Text Muted/Label: slate-500 (#64748b).
- Status Success: emerald-600 / bg-emerald-50 (SELESAI / DISETUJUI).
- Status Warning: amber-600 / bg-amber-50 (PERLU PERBAIKAN / MENUNGGU).
- Status Danger: rose-600 / bg-rose-50 (DITOLAK / BATAL).
- Status Info: sky-600 / bg-sky-50 (DIAJUKAN / DIPROSES).

Tipografi:
- Font Family: Inter / System UI sans-serif.
- Hierarki jelas: Heading bold formal, microcopy informatif, label form tegas.

32. Responsive Design

Sistem wajib mendukung:

Desktop;
Laptop;
Tablet;
Smartphone.

Masyarakat terutama harus dapat mengakses sistem melalui smartphone.

33. Persyaratan Non-Fungsional
Performance

Target:

halaman utama < 3 detik pada kondisi jaringan normal;
penggunaan pagination untuk data besar;
optimasi gambar;
caching jika diperlukan.
Security

Minimal:

HTTPS;
password hashing;
authorization berbasis role;
validasi input;
proteksi upload file;
CSRF protection;
XSS protection;
SQL Injection protection;
session timeout;
audit log.
34. Keamanan Dokumen

Dokumen masyarakat bersifat privat.

File tidak boleh dapat diakses hanya dengan mengetahui URL file.

Contoh tidak aman:

/uploads/ktp/budi.jpg

Sebaiknya:

file → private storage
       ↓
authorized request
       ↓
server validation
       ↓
download

Server harus memeriksa apakah pengguna memang memiliki hak untuk mengakses dokumen tersebut.

35. Validasi Upload & Kebijakan Privasi

Format yang diperbolehkan:
- PDF (.pdf)
- JPG / JPEG (.jpg, .jpeg)
- PNG (.png)

Aturan Validasi:
- Ukuran maksimal: 2 MB per file berkas.
- Maksimal lampiran: 5 dokumen per pengajuan.
- Pemeriksaan MIME type ketat di sisi server (mimes:pdf,jpg,jpeg,png).
- Penamaan file otomatis di-hash / uuid di server untuk mencegah path traversal.
- Lokasi penyimpanan: direktori non-publik (`storage/app/private/documents/{application_id}/`).
- Akses berkas: Menggunakan URL sementara bertanda tangan (signed temporary URLs) dengan masa kedaluwarsa 15 menit, hanya untuk pemilik pengajuan atau petugas berwenang.
- Kepatuhan UU Perlindungan Data Pribadi (UU PDP No. 27/2022): Dokumen KTP, KK, dan surat keterangan dienkripsi saat transit (HTTPS) dan disimpan dengan hak akses terbatas. Retensi dokumen digital maksimal 5 tahun setelah penyelesaian layanan sebelum diarsipkan secara permanen.

36. Database Utama

Sistem menggunakan database relasional dengan tabel inti:

villages (id, code, name, district, regency, province, address, postal_code, logo_path, official_head_name, official_head_nip, created_at, updated_at)
users (id, village_id, name, nik, email, phone, password, role_id, is_active, created_at, updated_at)
roles (id, name, label, description)
permissions (id, name, group)
role_permissions (role_id, permission_id)

residents (id, village_id, nik, family_card_no, full_name, birth_place, birth_date, gender, address, rt, rw, religion, marital_status, occupation, education, citizenship, phone_number, is_active)
families (id, village_id, family_card_no, head_of_family_name, address, rt, rw, total_members)
family_members (id, family_id, resident_id, relationship_status)

services (id, village_id, name, code, description, processing_days, is_active, requires_approval, approver_role, letter_template_id)
service_requirements (id, service_id, name, description, is_mandatory, file_type_allowed)
service_templates (id, village_id, service_id, template_name, letter_number_pattern, body_html, signature_placeholder)

applications (id, village_id, user_id, resident_id, service_id, tracking_number, purpose, status, current_step, revision_notes, rejection_reason, created_at, updated_at)
application_documents (id, application_id, requirement_id, file_path, original_filename, file_size, mime_type, verification_status, notes)
application_status_histories (id, application_id, user_id, from_status, to_status, note, created_at)

verifications (id, application_id, verifier_user_id, status, notes, verified_at)
approvals (id, application_id, approver_user_id, status, notes, approved_at)

generated_documents (id, application_id, letter_number, document_title, qr_uuid, pdf_path, issued_date, valid_until, signer_name, signer_title)

notifications (id, user_id, title, message, type, url, is_read, created_at)
audit_logs (id, village_id, user_id, action, entity, entity_id, old_values, new_values, ip_address, user_agent, created_at)
settings (id, village_id, key, value, group)

37. Relasi Utama

VILLAGE (Multi-tenant isolasi data)
 │
 ├── USERS
 ├── RESIDENTS
 └── APPLICATIONS
       │
       ├── SERVICE
       ├── DOCUMENTS
       ├── VERIFICATION (Oleh Verifikator)
       ├── APPROVAL (Oleh Kepala Desa/Lurah)
       ├── STATUS HISTORY (Timeline log)
       └── GENERATED DOCUMENT (Surat Jadi + QR Code UUID)
38. Alur Utama Sistem
Alur Masyarakat
Registrasi
   ↓
Login
   ↓
Lengkapi Profil
   ↓
Pilih Layanan
   ↓
Lihat Persyaratan
   ↓
Isi Form
   ↓
Upload Dokumen
   ↓
Kirim Pengajuan
   ↓
Tracking
Alur Petugas
Pengajuan Baru
      ↓
Verifikasi
      ↓
Valid?
 ┌────┴─────┐
Tidak       Ya
 ↓           ↓
Revisi     Approval
              ↓
        Kepala Desa/Lurah
              ↓
         Disetujui?
          ┌────┴────┐
        Tidak       Ya
          ↓          ↓
        Tolak     Generate Surat
                       ↓
                    Selesai
39.1 Matriks Hak Akses Peran (Role Permission Matrix)

| Modul/Fungsi               | Super Admin | Admin Desa | Verifikator | Kepala Desa | Masyarakat | RT/RW |
|----------------------------|-------------|------------|-------------|-------------|------------|-------|
| Kelola Desa                | CRUD        | Read       | -           | Read        | -          | -     |
| Kelola Pengguna & Role     | CRUD        | CRUD*      | -           | Read        | Profil     | Profil|
| Data Penduduk & Keluarga   | CRUD        | CRUD       | Read        | Read        | Profil     | Read  |
| Kelola Jenis Layanan       | CRUD        | CRUD       | Read        | Read        | Read       | Read  |
| Ajukan Pelayanan           | -           | -          | -           | -           | Create     | -     |
| Verifikasi Pengajuan       | Read        | Read       | Update      | Read        | Read       | Update|
| Persetujuan (Approval)     | -           | -          | -           | Approve     | Read       | -     |
| Cetak & Generate Surat     | Read        | CRUD       | -           | Sign        | Download   | -     |
| Arsip & Laporan            | Read        | CRUD       | Read        | Read        | Read       | -     |
| Audit Log & Pengaturan     | Full        | Read       | -           | -           | -          | -     |

*Admin Desa hanya dapat mengelola akun di lingkup desanya sendiri (village_id yang sama).

39. Business Rules

Beberapa aturan bisnis penting:

BR-01

Satu NIK hanya dapat memiliki satu akun masyarakat aktif.

BR-02

Masyarakat tidak dapat mengajukan layanan apabila data profil wajib belum lengkap. Field wajib meliputi:
- NIK (16 digit)
- Nama Lengkap (sesuai KTP)
- Tempat & Tanggal Lahir
- Jenis Kelamin
- Alamat Lengkap, RT, dan RW
- Nomor Telepon / WhatsApp Aktif
- Nomor Kartu Keluarga (KK)
Jika salah satu field di atas masih kosong, sistem akan menampilkan peringatan dan mengunci tombol "Ajukan Permohonan" hingga profil dilengkapi.

BR-03

Setiap layanan memiliki persyaratan yang berbeda.

BR-04

Pengajuan tidak dapat diverifikasi sebelum seluruh dokumen wajib tersedia.

BR-05

Pengajuan yang membutuhkan approval harus disetujui Kepala Desa/Lurah sebelum surat diterbitkan.

BR-06

Pengajuan yang ditolak harus memiliki alasan penolakan.

BR-07

Pengajuan yang membutuhkan perbaikan harus memiliki catatan dari petugas.

BR-08

Setiap perubahan status harus masuk ke status history.

BR-09

Dokumen privat hanya dapat diakses oleh pengguna yang memiliki hak akses.

BR-10

Pengajuan yang sudah selesai tidak boleh diubah secara langsung tanpa hak khusus.

40. Acceptance Criteria

Sistem dianggap memenuhi MVP apabila:

Authentication
 masyarakat dapat registrasi;
 pengguna dapat login;
 role dapat membatasi akses;
 pengguna dapat logout.
Data
 admin dapat mengelola penduduk;
 admin dapat mengelola keluarga;
 pencarian penduduk tersedia.
Pelayanan
 admin dapat membuat jenis layanan;
 admin dapat menentukan persyaratan;
 masyarakat dapat melihat layanan;
 masyarakat dapat mengajukan layanan.
Verifikasi
 petugas dapat memeriksa pengajuan;
 petugas dapat meminta perbaikan;
 petugas dapat menolak pengajuan;
 petugas dapat meneruskan pengajuan.
Approval
 kepala desa dapat melihat pengajuan;
 kepala desa dapat menyetujui;
 kepala desa dapat menolak.
Dokumen
 sistem dapat menghasilkan surat;
 surat dapat dicetak;
 masyarakat dapat mengakses dokumen yang telah selesai.
Tracking
 masyarakat dapat melihat status;
 timeline status tersedia;
 riwayat pengajuan tersedia.
Reporting
 laporan pelayanan tersedia;
 statistik tersedia;
 laporan dapat diekspor.
41. MVP

Untuk versi pertama, saya menyarankan jangan langsung membuat seluruh fitur pemerintahan.

Prioritaskan:

Phase 1 — Core
Login/Register
Role & Permission
Data Penduduk
Data Keluarga
Jenis Pelayanan
Pengajuan Online
Upload Dokumen
Verifikasi
Approval
Generate Surat
Tracking Status
Notifikasi
Dashboard
Phase 2 — Management
Arsip digital
Laporan
Statistik
Audit Log
Export Excel/PDF
Konfigurasi template surat
Phase 3 — Advanced
WhatsApp notification
tanda tangan elektronik;
integrasi data kependudukan;
QR Code validasi surat;
API;
mobile application;
integrasi layanan pemerintah lainnya.
42. QR Code Validasi Dokumen

Fitur Verifikasi Keaslian Surat via QR Code merupakan fitur unggulan sistem.

Mekanisme:
- Setiap surat yang diterbitkan dan berstatus SELESAI otomatis memiliki QR Code yang tercetak langsung pada dokumen fisik/digital.
- QR Code menyimpan token unik (UUID v4) yang mengarah ke Halaman Verifikasi Publik: `GET /verifikasi-surat/{kode_unik}`.
- Format Identifikasi: `SIADESA-DOC-XXXXXXXX-XXXXXXXX` (contoh: SIADESA-DOC-20261005-8F3A2B9C).

Ketika QR Code dipindai:

Dokumen Terverifikasi

Nomor Surat      : 001/SKU/X/2026
Jenis Surat      : Surat Keterangan Usaha
Nama Tertera     : B*** S*******
Tanggal Terbit   : 05 Oktober 2026
Desa Penerbit    : Desa/Kelurahan XXXXX
Status           : VALID

Kebijakan Privasi Tampilan Publik:
- Menggunakan sensor nama parsial (contoh: "Budi Santoso" menjadi "B*** S******") sesuai kepatuhan UU PDP.
- Tidak menampilkan NIK, Alamat lengkap, Nomor Telepon, atau detail data pemohon di halaman publik verifikasi.
- Status TIDAK VALID / TIDAK DITEMUKAN jika UUID belum pernah diterbitkan atau tanggal kedaluwarsa surat telah lewat (misalnya Surat Keterangan Usaha hanya berlaku 6 bulan).

43. KPI Sistem

Keberhasilan sistem dapat diukur melalui:

KPI	Target
Pengajuan online	≥ 80%
Pengajuan berhasil diproses	≥ 95%
Pengajuan dapat dilacak	100%
Dokumen terdigitalisasi	≥ 90%
Pengurangan proses manual	≥ 50%
Ketersediaan sistem	≥ 99%
Kepuasan pengguna	≥ 85%

Target tersebut dapat disesuaikan setelah sistem digunakan pada kondisi nyata.

44. Rekomendasi Tech Stack

Untuk proyek kuliah sekaligus portfolio, saya merekomendasikan:

Frontend
Next.js / React
TypeScript
Tailwind CSS
Backend

Pilihan 1:

Laravel
PHP
REST API

atau pilihan 2:

Node.js
NestJS/Express
TypeScript
Database
PostgreSQL atau MySQL
Storage
Local/private storage untuk development
Object storage untuk production
Authentication
Session / JWT
Role-Based Access Control
Deployment
Frontend
   ↓
Vercel / VPS

Backend
   ↓
VPS / Cloud Server

Database
   ↓
PostgreSQL / MySQL

Storage
   ↓
Object Storage
45. Struktur Arsitektur
                    ┌───────────────────┐
                    │     Masyarakat    │
                    └─────────┬─────────┘
                              │
                    ┌─────────▼─────────┐
                    │    Web Frontend   │
                    └─────────┬─────────┘
                              │
                         REST API
                              │
                    ┌─────────▼─────────┐
                    │   Backend Server  │
                    │                   │
                    │ Authentication    │
                    │ Authorization     │
                    │ Pelayanan         │
                    │ Verifikasi        │
                    │ Approval          │
                    │ Reporting         │
                    └──────┬──────┬─────┘
                           │      │
                 ┌─────────▼─┐  ┌─▼──────────┐
                 │ Database  │  │ File Storage│
                 └───────────┘  └─────────────┘
46. Prioritas Fitur
Fitur	Prioritas
Login/Register	🔴 Must Have
Role Management	🔴 Must Have
Data Penduduk	🔴 Must Have
Data Keluarga	🔴 Must Have
Pengajuan Online	🔴 Must Have
Upload Dokumen	🔴 Must Have
Verifikasi	🔴 Must Have
Approval	🔴 Must Have
Generate Surat	🔴 Must Have
Tracking	🔴 Must Have
Dashboard	🔴 Must Have
Notifikasi	🟠 Should Have
Arsip Digital	🟠 Should Have
Laporan	🟠 Should Have
Statistik	🟠 Should Have
Audit Log	🟠 Should Have
QR Validasi	🟢 Nice to Have
WhatsApp	🟢 Nice to Have
E-Signature	🟢 Nice to Have
47. Kesimpulan Produk

SIADESA bukan hanya website untuk membuat surat, tetapi merupakan sistem manajemen pelayanan administrasi desa/kelurahan end-to-end.

Alur utamanya adalah:

Masyarakat → Pengajuan → Verifikasi → Persetujuan → Penerbitan Surat → Arsip → Pelaporan

Dengan pendekatan role-based seperti ini, sistem dapat digunakan oleh:

Masyarakat
→ mengajukan pelayanan.

Verifikator
→ memeriksa persyaratan.

Admin
→ mengelola data dan operasional.

Kepala Desa/Lurah
→ memberikan persetujuan.

Super Admin
→ mengelola keseluruhan sistem dan integrasi multi-desa.