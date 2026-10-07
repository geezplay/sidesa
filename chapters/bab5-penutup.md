# PENUTUP

## Kesimpulan

Berdasarkan hasil perancangan, implementasi, pengujian, dan evaluasi yang telah dilakukan terhadap sistem SIADESA, dapat disimpulkan hal-hal berikut:

1. **Digitalisasi pelayanan administrasi desa** --- Sistem SIADESA berhasil mendigitalisasi proses pelayanan administrasi desa/kelurahan yang sebelumnya dilakukan secara manual menjadi alur kerja digital berbasis web. Masyarakat dapat mengajukan permohonan layanan, mengunggah dokumen persyaratan, dan melacak status pengajuan secara mandiri tanpa harus datang ke kantor desa.

2. **Efektivitas sistem tiga peran dengan *state machine*** --- Pembagian peran pengguna menjadi Warga, Admin Desa, dan Kepala Desa yang didukung oleh mekanisme *state machine* pada alur pengajuan terbukti efektif dalam mengelola siklus hidup permohonan dokumen, mulai dari pengajuan, verifikasi, persetujuan, hingga penerbitan surat resmi.

3. **Verifikasi dokumen dan kepatuhan privasi** --- Implementasi verifikasi keaslian dokumen melalui QR Code pada halaman publik dengan penerapan *name masking* menunjukkan kepatuhan terhadap prinsip pelindungan data pribadi sebagaimana diatur dalam Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.

4. **Arsitektur *decoupled* yang terstruktur** --- Penggunaan arsitektur *decoupled* dengan Laravel Blade sebagai *view engine*, Express.js sebagai REST API *backend*, dan PostgreSQL sebagai basis data relasional memberikan pemisahan tanggung jawab (*separation of concerns*) yang jelas antara lapisan presentasi, logika bisnis, dan penyimpanan data.

5. **Keberhasilan pengujian menyeluruh** --- Seluruh 28 skenario pengujian *Black Box Testing* berhasil dieksekusi dengan tingkat keberhasilan 100%, dan seluruh 15 kebutuhan fungsional serta 8 kebutuhan non-fungsional dinyatakan terpenuhi dalam evaluasi sistem.

6. **Kesiapan *production deployment*** --- Sistem telah berhasil di-*deploy* pada *Virtual Private Server* (VPS) berbasis Ubuntu dengan konfigurasi Nginx sebagai *reverse proxy*, PM2 untuk manajemen proses Node.js dengan fitur *auto-restart*, dan sertifikat SSL dari Let's Encrypt untuk menjamin keamanan transmisi data.

## Finalisasi dan Deployment

Proses finalisasi dan *deployment* sistem SIADESA dilakukan pada *Virtual Private Server* (VPS) berbasis Ubuntu menggunakan skrip otomasi `deploy.sh` sepanjang 296 baris yang mencakup seluruh tahapan konfigurasi server.

Arsitektur *deployment* menggunakan Nginx sebagai *reverse proxy* yang menangani dua domain. Domain utama `sidesa.geezplay.site` melayani aplikasi *frontend* Laravel yang diproses melalui PHP-FPM, sedangkan domain `api.sidesa.geezplay.site` melayani REST API Express.js yang dikelola oleh PM2 sebagai *process manager* Node.js.

Basis data PostgreSQL dikonfigurasi pada server yang sama dengan pengelolaan skema menggunakan Prisma ORM. Migrasi basis data dijalankan melalui perintah `npx prisma migrate deploy` yang memastikan konsistensi struktur tabel antara lingkungan pengembangan dan produksi.

Keamanan transmisi data dijamin melalui sertifikat SSL yang diterbitkan oleh Let's Encrypt menggunakan Certbot dengan konfigurasi *auto-renewal*. Firewall server dikonfigurasi menggunakan UFW (*Uncomplicated Firewall*) yang hanya membuka port 22 (SSH), 80 (HTTP), dan 443 (HTTPS) untuk meminimalkan permukaan serangan.

PM2 dikonfigurasi untuk menjalankan aplikasi Express.js dengan fitur *auto-restart* apabila terjadi kegagalan proses, serta fitur *startup script* agar aplikasi berjalan otomatis saat server di-*reboot*. Konfigurasi ini memastikan ketersediaan layanan API secara berkelanjutan.

## Demo Sistem

Sistem SIADESA dapat diakses pada alamat berikut:

- **Frontend**: https://sidesa.geezplay.site
- **API**: https://api.sidesa.geezplay.site

Untuk keperluan demonstrasi, disediakan akun uji coba dengan kredensial sebagai berikut:

| Role | Username (NIK) | Password |
| :--- | :--- | :--- |
| Admin Desa | 199508102020121002 | password123 |
| Kepala Desa | 196803151992031004 | password123 |
| Warga (Budi Santoso) | 3201121508900001 | password123 |

Dengan akun **Admin Desa**, pengguna dapat mengakses dashboard statistik pelayanan, mengelola data penduduk dan layanan, memverifikasi dokumen pengajuan, serta membuat akun petugas baru. Dengan akun **Kepala Desa**, pengguna dapat melihat daftar pengajuan yang telah diverifikasi dan memberikan persetujuan atau penolakan terhadap permohonan tersebut. Dengan akun **Warga**, pengguna dapat mengajukan permohonan layanan administrasi, mengunggah dokumen persyaratan, serta melacak status pengajuan melalui nomor tiket yang diberikan.

## Saran

Beberapa rekomendasi pengembangan lanjutan untuk meningkatkan fungsionalitas dan jangkauan sistem SIADESA di masa depan antara lain:

1. **Integrasi notifikasi WhatsApp** --- Penambahan fitur notifikasi otomatis melalui WhatsApp Business API untuk memberitahu warga secara *real-time* mengenai perubahan status pengajuan, sehingga warga tidak perlu secara aktif memeriksa halaman pelacakan.

2. **Integrasi tanda tangan digital (*e-signature*)** --- Implementasi tanda tangan elektronik tersertifikasi pada proses persetujuan Kepala Desa agar dokumen yang diterbitkan memiliki kekuatan hukum yang setara dengan dokumen bertanda tangan basah sesuai ketentuan Undang-Undang Informasi dan Transaksi Elektronik.

3. **Ekspansi multi-desa tingkat kabupaten/kota** --- Pengembangan fitur administrasi tingkat kabupaten/kota yang memungkinkan pengelolaan beberapa desa/kelurahan dalam satu *instance* sistem dengan dasbor agregasi dan pelaporan lintas wilayah.

4. **Pengembangan aplikasi mobile** --- Pembangunan aplikasi mobile menggunakan *framework cross-platform* seperti React Native atau Flutter untuk memberikan pengalaman pengguna yang lebih optimal pada perangkat seluler, termasuk fitur notifikasi *push* dan akses *offline*.

5. **Integrasi basis data kependudukan nasional (Dukcapil)** --- Penghubungan sistem dengan *web service* Dinas Kependudukan dan Pencatatan Sipil untuk melakukan verifikasi NIK secara otomatis terhadap data kependudukan nasional, sehingga mengeliminasi kebutuhan impor data penduduk secara manual.
