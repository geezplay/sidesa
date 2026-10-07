# ANALISIS DAN PERANCANGAN

Bab ini menyajikan hasil analisis kebutuhan dan perancangan sistem SIADESA (Sistem Informasi Administrasi Desa/Kelurahan) secara komprehensif. Analisis dilakukan terhadap pemangku kepentingan, aktor sistem, hak akses, kebutuhan fungsional, serta kebutuhan non-fungsional. Perancangan sistem disusun melalui pemodelan *use case*, *activity diagram*, *context diagram*, *Data Flow Diagram* (DFD), *sequence diagram*, *class diagram*, arsitektur sistem, *Entity Relationship Diagram* (ERD), dan perancangan basis data. Seluruh artefak perancangan disusun berdasarkan studi terhadap kode sumber (*codebase*) aktual sistem yang telah dikembangkan.

## Identifikasi Stakeholder

Identifikasi pemangku kepentingan (*stakeholder*) merupakan tahap awal dalam analisis kebutuhan sistem informasi [@dennis2015systems]. Pada konteks pengembangan SIADESA, terdapat dua kelompok pemangku kepentingan utama yang memiliki kepentingan langsung terhadap sistem yang dikembangkan.

Pemangku kepentingan pertama adalah **Pemerintah Desa Sukamaju** yang berlokasi di Kecamatan Jonggol, Kabupaten Bogor, Provinsi Jawa Barat. Pemerintah desa memerlukan sistem yang mampu meningkatkan efisiensi pelayanan administrasi kependudukan, mengurangi beban kerja manual perangkat desa, serta menyediakan mekanisme pelaporan dan rekapitulasi data pelayanan secara digital. Digitalisasi proses administrasi diharapkan dapat mempercepat waktu penerbitan dokumen resmi, mengurangi kesalahan pencatatan data, dan meningkatkan akuntabilitas pelayanan publik.

Pemangku kepentingan kedua adalah **Masyarakat atau Warga Desa Sukamaju** selaku penerima layanan administrasi. Warga desa memerlukan akses yang mudah, cepat, dan transparan terhadap berbagai layanan administrasi kependudukan tanpa harus datang berulang kali ke kantor desa. Masyarakat juga memerlukan mekanisme pelacakan status pengajuan secara mandiri serta jaminan keamanan data pribadi sesuai ketentuan perundang-undangan yang berlaku.

## Identifikasi Aktor

Berdasarkan analisis terhadap proses bisnis pelayanan administrasi desa dan implementasi *Role-Based Access Control* (RBAC) pada kode sumber sistem, diidentifikasi tiga aktor utama yang berinteraksi dengan SIADESA. Setiap aktor memiliki peran (*role*) yang terdefinisi secara eksplisit dalam enumerasi `Role` pada skema basis data Prisma, yaitu `WARGA`, `ADMIN`, dan `KADES`.

| Aktor | *Role* Sistem | Deskripsi |
|---|---|---|
| Warga/Masyarakat | `WARGA` | Mengajukan pelayanan administrasi secara *online*, melacak status pengajuan melalui nomor tiket, mengunggah dokumen persyaratan, memperbaiki pengajuan yang dikembalikan, dan mengunduh dokumen surat resmi yang telah diterbitkan |
| Admin Desa | `ADMIN` | Mengelola data penduduk (CRUD dan impor massal), memverifikasi kelengkapan dan keabsahan dokumen pengajuan, mengelola konfigurasi jenis layanan dan persyaratan, mengelola akun petugas, mengatur profil dan konten *website* desa, serta mencetak surat resmi |
| Kepala Desa | `KADES` | Menyetujui atau menolak pengajuan yang telah lolos verifikasi dokumen oleh Admin Desa, memberikan pengesahan resmi disertai penerbitan nomor surat dan kode QR validasi |

Pembagian peran tersebut mengimplementasikan prinsip *separation of duties*, di mana proses verifikasi dokumen (dilakukan oleh Admin Desa) dipisahkan dari proses persetujuan dan pengesahan (dilakukan oleh Kepala Desa). Pemisahan tanggung jawab ini dirancang untuk meningkatkan akuntabilitas dan mengurangi risiko penyalahgunaan wewenang dalam penerbitan dokumen resmi.

## Hak Akses

Matriks hak akses berikut mendefinisikan izin (*permission*) yang dimiliki oleh masing-masing peran terhadap fitur-fitur sistem. Penetapan hak akses diimplementasikan melalui *middleware* `requireRole()` pada lapisan *routing* Express.js, yang memvalidasi peran pengguna berdasarkan *payload* token JWT sebelum mengizinkan akses ke *endpoint* API tertentu.

| Fitur/Aksi | WARGA | ADMIN | KADES |
|---|:---:|:---:|:---:|
| Registrasi akun (berbasis NIK) | Ya | --- | --- |
| Login (NIK/email + kata sandi) | Ya | Ya | Ya |
| Lihat dan lengkapi profil kependudukan | Ya | --- | --- |
| Katalog layanan publik (tanpa login) | Ya | Ya | Ya |
| Ajukan pelayanan administrasi | Ya | --- | --- |
| Unggah dokumen persyaratan | Ya | --- | --- |
| Lacak status pengajuan (nomor tiket) | Ya | Ya | Ya |
| Lihat riwayat pengajuan pribadi | Ya | --- | --- |
| Perbaiki pengajuan (*resubmit*) | Ya | --- | --- |
| Dashboard statistik pelayanan | --- | Ya | --- |
| Verifikasi pengajuan (*approve*/*revise*/*reject*) | --- | Ya | --- |
| Daftar seluruh pengajuan desa | --- | Ya | --- |
| CRUD data penduduk | --- | Ya | --- |
| Impor data penduduk dari Excel | --- | Ya | --- |
| CRUD jenis layanan dan persyaratan | --- | Ya | --- |
| CRUD akun petugas (*staff*) | --- | Ya | --- |
| Pengaturan profil dan konten *website* desa | --- | Ya | --- |
| Cetak surat resmi | --- | Ya | --- |
| Dashboard antrean persetujuan | --- | --- | Ya |
| Review pengajuan terverifikasi | --- | --- | Ya |
| Persetujuan (*approve* + penerbitan nomor surat + QR) | --- | --- | Ya |
| Penolakan pengajuan dengan alasan | --- | --- | Ya |

## Analisis Kebutuhan Fungsional

Kebutuhan fungsional (*functional requirements*) mendefinisikan fungsi-fungsi yang harus disediakan oleh sistem untuk memenuhi kebutuhan pengguna [@pressman2014software]. Identifikasi kebutuhan fungsional SIADESA dilakukan berdasarkan analisis terhadap kode sumber *controller*, *route*, dan skema basis data yang telah diimplementasikan. Berikut adalah daftar kebutuhan fungsional yang teridentifikasi:

| ID | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| FR-01 | Registrasi akun warga | Warga dapat mendaftarkan akun menggunakan NIK 16 digit yang wajib telah terdaftar dalam data penduduk desa (*business rule* BR-01). Sistem memvalidasi keunikan NIK dan menautkan akun pengguna dengan data kependudukan (*Resident*) yang sudah ada |
| FR-02 | Login pengguna | Pengguna dapat masuk ke sistem menggunakan NIK atau email beserta kata sandi. Autentikasi menghasilkan token JWT dengan masa berlaku 12 jam. *Endpoint* login dilindungi *rate limiter* dengan batas maksimum 20 percobaan per menit |
| FR-03 | Kelengkapan profil kependudukan | Warga wajib melengkapi data kependudukan (nomor KK, tempat/tanggal lahir, alamat, RT/RW) sebelum dapat mengajukan pelayanan (*business rule* BR-02). Sistem memvalidasi kelengkapan profil melalui pengecekan data *Resident* terkait |
| FR-04 | Katalog layanan publik | Sistem menyediakan halaman katalog layanan administrasi yang dapat diakses tanpa autentikasi. Informasi yang ditampilkan meliputi nama layanan, kategori, deskripsi, estimasi waktu, dan daftar persyaratan dokumen |
| FR-05 | Pengajuan pelayanan *online* | Warga dapat mengajukan pelayanan melalui formulir digital yang mencakup pemilihan jenis layanan, pengisian keperluan surat, dan pengunggahan dokumen persyaratan. Sistem mendukung pengunggahan maksimal 5 berkas dengan ukuran maksimum 2 MB per berkas dalam format PDF, JPG, atau PNG |
| FR-06 | Pemberian nomor tiket otomatis | Setiap pengajuan yang berhasil dibuat secara otomatis mendapatkan nomor tiket unik dengan format `ADM-YYYYMMDD-XXXXXX`, di mana `YYYYMMDD` adalah tanggal pengajuan dan `XXXXXX` adalah enam digit acak |
| FR-07 | Pelacakan status pengajuan publik | Masyarakat dapat melacak status pengajuan melalui halaman publik dengan memasukkan nomor tiket. Informasi yang ditampilkan meliputi jenis layanan, status terkini, dan riwayat perubahan status |
| FR-08 | Verifikasi pengajuan oleh Admin Desa | Admin Desa dapat memverifikasi kelengkapan dan keabsahan dokumen pengajuan dengan tiga opsi keputusan: menyetujui (*approve*) dan meneruskan ke Kepala Desa, meminta perbaikan (*revise*) dengan catatan, atau menolak (*reject*) dengan alasan |
| FR-09 | Perbaikan pengajuan oleh warga | Warga dapat memperbaiki dan mengajukan ulang (*resubmit*) permohonan yang berstatus `PERLU_PERBAIKAN`, termasuk mengunggah dokumen pengganti. Status pengajuan dikembalikan ke `DIAJUKAN` untuk diverifikasi ulang |
| FR-10 | Persetujuan Kepala Desa | Kepala Desa dapat menyetujui atau menolak pengajuan yang berstatus `MENUNGGU_PERSETUJUAN`. Persetujuan menghasilkan nomor surat resmi dengan format `500/XXX/CODE/X/YEAR` dan kode QR unik dengan format `SIADESA-DOC-XXXXXXXX-XXXXXXXX` untuk validasi keaslian dokumen |
| FR-11 | Verifikasi keaslian dokumen via QR Code | Halaman publik menyediakan fitur verifikasi keaslian dokumen melalui pemindaian atau pemasukan kode QR. Nama pemohon ditampilkan dalam bentuk tersamarkan (*privacy-masked*) sesuai ketentuan UU PDP [@uupdb2022] |
| FR-12 | Manajemen data penduduk | Admin Desa dapat menambah, melihat, mengubah, dan menghapus data penduduk. Fitur pencarian mendukung pencarian berdasarkan nama, NIK, atau nomor KK. Sistem juga mendukung impor data massal dari berkas Excel |
| FR-13 | Manajemen jenis layanan dan persyaratan | Admin Desa dapat menambah, mengubah, dan menonaktifkan jenis layanan administrasi beserta daftar persyaratan dokumennya. Setiap layanan memiliki kode unik per desa, kategori, deskripsi, dan estimasi waktu penyelesaian |
| FR-14 | Dashboard statistik pelayanan | Dashboard Admin Desa menampilkan statistik ringkasan meliputi total penduduk terdaftar, total pengajuan, pengajuan menunggu verifikasi, pengajuan menunggu persetujuan Kepala Desa, dan pengajuan selesai, serta daftar lima pengajuan terbaru |
| FR-15 | Manajemen akun petugas | Admin Desa dapat membuat, mengubah, dan menghapus akun petugas dengan peran `ADMIN` atau `KADES`. Sistem membatasi maksimum satu akun Kepala Desa per desa dan menjamin minimum satu akun Admin Desa tetap aktif |

## Analisis Kebutuhan Non-Fungsional

Kebutuhan non-fungsional (*non-functional requirements*) mendefinisikan atribut kualitas sistem yang harus dipenuhi agar sistem dapat beroperasi secara andal, aman, dan sesuai standar [@sommerville2015software]. Berikut adalah kebutuhan non-fungsional yang diidentifikasi berdasarkan implementasi aktual pada kode sumber SIADESA:

| ID | Kategori | Deskripsi |
|---|---|---|
| NFR-01 | Keamanan | Kata sandi pengguna di-*hash* menggunakan algoritma bcrypt dengan *salt round* 10 [@provos1999bcrypt]. Autentikasi berbasis JWT (*JSON Web Token*) [@jones2015json] dengan masa berlaku 12 jam. *Rate limiting* diterapkan pada *endpoint* login dengan batas 20 percobaan per menit. *Security headers* dikonfigurasi melalui Helmet.js. Kebijakan CORS membatasi akses hanya dari domain *frontend* yang diizinkan |
| NFR-02 | Privasi data | Sistem mematuhi ketentuan UU Pelindungan Data Pribadi Nomor 27 Tahun 2022 [@uupdb2022]. Pada halaman verifikasi dokumen publik, nama pemohon ditampilkan dalam bentuk tersamarkan (*privacy-masked*) dengan menampilkan hanya huruf pertama setiap kata diikuti tanda asterisk |
| NFR-03 | Validasi unggahan | Pengunggahan berkas divalidasi melalui *whitelist* tipe MIME (`image/jpeg`, `image/png`, `application/pdf`). Ukuran maksimum per berkas dibatasi 2 MB. Nama berkas diganti dengan UUID unik untuk mencegah konflik dan serangan *path traversal*. Berkas disimpan pada direktori *private storage* yang tidak dapat diakses langsung melalui URL publik |
| NFR-04 | Performa | Halaman web harus dapat dimuat dalam waktu kurang dari 3 detik pada koneksi *broadband* standar |
| NFR-05 | Responsivitas | Antarmuka pengguna dirancang responsif dan ramah perangkat bergerak (*mobile-friendly*) menggunakan *utility-first CSS framework* Tailwind CSS 4.x [@tailwindcss2024docs] |
| NFR-06 | Multi-*tenant* | Sistem mendukung isolasi data berdasarkan `village_id` pada setiap model data, sehingga satu *instance* aplikasi dapat melayani lebih dari satu desa tanpa terjadi kebocoran data antar-desa |
| NFR-07 | Ketersediaan | Sistem di-*deploy* pada *Virtual Private Server* (VPS) dengan PM2 sebagai *process manager* [@pm2docs2024] untuk menjamin ketersediaan proses Node.js, serta Nginx sebagai *reverse proxy* [@nginx2024docs] dengan sertifikat SSL Let's Encrypt untuk enkripsi komunikasi |
| NFR-08 | Kegunaan (*Usability*) | Antarmuka pengguna dirancang sederhana dengan navigasi yang konsisten. Menu sistem ditampilkan secara dinamis berdasarkan peran pengguna (*role-based menu*), sehingga setiap aktor hanya melihat fitur yang relevan dengan tanggung jawabnya |

## Use Case Diagram

*Use case diagram* menggambarkan interaksi antara aktor-aktor sistem dengan fungsi-fungsi utama yang disediakan oleh SIADESA [@dennis2015systems]. Diagram ini mengidentifikasi tiga aktor utama --- Warga, Admin Desa, dan Kepala Desa --- beserta *use case* yang dapat diakses oleh masing-masing aktor sesuai dengan hak akses yang telah didefinisikan.

Aktor Warga berinteraksi dengan *use case* registrasi akun, login, kelengkapan profil, pengajuan pelayanan, pengunggahan dokumen, pelacakan status, perbaikan pengajuan, dan pengunduhan surat. Aktor Admin Desa berinteraksi dengan *use case* login, dashboard statistik, verifikasi pengajuan, manajemen data penduduk, manajemen layanan, manajemen akun petugas, pengaturan desa, dan pencetakan surat. Aktor Kepala Desa berinteraksi dengan *use case* login, dashboard antrean persetujuan, dan persetujuan atau penolakan pengajuan. Terdapat pula *use case* publik yang dapat diakses tanpa autentikasi, yaitu katalog layanan, pelacakan pengajuan, dan verifikasi keaslian dokumen.

![Use Case Diagram SIADESA](gambar/use-case-diagram.png)

## Activity Diagram

*Activity diagram* menggambarkan alur aktivitas utama dalam proses pengajuan pelayanan administrasi pada SIADESA. Proses ini melibatkan tiga *swimlane* yang merepresentasikan aktivitas masing-masing aktor: Warga, Admin Desa, dan Kepala Desa.

Alur dimulai ketika Warga mengajukan pelayanan dengan mengisi formulir dan mengunggah dokumen persyaratan. Pengajuan yang berhasil dibuat memperoleh status `DIAJUKAN` dan nomor tiket otomatis. Admin Desa kemudian memverifikasi kelengkapan dan keabsahan dokumen. Pada tahap verifikasi, terdapat tiga kemungkinan keputusan:

1. **Disetujui (*approve*)** --- pengajuan diteruskan ke Kepala Desa dengan status berubah menjadi `MENUNGGU_PERSETUJUAN`.
2. **Perlu perbaikan (*revise*)** --- pengajuan dikembalikan ke Warga dengan status `PERLU_PERBAIKAN`. Warga dapat memperbaiki dan mengajukan ulang, sehingga status kembali ke `DIAJUKAN` untuk diverifikasi ulang.
3. **Ditolak (*reject*)** --- pengajuan ditolak dengan alasan yang dicatat, status berubah menjadi `DITOLAK`.

Pengajuan yang telah mencapai Kepala Desa dapat disetujui atau ditolak. Jika disetujui, sistem secara otomatis membangkitkan nomor surat resmi dan kode QR validasi, serta mengubah status menjadi `SELESAI`. Jika ditolak, status berubah menjadi `DITOLAK` disertai catatan alasan penolakan. Selain itu, Warga dapat membatalkan pengajuan kapan saja sebelum status `SELESAI`, yang mengubah status menjadi `DIBATALKAN`.

Mesin status (*state machine*) pengajuan secara lengkap dapat diringkas sebagai berikut:

`DIAJUKAN` → *Admin verifikasi* → `MENUNGGU_PERSETUJUAN` → *Kades menyetujui* → `SELESAI`

Dengan cabang alternatif:

- `DIAJUKAN` → *Admin meminta perbaikan* → `PERLU_PERBAIKAN` → *Warga mengajukan ulang* → `DIAJUKAN`
- `DIAJUKAN` → *Admin menolak* → `DITOLAK`
- `MENUNGGU_PERSETUJUAN` → *Kades menolak* → `DITOLAK`
- *Warga membatalkan* → `DIBATALKAN`

![Activity Diagram Pengajuan Pelayanan](gambar/activity-diagram.png)

## Context Diagram

*Context diagram* menggambarkan SIADESA sebagai satu proses sentral yang berinteraksi dengan entitas-entitas eksternal melalui aliran data (*data flow*) [@dennis2015systems]. Diagram ini menyajikan batasan sistem secara jelas dengan mengidentifikasi masukan dan keluaran antara sistem dan lingkungannya.

Terdapat tiga entitas eksternal yang berinteraksi dengan SIADESA:

1. **Warga** --- mengirimkan data registrasi, kredensial login, data profil kependudukan, formulir pengajuan, dan dokumen persyaratan ke sistem. Dari sistem, Warga menerima konfirmasi registrasi, token autentikasi, nomor tiket pengajuan, informasi status pengajuan, dan dokumen surat resmi.

2. **Admin Desa** --- mengirimkan kredensial login, keputusan verifikasi (beserta catatan), data penduduk (manual dan impor Excel), konfigurasi layanan, konfigurasi akun petugas, dan pengaturan *website* desa. Dari sistem, Admin Desa menerima dashboard statistik, daftar pengajuan, data penduduk, daftar layanan, dan *template* surat cetak.

3. **Kepala Desa** --- mengirimkan kredensial login dan keputusan persetujuan atau penolakan beserta catatan. Dari sistem, Kepala Desa menerima daftar antrean pengajuan yang menunggu persetujuan, detail pengajuan dan dokumen pendukung, serta konfirmasi hasil keputusan.

![Context Diagram SIADESA](gambar/context-diagram.png)

## DFD Level 1

*Data Flow Diagram* (DFD) Level 1 mendekomposisi proses tunggal pada *context diagram* menjadi lima proses utama yang menggambarkan subsistem fungsional SIADESA [@dennis2015systems]. Dekomposisi ini memberikan gambaran lebih rinci mengenai aliran data antar-proses dan penyimpanan data yang digunakan.

Lima proses utama pada DFD Level 1 adalah:

1. **Proses 1: Autentikasi dan Otorisasi** --- Menangani registrasi akun warga, login pengguna (NIK/email + kata sandi), verifikasi token JWT, dan pengelolaan sesi. Proses ini berinteraksi dengan penyimpanan data D1 (Database Pengguna) dan D2 (Database Penduduk) untuk memvalidasi NIK dan menautkan akun dengan data kependudukan.

2. **Proses 2: Pengajuan Pelayanan** --- Menangani pembuatan pengajuan baru oleh Warga, pemberian nomor tiket otomatis, penyimpanan dokumen persyaratan, pencatatan riwayat status, dan perbaikan pengajuan. Proses ini berinteraksi dengan D1, D2, D3 (Database Layanan), D4 (Database Pengajuan), dan D5 (Penyimpanan Dokumen).

3. **Proses 3: Verifikasi Dokumen** --- Menangani pemeriksaan kelengkapan dan keabsahan dokumen pengajuan oleh Admin Desa. Proses ini membaca data dari D4 dan D5, serta memperbarui status pengajuan (disetujui, perlu perbaikan, atau ditolak) pada D4.

4. **Proses 4: Persetujuan Kepala Desa** --- Menangani proses *approval* akhir oleh Kepala Desa terhadap pengajuan yang telah lolos verifikasi. Proses ini membangkitkan nomor surat resmi dan kode QR validasi, kemudian menyimpan hasilnya pada D4.

5. **Proses 5: Manajemen Data Master** --- Menangani pengelolaan data penduduk (CRUD dan impor massal), konfigurasi jenis layanan dan persyaratan, manajemen akun petugas, dan pengaturan *website* desa. Proses ini berinteraksi dengan D1, D2, dan D3.

Penyimpanan data (*data store*) yang diidentifikasi pada DFD Level 1:

| Data Store | Nama | Deskripsi |
|---|---|---|
| D1 | Database Pengguna | Menyimpan data akun pengguna (User), termasuk kredensial dan peran |
| D2 | Database Penduduk | Menyimpan data kependudukan (Resident) warga desa |
| D3 | Database Layanan | Menyimpan konfigurasi jenis layanan (Service) dan persyaratan (ServiceRequirement) |
| D4 | Database Pengajuan | Menyimpan data pengajuan (Application) dan riwayat status (StatusHistory) |
| D5 | Penyimpanan Dokumen | Menyimpan berkas dokumen persyaratan (ApplicationDocument) pada *private storage* |

![DFD Level 1 SIADESA](gambar/dfd-level-1.png)

## DFD Level 2

DFD Level 2 mendekomposisi proses-proses utama pada DFD Level 1 menjadi sub-proses yang lebih rinci. Pada bagian ini disajikan dekomposisi untuk Proses 2 (Pengajuan Pelayanan) dan Proses 3 (Verifikasi Dokumen) sebagai dua proses yang memiliki kompleksitas alur data tertinggi.

### Dekomposisi Proses 2: Pengajuan Pelayanan

Proses 2 didekomposisi menjadi empat sub-proses:

1. **Proses 2.1: Validasi Data Pemohon** --- Memeriksa kelengkapan profil kependudukan pemohon sesuai *business rule* BR-02. Sub-proses ini membaca data *Resident* dari D2 melalui relasi `user_id` dan memvalidasi bahwa *field* wajib (nomor KK, tempat/tanggal lahir, alamat, RT/RW) telah terisi. Jika profil belum lengkap, pengajuan ditolak dengan pesan kesalahan yang mengarahkan Warga untuk melengkapi biodata terlebih dahulu.

2. **Proses 2.2: Penyimpanan Dokumen Persyaratan** --- Memproses berkas dokumen yang diunggah oleh Warga melalui *middleware* Multer. Sub-proses ini memvalidasi tipe MIME berkas (*whitelist*: PDF, JPG, PNG), memeriksa ukuran berkas (maksimum 2 MB), mengganti nama berkas dengan UUID unik, dan menyimpan berkas pada direktori *private storage* (D5). Metadata berkas (nama asli, *path*, tipe MIME, ukuran) dicatat pada tabel `ApplicationDocument` di D4.

3. **Proses 2.3: Pemberian Nomor Tiket** --- Membangkitkan nomor tiket unik dengan format `ADM-YYYYMMDD-XXXXXX` berdasarkan tanggal pengajuan dan enam digit acak. Nomor tiket disimpan pada *field* `tracking_number` di D4 dan dikembalikan kepada Warga sebagai referensi pelacakan.

4. **Proses 2.4: Pencatatan Status Pengajuan** --- Membuat rekaman (*record*) pengajuan baru pada tabel `Application` di D4 dengan status awal `DIAJUKAN`, serta mencatat entri pertama pada tabel `StatusHistory` sebagai jejak audit.

### Dekomposisi Proses 3: Verifikasi Dokumen

Proses 3 didekomposisi menjadi empat sub-proses:

1. **Proses 3.1: Pemeriksaan Kelengkapan Dokumen** --- Admin Desa memeriksa berkas dokumen persyaratan yang diunggah oleh Warga. Sub-proses ini membaca data dokumen dari D5 melalui relasi `application_id` dan menampilkan daftar berkas beserta informasi metadata-nya.

2. **Proses 3.2: Validasi Data Pemohon** --- Admin Desa memeriksa kesesuaian data kependudukan pemohon (dari D2) dengan dokumen yang diunggah. Pemeriksaan ini mencakup verifikasi kesesuaian nama, NIK, alamat, dan data demografis lainnya.

3. **Proses 3.3: Penentuan Status Verifikasi** --- Admin Desa menentukan keputusan verifikasi berdasarkan hasil pemeriksaan: `APPROVE` (diteruskan ke Kepala Desa dengan status `MENUNGGU_PERSETUJUAN`), `REVISE` (dikembalikan ke Warga dengan status `PERLU_PERBAIKAN` beserta catatan perbaikan), atau `REJECT` (ditolak dengan status `DITOLAK` beserta alasan penolakan).

4. **Proses 3.4: Pencatatan Riwayat Status** --- Setiap perubahan status dicatat pada tabel `StatusHistory` di D4, meliputi status baru, nama aktor yang melakukan perubahan, catatan keputusan, dan *timestamp* pencatatan. Riwayat ini berfungsi sebagai jejak audit yang dapat ditelusuri.

![DFD Level 2 SIADESA](gambar/dfd-level-2.png)

## Sequence Diagram

*Sequence diagram* menggambarkan interaksi antar-komponen sistem secara kronologis untuk skenario pengajuan pelayanan administrasi oleh Warga. Diagram ini memodelkan arsitektur *decoupled* di mana *frontend* (Laravel + Alpine.js) berkomunikasi dengan *backend* API (Express.js) melalui permintaan HTTP.

Alur interaksi dimulai dari *Browser* yang mengakses halaman pengajuan pelayanan. Laravel menyajikan *view* Blade yang berisi komponen Alpine.js. Ketika Warga mengisi formulir dan mengunggah dokumen, Alpine.js mengirimkan permintaan HTTP `POST` melalui `fetch()` API ke *endpoint* `/api/applications` pada server Express.js.

Permintaan diterima oleh Express.js dan melewati serangkaian *middleware*: Helmet (keamanan), CORS (validasi *origin*), dan Multer (*parsing multipart/form-data* untuk pengunggahan berkas). Selanjutnya, *middleware* autentikasi (`authenticate`) mengekstrak dan memverifikasi token JWT dari *header* `Authorization`. Token yang valid menghasilkan *query* ke basis data PostgreSQL melalui Prisma ORM untuk mengambil data pengguna beserta relasi `Resident` dan `Village`.

Setelah autentikasi berhasil, *middleware* `requireRole('WARGA')` memvalidasi bahwa pengguna memiliki peran yang diizinkan. Permintaan kemudian diteruskan ke `application.controller.js` yang menjalankan logika bisnis: validasi kelengkapan profil (BR-02), validasi masukan, pembangkitan nomor tiket, penyimpanan pengajuan dan dokumen ke basis data melalui Prisma ORM, serta pencatatan riwayat status.

Respons JSON dikembalikan melalui rantai yang sama: *Controller* → Express.js → *Browser*. Alpine.js pada sisi klien memproses respons dan memperbarui antarmuka pengguna secara reaktif.

![Sequence Diagram Pengajuan Pelayanan](gambar/sequence-diagram.png)

## Class Diagram

*Class diagram* memodelkan struktur data sistem berdasarkan skema Prisma ORM yang mendefinisikan sembilan model data beserta relasi antar-model [@connolly2015database]. Setiap model merepresentasikan sebuah tabel pada basis data PostgreSQL dengan atribut-atribut dan tipe data yang terdefinisi secara eksplisit.

Berikut adalah deskripsi setiap model beserta atribut dan relasinya:

1. **Village** --- Merepresentasikan entitas desa/kelurahan. Atribut meliputi `code` (kode desa, unik), `name`, `district` (kecamatan), `regency` (kabupaten), `province` (provinsi, *default*: Jawa Barat), `address`, `phone`, `email`, `official_head` (nama kepala desa), `official_nip` (NIP kepala desa), dan `service_hours` (jam pelayanan). Model ini memiliki relasi *one-to-many* dengan User, Resident, Service, Application, dan Setting.

2. **User** --- Merepresentasikan akun pengguna sistem. Atribut meliputi `nik` (unik), `name`, `email` (opsional, unik), `phone`, `password_hash`, `role` (enumerasi: `WARGA`, `ADMIN`, `KADES`), dan `is_active`. Model ini memiliki relasi *many-to-one* dengan Village, relasi *one-to-one* opsional dengan Resident, dan relasi *one-to-many* dengan Application.

3. **Resident** --- Merepresentasikan data kependudukan warga. Atribut meliputi `nik` (unik), `family_card_no`, `full_name`, `birth_place`, `birth_date`, `gender`, `address`, `rt`, `rw`, `religion`, `marital_status`, `occupation`, `phone_number`, dan `is_verified`. Model ini memiliki relasi *many-to-one* dengan Village, relasi *one-to-one* opsional dengan User, dan relasi *one-to-many* dengan Application.

4. **Service** --- Merepresentasikan jenis layanan administrasi. Atribut meliputi `code` (unik per desa), `name`, `category`, `description`, `estimation_days`, `requires_approval`, dan `is_active`. Model ini memiliki relasi *many-to-one* dengan Village, relasi *one-to-many* dengan ServiceRequirement, dan relasi *one-to-many* dengan Application.

5. **ServiceRequirement** --- Merepresentasikan persyaratan dokumen untuk suatu layanan. Atribut meliputi `name` dan `is_mandatory`. Model ini memiliki relasi *many-to-one* dengan Service.

6. **Application** --- Merepresentasikan pengajuan pelayanan. Atribut meliputi `tracking_number` (unik), `purpose`, `business_name` (opsional), `business_type` (opsional), `business_address` (opsional), `status` (enumerasi `AppStatus`), `verifier_notes`, `approval_notes`, `letter_number`, dan `qr_uuid` (unik). Model ini memiliki relasi *many-to-one* dengan Village, User, Resident, dan Service, serta relasi *one-to-many* dengan ApplicationDocument dan StatusHistory.

7. **ApplicationDocument** --- Merepresentasikan berkas dokumen yang diunggah. Atribut meliputi `file_name`, `file_path`, `mime_type`, dan `file_size`. Model ini memiliki relasi *many-to-one* dengan Application.

8. **StatusHistory** --- Merepresentasikan riwayat perubahan status pengajuan. Atribut meliputi `status` (enumerasi `AppStatus`), `actor_name`, `notes`, dan `created_at`. Model ini memiliki relasi *many-to-one* dengan Application.

9. **Setting** --- Merepresentasikan pengaturan konfigurasi *website* desa. Atribut meliputi `key` dan `value`, dengan *constraint* unik pada kombinasi `village_id` dan `key`. Model ini memiliki relasi *many-to-one* dengan Village.

Sistem mendefinisikan dua enumerasi:

- **Role**: `WARGA`, `ADMIN`, `KADES`
- **AppStatus**: `DRAFT`, `DIAJUKAN`, `PERLU_PERBAIKAN`, `MENUNGGU_PERSETUJUAN`, `DISETUJUI`, `DIPROSES`, `SELESAI`, `DITOLAK`, `DIBATALKAN`

![Class Diagram SIADESA](gambar/class-diagram.png)

## Arsitektur Sistem

SIADESA dikembangkan menggunakan arsitektur *decoupled* yang memisahkan lapisan presentasi (*frontend*) dari lapisan logika bisnis dan akses data (*backend* API) [@fowler2003patterns]. Pendekatan ini dipilih untuk meningkatkan modularitas, kemudahan pengujian, dan fleksibilitas pengembangan secara independen pada masing-masing lapisan.

Komponen-komponen arsitektur sistem terdiri atas:

**Lapisan Frontend:**

- **Laravel 12.x** [@laravel2024docs] --- berfungsi sebagai *view engine* yang menyajikan halaman web melalui Blade *template engine*. Laravel tidak menangani logika bisnis; seluruh data diperoleh dari *backend* API melalui permintaan HTTP.
- **Alpine.js 3.x** [@alpinejs2024docs] --- *framework* reaktivitas JavaScript ringan yang menangani interaksi *client-side*, formulir dinamis, dan komunikasi asinkron dengan API menggunakan `fetch()`.
- **Tailwind CSS 4.x** [@tailwindcss2024docs] --- *utility-first CSS framework* yang digunakan untuk membangun antarmuka responsif dan konsisten.
- **Vite 7.x** [@vite2024docs] --- *build tool* dan *development server* yang menangani *bundling* aset (JavaScript, CSS) dengan *Hot Module Replacement* (HMR) untuk pengembangan yang efisien.

**Lapisan Backend API:**

- **Express.js 4.x** [@expressjs2024docs] --- *framework* Node.js minimalis yang menyediakan RESTful API [@fielding2000rest]. Seluruh *endpoint* dikelompokkan dalam lima modul *routing*: `auth`, `applications`, `admin`, `kades`, dan `public`.
- **Prisma ORM 6.x** [@prisma2024docs] --- *Object-Relational Mapping* yang menyediakan *type-safe database access* dan migrasi skema basis data secara deklaratif.
- **Node.js 22.x** [@nodejs2024docs] --- *runtime* JavaScript yang menjalankan server Express.js dengan dukungan ES Modules (`"type": "module"`).

**Lapisan Basis Data:**

- **PostgreSQL** [@postgresql2024docs] --- sistem manajemen basis data relasional yang digunakan untuk menyimpan seluruh data persisten sistem.

**Infrastruktur Deployment:**

- **Nginx** [@nginx2024docs] --- *reverse proxy* yang mengarahkan permintaan ke Laravel (*frontend*) dan Express.js (*backend* API) berdasarkan subdomain.
- **PM2** [@pm2docs2024] --- *process manager* untuk Node.js yang menjamin ketersediaan proses Express.js melalui *auto-restart* dan *cluster mode*.
- **Let's Encrypt** --- penyedia sertifikat SSL/TLS gratis untuk enkripsi komunikasi HTTPS.

Arsitektur *deployment* menggunakan dua subdomain: `sidesa.geezplay.site` untuk *frontend* Laravel dan `api.sidesa.geezplay.site` untuk *backend* API Express.js. Nginx berperan sebagai *reverse proxy* yang meneruskan permintaan ke masing-masing layanan berdasarkan konfigurasi *server block*.

![Arsitektur Sistem SIADESA](gambar/arsitektur-sistem.png)

## Entity Relationship Diagram (ERD)

*Entity Relationship Diagram* (ERD) menggambarkan struktur basis data relasional SIADESA beserta hubungan antar-entitas [@connolly2015database]. Diagram ini memodelkan sembilan entitas yang saling terhubung melalui *foreign key* dengan kardinalitas yang terdefinisi secara eksplisit pada skema Prisma.

Hubungan antar-entitas beserta kardinalitasnya adalah sebagai berikut:

- **Village → User** (*one-to-many*): Satu desa memiliki banyak pengguna. Penghapusan desa secara *cascade* menghapus seluruh pengguna terkait.
- **Village → Resident** (*one-to-many*): Satu desa memiliki banyak data penduduk. Penghapusan bersifat *cascade*.
- **Village → Service** (*one-to-many*): Satu desa memiliki banyak jenis layanan. Penghapusan bersifat *cascade*.
- **Village → Application** (*one-to-many*): Satu desa memiliki banyak pengajuan. Penghapusan bersifat *cascade*.
- **Village → Setting** (*one-to-many*): Satu desa memiliki banyak pengaturan konfigurasi. Penghapusan bersifat *cascade*.
- **User → Village** (*many-to-one*): Setiap pengguna terdaftar pada satu desa.
- **User → Resident** (*one-to-one*, opsional): Satu pengguna dapat dikaitkan dengan satu data kependudukan. Relasi ini opsional karena akun Admin dan Kades tidak wajib memiliki data kependudukan. Penghapusan pengguna mengatur `user_id` pada Resident menjadi `null` (*SetNull*).
- **User → Application** (*one-to-many*): Satu pengguna dapat memiliki banyak pengajuan. Penghapusan bersifat *cascade*.
- **Service → ServiceRequirement** (*one-to-many*): Satu layanan memiliki banyak persyaratan dokumen. Penghapusan bersifat *cascade*.
- **Service → Application** (*one-to-many*): Satu layanan dapat diajukan oleh banyak pengajuan. Penghapusan bersifat *cascade*.
- **Resident → Application** (*one-to-many*): Satu data penduduk dapat terkait dengan banyak pengajuan. Penghapusan penduduk mengatur `resident_id` menjadi `null` (*SetNull*).
- **Application → ApplicationDocument** (*one-to-many*): Satu pengajuan memiliki banyak dokumen. Penghapusan bersifat *cascade*.
- **Application → StatusHistory** (*one-to-many*): Satu pengajuan memiliki banyak catatan riwayat status. Penghapusan bersifat *cascade*.

![Entity Relationship Diagram SIADESA](gambar/erd.png)

## Perancangan Database

Perancangan basis data SIADESA disusun berdasarkan skema Prisma yang mendefinisikan struktur tabel, tipe data, *constraint*, dan relasi antar-tabel secara deklaratif. Berikut adalah spesifikasi rinci setiap tabel beserta definisi enumerasi yang digunakan.

### Tabel Village

Tabel `Village` menyimpan data profil desa/kelurahan yang menjadi entitas induk bagi seluruh data dalam sistem.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| code | String | Kode desa, *Unique* |
| name | String | Nama desa |
| district | String | Nama kecamatan |
| regency | String | Nama kabupaten |
| province | String | Nama provinsi, *Default*: "Jawa Barat" |
| address | String | Alamat kantor desa |
| phone | String | Nomor telepon kantor desa |
| email | String | Alamat email kantor desa |
| official_head | String | Nama kepala desa |
| official_nip | String | NIP kepala desa |
| service_hours | String | Jam pelayanan, *Default*: "Senin - Jumat, 08.00 - 14.00 WIB" |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Tabel User

Tabel `User` menyimpan data akun pengguna sistem, mencakup warga, admin desa, dan kepala desa.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| village_id | String (UUID) | *Foreign Key* ke Village, *Cascade Delete* |
| nik | String | Nomor Induk Kependudukan, *Unique* |
| name | String | Nama pengguna |
| email | String (Nullable) | Alamat email, *Unique* (opsional) |
| phone | String | Nomor telepon |
| password_hash | String | *Hash* kata sandi (bcrypt) |
| role | Enum Role | Peran pengguna, *Default*: `WARGA` |
| is_active | Boolean | Status keaktifan akun, *Default*: `true` |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Tabel Resident

Tabel `Resident` menyimpan data kependudukan warga desa yang menjadi dasar validasi pengajuan pelayanan.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| village_id | String (UUID) | *Foreign Key* ke Village, *Cascade Delete* |
| user_id | String (UUID, Nullable) | *Foreign Key* ke User, *Unique*, *SetNull on Delete* |
| nik | String | Nomor Induk Kependudukan, *Unique* |
| family_card_no | String | Nomor Kartu Keluarga |
| full_name | String | Nama lengkap sesuai KTP |
| birth_place | String | Tempat lahir |
| birth_date | DateTime | Tanggal lahir |
| gender | String | Jenis kelamin |
| address | String | Alamat tempat tinggal |
| rt | String | Nomor Rukun Tetangga |
| rw | String | Nomor Rukun Warga |
| religion | String | Agama, *Default*: "Islam" |
| marital_status | String | Status perkawinan, *Default*: "Kawin" |
| occupation | String | Pekerjaan, *Default*: "Wiraswasta" |
| phone_number | String | Nomor telepon |
| is_verified | Boolean | Status verifikasi data, *Default*: `false` |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Tabel Service

Tabel `Service` menyimpan konfigurasi jenis layanan administrasi yang tersedia di desa.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| village_id | String (UUID) | *Foreign Key* ke Village, *Cascade Delete* |
| code | String | Kode layanan, *Unique* per `village_id` |
| name | String | Nama layanan |
| category | String | Kategori layanan |
| description | String | Deskripsi layanan |
| estimation_days | Int | Estimasi waktu penyelesaian (hari), *Default*: `1` |
| requires_approval | Boolean | Wajib persetujuan Kepala Desa, *Default*: `true` |
| is_active | Boolean | Status keaktifan layanan, *Default*: `true` |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Tabel ServiceRequirement

Tabel `ServiceRequirement` menyimpan daftar persyaratan dokumen yang harus dilengkapi untuk mengajukan suatu layanan.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| service_id | String (UUID) | *Foreign Key* ke Service, *Cascade Delete* |
| name | String | Nama persyaratan dokumen |
| is_mandatory | Boolean | Status wajib persyaratan, *Default*: `true` |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |

### Tabel Application

Tabel `Application` menyimpan data pengajuan pelayanan administrasi yang diajukan oleh warga.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| village_id | String (UUID) | *Foreign Key* ke Village, *Cascade Delete* |
| user_id | String (UUID) | *Foreign Key* ke User, *Cascade Delete* |
| resident_id | String (UUID, Nullable) | *Foreign Key* ke Resident, *SetNull on Delete* |
| service_id | String (UUID) | *Foreign Key* ke Service, *Cascade Delete* |
| tracking_number | String | Nomor tiket pengajuan, *Unique* |
| purpose | String | Keperluan pengajuan surat |
| business_name | String (Nullable) | Nama usaha (untuk Surat Keterangan Usaha) |
| business_type | String (Nullable) | Jenis usaha |
| business_address | String (Nullable) | Alamat usaha |
| status | Enum AppStatus | Status pengajuan, *Default*: `DIAJUKAN` |
| verifier_notes | String (Nullable) | Catatan dari Admin Desa (verifikator) |
| approval_notes | String (Nullable) | Catatan dari Kepala Desa |
| letter_number | String (Nullable) | Nomor surat resmi (terisi setelah disetujui) |
| qr_uuid | String (Nullable) | Kode QR validasi dokumen, *Unique* |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Tabel ApplicationDocument

Tabel `ApplicationDocument` menyimpan metadata berkas dokumen persyaratan yang diunggah oleh warga.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| application_id | String (UUID) | *Foreign Key* ke Application, *Cascade Delete* |
| file_name | String | Nama asli berkas yang diunggah |
| file_path | String | *Path* penyimpanan berkas pada *server* |
| mime_type | String | Tipe MIME berkas (PDF, JPG, PNG) |
| file_size | Int | Ukuran berkas dalam *byte* |
| created_at | DateTime | Waktu pengunggahan, *Default*: `now()` |

### Tabel StatusHistory

Tabel `StatusHistory` menyimpan jejak audit perubahan status pengajuan untuk keperluan penelusuran dan transparansi proses.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| application_id | String (UUID) | *Foreign Key* ke Application, *Cascade Delete* |
| status | Enum AppStatus | Status yang dicatat |
| actor_name | String | Nama aktor yang melakukan perubahan |
| notes | String (Nullable) | Catatan atau keterangan perubahan |
| created_at | DateTime | Waktu pencatatan, *Default*: `now()` |

### Tabel Setting

Tabel `Setting` menyimpan pengaturan konfigurasi dinamis *website* desa dalam format *key-value pair*.

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | String (UUID) | *Primary Key*, di-*generate* otomatis |
| village_id | String (UUID) | *Foreign Key* ke Village, *Cascade Delete* |
| key | String | Kunci pengaturan, *Unique* per `village_id` |
| value | String | Nilai pengaturan |
| created_at | DateTime | Waktu pembuatan, *Default*: `now()` |
| updated_at | DateTime | Waktu pembaruan, otomatis diperbarui |

### Definisi Enumerasi

Sistem menggunakan dua enumerasi yang didefinisikan pada tingkat basis data PostgreSQL melalui Prisma:

**Enum Role:**

| Nilai | Deskripsi |
|---|---|
| `WARGA` | Peran untuk akun masyarakat/warga desa |
| `ADMIN` | Peran untuk akun petugas administrasi desa |
| `KADES` | Peran untuk akun kepala desa |

**Enum AppStatus:**

| Nilai | Deskripsi |
|---|---|
| `DRAFT` | Pengajuan disimpan sebagai draf (belum dikirim) |
| `DIAJUKAN` | Pengajuan telah dikirim dan menunggu verifikasi Admin Desa |
| `PERLU_PERBAIKAN` | Pengajuan dikembalikan oleh Admin Desa untuk perbaikan dokumen |
| `MENUNGGU_PERSETUJUAN` | Pengajuan telah lolos verifikasi dan menunggu keputusan Kepala Desa |
| `DISETUJUI` | Pengajuan telah disetujui oleh Kepala Desa |
| `DIPROSES` | Pengajuan sedang dalam proses penerbitan dokumen |
| `SELESAI` | Pengajuan telah selesai dan surat resmi telah diterbitkan |
| `DITOLAK` | Pengajuan ditolak oleh Admin Desa atau Kepala Desa |
| `DIBATALKAN` | Pengajuan dibatalkan oleh warga pemohon |
