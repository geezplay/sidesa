# PENGUJIAN DAN EVALUASI

## Metode Pengujian

Pengujian sistem SIADESA dilakukan menggunakan metode *Black Box Testing*, yaitu teknik pengujian perangkat lunak yang memverifikasi fungsionalitas sistem tanpa memperhatikan struktur kode internal. Pengujian difokuskan pada kesesuaian antara masukan (*input*) yang diberikan dengan keluaran (*output*) yang dihasilkan oleh sistem [@pressman2014software].

Pendekatan pengujian dilakukan secara modular berdasarkan kelompok fitur utama, yaitu modul autentikasi, modul pengajuan, modul verifikasi, modul persetujuan, dan modul manajemen data. Selain itu, pengujian juga dilakukan berdasarkan peran pengguna (*role-based*), meliputi peran Warga, Admin Desa, dan Kepala Desa, guna memastikan bahwa setiap aktor hanya dapat mengakses fungsionalitas yang sesuai dengan hak aksesnya.

Setiap skenario pengujian didokumentasikan dalam bentuk tabel yang memuat identitas kasus uji, skenario pengujian, langkah-langkah pelaksanaan, hasil yang diharapkan, hasil aktual, dan status keberhasilan.

## Pengujian Fungsional Modul Autentikasi

Pengujian modul autentikasi mencakup proses registrasi, login, pembatasan akses berbasis *rate limiting*, dan proteksi *endpoint* API menggunakan token JWT.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-01 | Registrasi warga dengan NIK valid | Input NIK 16 digit yang terdaftar di data penduduk, nama, nomor HP, password | Akun berhasil dibuat, token JWT diterima | Sesuai | Berhasil |
| TC-02 | Registrasi warga dengan NIK tidak terdaftar | Input NIK yang tidak ada di database penduduk | Sistem menampilkan pesan error "NIK tidak ditemukan" | Sesuai | Berhasil |
| TC-03 | Login dengan kredensial valid | Input NIK/email dan password yang benar | Login berhasil, diarahkan ke dashboard sesuai role | Sesuai | Berhasil |
| TC-04 | Login dengan password salah | Input NIK valid dan password salah | Sistem menampilkan pesan error autentikasi | Sesuai | Berhasil |
| TC-05 | Login melebihi batas rate limiting | Login gagal lebih dari 20 kali dalam 1 menit | Sistem memblokir akses sementara | Sesuai | Berhasil |
| TC-06 | Akses halaman terproteksi tanpa token | Mengakses endpoint API tanpa header Authorization | Sistem mengembalikan status 401 Unauthorized | Sesuai | Berhasil |

## Pengujian Fungsional Modul Pengajuan

Pengujian modul pengajuan mencakup proses pengajuan layanan oleh warga, validasi kelengkapan profil, unggah dokumen persyaratan, serta pelacakan status pengajuan melalui nomor tiket.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-07 | Pengajuan dengan profil lengkap | Warga dengan profil lengkap memilih layanan, isi form, upload dokumen | Pengajuan berhasil, nomor tiket ADM-XXXXXX diberikan | Sesuai | Berhasil |
| TC-08 | Pengajuan dengan profil tidak lengkap | Warga dengan profil belum lengkap mencoba mengajukan | Sistem menolak pengajuan, menampilkan pesan untuk melengkapi profil | Sesuai | Berhasil |
| TC-09 | Upload dokumen melebihi batas ukuran | Upload file > 2MB | Sistem menolak upload dengan pesan error | Sesuai | Berhasil |
| TC-10 | Upload dokumen format tidak didukung | Upload file .exe atau .doc | Sistem menolak upload, hanya menerima PDF/JPG/PNG | Sesuai | Berhasil |
| TC-11 | Pelacakan pengajuan dengan nomor tiket valid | Input nomor tiket ADM-XXXXXX di halaman tracking | Sistem menampilkan status dan timeline pengajuan | Sesuai | Berhasil |
| TC-12 | Pelacakan dengan nomor tiket tidak valid | Input nomor tiket yang tidak ada | Sistem menampilkan pesan "Pengajuan tidak ditemukan" | Sesuai | Berhasil |

## Pengujian Fungsional Modul Verifikasi

Pengujian modul verifikasi mencakup proses pemeriksaan dokumen pengajuan oleh Admin Desa, termasuk keputusan menerima, meminta perbaikan, atau menolak pengajuan, serta kemampuan warga dalam memperbaiki dokumen yang dikembalikan.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-13 | Verifikasi pengajuan --- terima | Admin memeriksa dokumen dan memilih "Terima" | Status berubah menjadi MENUNGGU_PERSETUJUAN, diteruskan ke Kades | Sesuai | Berhasil |
| TC-14 | Verifikasi pengajuan --- revisi | Admin memilih "Perlu Perbaikan" dengan catatan | Status berubah menjadi PERLU_PERBAIKAN, warga dapat memperbaiki | Sesuai | Berhasil |
| TC-15 | Verifikasi pengajuan --- tolak | Admin memilih "Tolak" dengan alasan | Status berubah menjadi DITOLAK | Sesuai | Berhasil |
| TC-16 | Perbaikan pengajuan oleh warga | Warga memperbaiki dokumen pada pengajuan berstatus PERLU_PERBAIKAN | Status berubah kembali menjadi DIAJUKAN | Sesuai | Berhasil |

## Pengujian Fungsional Modul Persetujuan

Pengujian modul persetujuan mencakup proses *approval* oleh Kepala Desa terhadap pengajuan yang telah lolos verifikasi, serta validasi dokumen melalui QR Code pada halaman publik.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-17 | Persetujuan kepala desa --- setuju | Kades menyetujui pengajuan yang telah diverifikasi | Status SELESAI, nomor surat dan QR UUID tergenerate | Sesuai | Berhasil |
| TC-18 | Persetujuan kepala desa --- tolak | Kades menolak pengajuan dengan alasan | Status DITOLAK | Sesuai | Berhasil |
| TC-19 | Verifikasi dokumen via QR Code | Scan QR Code pada halaman publik /verifikasi-surat/ | Informasi dokumen ditampilkan dengan nama ter-mask (privasi) | Sesuai | Berhasil |

## Pengujian Fungsional Modul Manajemen Data

Pengujian modul manajemen data mencakup pengelolaan data penduduk, konfigurasi layanan, serta manajemen akun petugas oleh Admin Desa.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-20 | Tambah data penduduk | Admin mengisi form data penduduk dan menyimpan | Data penduduk berhasil tersimpan | Sesuai | Berhasil |
| TC-21 | Import data penduduk dari Excel | Admin upload file Excel berisi data penduduk | Data berhasil diimpor ke database | Sesuai | Berhasil |
| TC-22 | Tambah jenis layanan baru | Admin mengisi form layanan dengan persyaratan | Layanan baru tersedia di katalog | Sesuai | Berhasil |
| TC-23 | Kelola akun staff | Admin membuat akun ADMIN atau KADES baru | Akun berhasil dibuat sesuai role | Sesuai | Berhasil |
| TC-24 | Cegah hapus akun admin terakhir | Admin mencoba menghapus satu-satunya akun admin | Sistem menolak penghapusan | Sesuai | Berhasil |

## Pengujian Kebutuhan Non-Fungsional

Pengujian kebutuhan non-fungsional dilakukan untuk memverifikasi aspek keamanan, responsivitas, dan isolasi data pada sistem SIADESA.

| ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-25 | Keamanan password | Verifikasi password tersimpan dalam bentuk hash bcrypt | Password tidak tersimpan dalam plaintext | Sesuai | Berhasil |
| TC-26 | Keamanan header HTTP | Periksa response header menggunakan Helmet | Header keamanan (X-Content-Type-Options, X-Frame-Options, dll.) aktif | Sesuai | Berhasil |
| TC-27 | Responsivitas tampilan | Akses sistem dari perangkat mobile dan desktop | Tampilan menyesuaikan ukuran layar | Sesuai | Berhasil |
| TC-28 | Isolasi data multi-tenant | Akses API dengan user dari village_id berbeda | Data hanya menampilkan milik village yang sesuai | Sesuai | Berhasil |

## Rekapitulasi Hasil Pengujian

Berdasarkan seluruh skenario pengujian yang telah dilaksanakan, rekapitulasi hasil pengujian disajikan pada tabel berikut.

| Kategori Pengujian | Jumlah Skenario | Berhasil | Gagal | Persentase Keberhasilan |
| :--- | :---: | :---: | :---: | :---: |
| Autentikasi | 6 | 6 | 0 | 100% |
| Pengajuan | 6 | 6 | 0 | 100% |
| Verifikasi | 4 | 4 | 0 | 100% |
| Persetujuan | 3 | 3 | 0 | 100% |
| Manajemen Data | 5 | 5 | 0 | 100% |
| Non-Fungsional | 4 | 4 | 0 | 100% |
| **Total** | **28** | **28** | **0** | **100%** |

Hasil pengujian menunjukkan bahwa seluruh 28 skenario pengujian berhasil dieksekusi dengan tingkat keberhasilan 100%. Tidak ditemukan kegagalan pada skenario pengujian yang telah dirancang, baik pada pengujian fungsional maupun non-fungsional.

## Evaluasi Sistem

### Evaluasi Kebutuhan Fungsional

Evaluasi kebutuhan fungsional dilakukan dengan memetakan setiap *functional requirement* yang telah didefinisikan pada tahap analisis terhadap implementasi aktual pada sistem SIADESA.

| ID | Kebutuhan Fungsional | Deskripsi | Status |
| :--- | :--- | :--- | :---: |
| FR-01 | Registrasi Warga | Warga dapat membuat akun menggunakan NIK yang terdaftar di data penduduk | Terpenuhi |
| FR-02 | Login Multi-Role | Pengguna dapat login menggunakan NIK atau email sesuai peran masing-masing | Terpenuhi |
| FR-03 | Pengajuan Layanan | Warga dapat mengajukan permohonan layanan administrasi secara online | Terpenuhi |
| FR-04 | Upload Dokumen | Warga dapat mengunggah dokumen persyaratan dalam format PDF, JPG, atau PNG | Terpenuhi |
| FR-05 | Pelacakan Pengajuan | Warga dapat melacak status pengajuan melalui nomor tiket | Terpenuhi |
| FR-06 | Verifikasi Dokumen | Admin Desa dapat memverifikasi kelengkapan dan keabsahan dokumen pengajuan | Terpenuhi |
| FR-07 | Persetujuan Kepala Desa | Kepala Desa dapat menyetujui atau menolak pengajuan yang telah diverifikasi | Terpenuhi |
| FR-08 | Penerbitan Nomor Surat | Sistem menghasilkan nomor surat otomatis saat pengajuan disetujui | Terpenuhi |
| FR-09 | Validasi QR Code | Dokumen resmi dilengkapi QR Code untuk verifikasi keaslian pada halaman publik | Terpenuhi |
| FR-10 | Manajemen Data Penduduk | Admin dapat menambah, mengedit, dan mengimpor data penduduk dari file Excel | Terpenuhi |
| FR-11 | Konfigurasi Layanan | Admin dapat mengelola jenis layanan beserta persyaratan dan estimasi waktu | Terpenuhi |
| FR-12 | Manajemen Akun Petugas | Admin dapat membuat dan mengelola akun Admin Desa dan Kepala Desa | Terpenuhi |
| FR-13 | Dashboard Statistik | Sistem menyediakan ringkasan statistik pelayanan pada halaman dashboard | Terpenuhi |
| FR-14 | Halaman Publik | Beranda, katalog layanan, dan pelacakan pengajuan dapat diakses tanpa login | Terpenuhi |
| FR-15 | Cetak Surat | Dokumen surat resmi dapat dicetak dengan kop surat, nomor surat, dan QR Code | Terpenuhi |

Seluruh 15 kebutuhan fungsional berhasil diimplementasikan dan berfungsi sesuai spesifikasi yang telah ditetapkan.

### Evaluasi Kebutuhan Non-Fungsional

Evaluasi kebutuhan non-fungsional dilakukan untuk memastikan bahwa aspek kualitas sistem di luar fungsionalitas utama telah terpenuhi.

| ID | Kebutuhan Non-Fungsional | Deskripsi | Status |
| :--- | :--- | :--- | :---: |
| NFR-01 | Keamanan Autentikasi | Password disimpan menggunakan hash bcrypt, sesi dikelola dengan JWT | Terpenuhi |
| NFR-02 | Rate Limiting | Pembatasan jumlah permintaan API untuk mencegah penyalahgunaan | Terpenuhi |
| NFR-03 | Keamanan Header HTTP | Penggunaan Helmet untuk mengaktifkan header keamanan standar | Terpenuhi |
| NFR-04 | Responsivitas | Tampilan sistem responsif pada perangkat mobile dan desktop | Terpenuhi |
| NFR-05 | Privasi Data | Penyembunyian (*masking*) nama pada halaman verifikasi publik sesuai UU PDP | Terpenuhi |
| NFR-06 | Isolasi Multi-Tenant | Data antar desa terisolasi berdasarkan village_id | Terpenuhi |
| NFR-07 | Validasi Input | Validasi data pada sisi klien dan server untuk mencegah data tidak valid | Terpenuhi |
| NFR-08 | Ketersediaan | Sistem berjalan pada VPS dengan PM2 untuk *auto-restart* dan Nginx sebagai *reverse proxy* | Terpenuhi |

Seluruh 8 kebutuhan non-fungsional berhasil dipenuhi dalam implementasi sistem.

### Evaluasi Acceptance Criteria

Evaluasi *acceptance criteria* dilakukan terhadap kriteria penerimaan MVP Phase 1 yang telah ditetapkan pada tahap perencanaan. Kriteria tersebut meliputi:

1. **Registrasi dan login berbasis NIK** --- Warga dapat mendaftarkan akun dan masuk ke sistem menggunakan NIK yang telah terdaftar di database penduduk. Fitur ini telah terverifikasi pada skenario TC-01 hingga TC-04.

2. **Alur pengajuan end-to-end** --- Proses pengajuan layanan administrasi berjalan secara utuh mulai dari pengajuan oleh warga, verifikasi oleh Admin Desa, persetujuan oleh Kepala Desa, hingga penerbitan dokumen dengan nomor surat dan QR Code. Alur ini telah terverifikasi pada skenario TC-07 hingga TC-19.

3. **Pelacakan status pengajuan** --- Warga dapat memantau progres pengajuan secara mandiri melalui nomor tiket yang diberikan saat pengajuan. Fitur ini telah terverifikasi pada skenario TC-11 dan TC-12.

4. **Validasi dokumen via QR Code** --- Dokumen resmi yang diterbitkan dilengkapi QR Code yang dapat dipindai pada halaman publik untuk memverifikasi keaslian dokumen dengan perlindungan privasi (*name masking*). Fitur ini telah terverifikasi pada skenario TC-19.

5. **Manajemen data penduduk dan layanan** --- Admin Desa dapat mengelola data penduduk secara manual maupun melalui impor data massal, serta mengonfigurasi jenis layanan yang tersedia. Fitur ini telah terverifikasi pada skenario TC-20 hingga TC-24.

6. **Keamanan dan privasi data** --- Sistem mengimplementasikan mekanisme keamanan berlapis meliputi hash password, rate limiting, header keamanan HTTP, dan isolasi data multi-tenant. Aspek ini telah terverifikasi pada skenario TC-25 hingga TC-28.

Seluruh kriteria penerimaan MVP Phase 1 telah terpenuhi berdasarkan hasil pengujian yang dilakukan.

### Kekuatan dan Keterbatasan Sistem

Berdasarkan hasil pengujian dan evaluasi, beberapa kekuatan sistem SIADESA yang dapat diidentifikasi antara lain:

1. **Alur kerja terstruktur** --- Penggunaan *state machine* dengan status DIAJUKAN, DIVERIFIKASI, MENUNGGU_PERSETUJUAN, PERLU_PERBAIKAN, SELESAI, dan DITOLAK memberikan kejelasan tahapan proses dan mencegah transisi status yang tidak valid.

2. **Arsitektur *decoupled*** --- Pemisahan antara *view engine* (Laravel Blade) dan REST API (Express.js) memungkinkan pengembangan independen pada sisi tampilan dan sisi logika bisnis, serta membuka peluang integrasi dengan aplikasi klien lain di masa depan.

3. **Perlindungan privasi** --- Penerapan *name masking* pada halaman verifikasi publik menunjukkan kepatuhan terhadap prinsip minimalisasi data sebagaimana diamanatkan UU PDP Nomor 27 Tahun 2022.

4. **Skalabilitas multi-tenant** --- Arsitektur isolasi data berbasis village_id memungkinkan satu *instance* sistem melayani lebih dari satu desa/kelurahan tanpa risiko kebocoran data antar entitas.

Adapun beberapa keterbatasan yang teridentifikasi pada versi MVP Phase 1 meliputi:

1. **Belum tersedia notifikasi *real-time*** --- Sistem belum mengimplementasikan mekanisme notifikasi otomatis (misalnya melalui WhatsApp atau email) untuk memberitahu warga mengenai perubahan status pengajuan.

2. **Belum terintegrasi dengan tanda tangan digital** --- Proses persetujuan Kepala Desa belum menggunakan tanda tangan elektronik (*e-signature*) yang memiliki kekuatan hukum sesuai UU ITE.

3. **Pengujian performa belum dilakukan** --- Pengujian pada versi ini difokuskan pada aspek fungsional dan keamanan dasar. Pengujian beban (*load testing*) dan pengujian performa (*performance testing*) belum dilaksanakan untuk mengukur kapasitas sistem dalam menangani akses konkuren dalam jumlah besar.

4. **Belum terhubung dengan basis data kependudukan nasional** --- Validasi NIK masih dilakukan terhadap data penduduk lokal yang diimpor secara manual, belum terintegrasi dengan sistem Dukcapil (Dinas Kependudukan dan Pencatatan Sipil) untuk verifikasi otomatis.
