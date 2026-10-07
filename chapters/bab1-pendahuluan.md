# PENDAHULUAN

## Latar Belakang

Pelayanan administrasi desa/kelurahan merupakan salah satu fungsi pemerintahan yang bersentuhan langsung dengan kehidupan masyarakat sehari-hari. Masyarakat memerlukan berbagai dokumen resmi seperti Surat Keterangan Usaha, Surat Keterangan Domisili, Surat Keterangan Tidak Mampu, Surat Pengantar SKCK, hingga Surat Keterangan Kelahiran dan Kematian untuk berbagai keperluan administratif.

Pada banyak wilayah di Indonesia, proses pelayanan administrasi desa masih dilakukan secara manual. Masyarakat harus datang ke kantor desa, membawa dokumen persyaratan secara fisik, mengisi formulir di tempat, kemudian menunggu proses verifikasi dan penerbitan dokumen tanpa kepastian waktu penyelesaian. Pendekatan konvensional tersebut menimbulkan sejumlah permasalahan:

1. **Antrean pelayanan yang panjang** --- Masyarakat harus meluangkan waktu untuk datang dan menunggu di kantor desa, terutama pada jam-jam sibuk pelayanan.

2. **Proses pengajuan yang memakan waktu** --- Ketiadaan sistem terpadu menyebabkan proses verifikasi dokumen, pengecekan kelengkapan persyaratan, dan persetujuan pimpinan berlangsung tidak efisien.

3. **Kesulitan pelacakan status pengajuan** --- Masyarakat tidak memiliki mekanisme untuk mengetahui progres pengajuan secara mandiri, sehingga harus berulang kali menghubungi atau mendatangi kantor desa untuk menanyakan status permohonan.

4. **Pengelolaan arsip berbasis dokumen fisik** --- Pencatatan pelayanan dan penyimpanan arsip masih mengandalkan berkas kertas yang rentan rusak, hilang, dan sulit ditelusuri kembali.

5. **Risiko kesalahan input data** --- Pengisian data secara manual berulang pada setiap pengajuan meningkatkan potensi inkonsistensi dan kesalahan pencatatan data penduduk.

6. **Keterbatasan pelaporan** --- Pimpinan desa kesulitan memperoleh rekapitulasi dan statistik pelayanan secara cepat karena data tersebar dalam format non-digital.

Berdasarkan permasalahan tersebut, diperlukan sebuah sistem informasi berbasis web yang mampu mendigitalisasi seluruh proses pelayanan administrasi desa/kelurahan secara end-to-end, mulai dari pengajuan oleh masyarakat, verifikasi oleh petugas, persetujuan oleh kepala desa, hingga penerbitan dokumen resmi.

## Tujuan Sistem

Tujuan pengembangan sistem SIADESA (Sistem Informasi Administrasi Desa/Kelurahan) adalah sebagai berikut:

1. Membangun sistem pelayanan administrasi desa/kelurahan berbasis web yang mudah digunakan oleh masyarakat maupun perangkat desa.
2. Mempercepat proses pengajuan dan penerbitan dokumen administrasi melalui alur kerja digital yang terstruktur.
3. Menyediakan mekanisme pelacakan status pengajuan secara *real-time* bagi masyarakat melalui nomor tiket pengajuan.
4. Mengimplementasikan sistem verifikasi dokumen bertingkat dengan peran Admin Desa sebagai verifikator dan Kepala Desa sebagai pemberi persetujuan.
5. Menyediakan fitur penerbitan dokumen resmi dengan nomor surat otomatis dan validasi keaslian melalui QR Code.
6. Mengelola data penduduk secara terstruktur dalam basis data digital yang mendukung pencarian dan impor data massal.
7. Menyediakan dashboard statistik pelayanan bagi perangkat desa untuk mendukung pengambilan keputusan.
8. Meningkatkan transparansi pelayanan publik melalui halaman informasi layanan yang dapat diakses tanpa autentikasi.
9. Menjamin keamanan data pribadi masyarakat sesuai dengan ketentuan Undang-Undang Pelindungan Data Pribadi (UU PDP) Nomor 27 Tahun 2022 [@uupdb2022].

## Ruang Lingkup

Ruang lingkup pengembangan sistem SIADESA pada versi MVP (*Minimum Viable Product*) Phase 1 mencakup:

1. **Modul Autentikasi** --- Registrasi akun masyarakat berbasis NIK, login menggunakan NIK atau email, manajemen sesi berbasis JWT (*JSON Web Token*), dan pembatasan akses berdasarkan peran (*Role-Based Access Control*).

2. **Modul Data Penduduk** --- Pengelolaan data kependudukan (NIK, nama, alamat, tempat/tanggal lahir, dan data demografis lainnya) dengan fitur pencarian, penambahan manual, dan impor data massal dari berkas Excel.

3. **Modul Pelayanan Administrasi** --- Konfigurasi jenis layanan, persyaratan dokumen, dan estimasi waktu penyelesaian oleh Admin Desa.

4. **Modul Pengajuan Online** --- Pengajuan pelayanan oleh masyarakat yang dilengkapi formulir digital, unggah dokumen persyaratan, dan pemberian nomor tiket otomatis.

5. **Modul Verifikasi** --- Pemeriksaan kelengkapan dan keabsahan dokumen pengajuan oleh Admin Desa, dengan opsi menerima, meminta perbaikan, atau menolak pengajuan.

6. **Modul Persetujuan Kepala Desa** --- Proses *approval* oleh Kepala Desa terhadap pengajuan yang telah lolos verifikasi, disertai penerbitan nomor surat resmi dan kode QR validasi.

7. **Modul Pelacakan dan Verifikasi Dokumen** --- Pelacakan status pengajuan melalui nomor tiket dan verifikasi keaslian dokumen melalui pemindaian QR Code pada halaman publik.

8. **Modul Dashboard** --- Tampilan ringkasan statistik pelayanan (total penduduk, pengajuan baru, menunggu verifikasi, menunggu persetujuan, dan selesai).

9. **Modul Cetak Surat** --- Pencetakan dokumen surat resmi dengan kop surat, nomor surat, dan QR Code validasi.

10. **Halaman Publik** --- Beranda informasi desa, katalog layanan, dan formulir pelacakan pengajuan yang dapat diakses tanpa login.

Sistem dikembangkan menggunakan arsitektur *decoupled* dengan Laravel sebagai *view engine* (Blade template), Alpine.js sebagai *framework* reaktivitas *client-side*, Express.js sebagai REST API *backend*, dan PostgreSQL sebagai basis data relasional. Deployment dilakukan pada *Virtual Private Server* (VPS) dengan Nginx sebagai *reverse proxy* [@nginx2024docs].
