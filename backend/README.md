# SIADESA Backend API

Backend API resmi **SIADESA** berbasis **Express.js + PostgreSQL + Prisma ORM**.

## Fitur Utama
- Autentikasi JWT multi-role: WARGA, ADMIN, KADES
- Alur verifikasi dua tahap: Admin memeriksa kelengkapan berkas → Semua surat diteruskan ke Kepala Desa untuk verifikasi akhir
- Generator nomor surat resmi & token QR Code validasi keaslian dokumen
- Upload dokumen KTP/KK (maks 2MB: PDF, JPG, PNG)
- Master penduduk, layanan surat, & pengaturan website desa oleh Admin

## Persiapan & Konfigurasi

1. Pastikan PostgreSQL berjalan dan database `siadesa_db` tersedia.
2. Salin `.env.example` menjadi `.env` dan sesuaikan koneksi:
```ini
DATABASE_URL="postgresql://postgres:password@localhost:5432/siadesa_db?schema=public"
```

3. Instalasi dependensi:
```bash
npm install
```

4. Sinkronisasi skema ke PostgreSQL:
```bash
npx prisma db push
```

5. Seeding data awal:
```bash
node prisma/seed.js
```

6. Jalankan server:
```bash
npm run dev
# atau
node src/server.js
```

Server aktif di `http://localhost:5000`.

## Kredensial Demo
- **Warga**: `3201121508900001` / `password123`
- **Admin Desa (Merangkap Verifikator)**: `199508102020121002` / `password123`
- **Kepala Desa**: `196803151992031004` / `password123`

## Daftar Endpoint API
- `POST /api/auth/register` - Pendaftaran NIK warga
- `POST /api/auth/login` - Login multi-role
- `PUT /api/auth/profile` - Lengkapi biodata warga (BR-02)
- `POST /api/applications` - Pengajuan surat warga
- `GET /api/applications/my` - Riwayat pengajuan warga
- `GET /api/admin/verifications` - Antrean verifikasi berkas Admin
- `PATCH /api/admin/verifications/:id` - Keputusan Admin: APPROVE (teruskan ke Kades) / REVISE / REJECT
- `GET /api/kades/approvals` - Antrean persetujuan surat Kepala Desa
- `PATCH /api/kades/approvals/:id` - Persetujuan Akhir Kepala Desa: APPROVE / REJECT
- `GET /api/public/services` - Katalog layanan
- `GET /api/public/tracking/:code` - Lacak tiket ADM-XXXXXX
- `GET /api/public/verify-doc/:code` - Validasi QR keaslian surat
