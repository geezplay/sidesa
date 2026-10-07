# Panduan Deploy SIADESA ke VPS

Dokumen ini menjelaskan alur lengkap deploy **SIADESA** (Laravel + Express.js + PostgreSQL) ke VPS dengan domain:

- **Website (frontend):** `https://sidesa.geezplay.site`
- **API (backend):** `https://api.sidesa.geezplay.site`

Repositori: `https://github.com/geezplay/sidesa.git`

---

## Ringkasan Arsitektur

```
Browser
   │
   ├── https://sidesa.geezplay.site ──► Nginx ──► PHP-FPM (Laravel)
   │
   └── https://api.sidesa.geezplay.site ──► Nginx (proxy) ──► Express.js (PM2, port 5000)
                                                                   │
                                                                   ▼
                                                            PostgreSQL (5432, lokal)
```

| Komponen | Teknologi | Port | Domain |
|---|---|---|---|
| Frontend | Laravel 12 + Blade + Vite + Alpine.js | 80/443 | sidesa.geezplay.site |
| Backend API | Express.js + Prisma ORM | 5000 (lokal) | api.sidesa.geezplay.site |
| Database | PostgreSQL | 5432 (lokal) | — |
| Process Manager | PM2 | — | — |

---

## Prasyarat

1. VPS Ubuntu 22.04 / 24.04 dengan akses `sudo`.
2. Domain `geezplay.site` dikelola (akses DNS).
3. Repositori GitHub `geezplay/sidesa` sudah berisi kode terbaru.
4. Port 80 & 443 terbuka (akan diatur UFW oleh `deploy.sh`).

---

## Langkah 1 — Setup DNS

Di panel DNS `geezplay.site`, tambahkan 2 record A yang mengarah ke IP VPS:

| Tipe | Nama | Nilai |
|------|------|-------|
| A | `sidesa` | `IP_VPS_ANDA` |
| A | `api` | `IP_VPS_ANDA` |

Tunggu propagasi (5–30 menit). Cek dari VPS:

```bash
dig +short sidesa.geezplay.site
dig +short api.sidesa.geezplay.site
```

> Pastikan keduanya sudah menampilkan IP VPS sebelum menjalankan `deploy.sh` agar SSL Let's Encrypt otomatis berhasil.

---

## Langkah 2 — Push Kode ke GitHub (dari komputer lokal)

```bash
# Ganti remote jika belum benar
git remote set-url origin https://github.com/geezplay/sidesa.git

git add -A
git commit -m "deskripsi perubahan"
git push -u origin main
```

---

## Langkah 3 — Clone di VPS

```bash
# Install git (sekali saja)
sudo apt update && sudo apt install -y git

# Clone ke /var/www/sidesa
sudo mkdir -p /var/www/sidesa
sudo git clone https://github.com/geezplay/sidesa.git /var/www/sidesa
cd /var/www/sidesa
```

**Jika repo privat**, gunakan token:
```bash
sudo git clone https://geezplay:TOKEN_GITHUB@github.com/geezplay/sidesa.git /var/www/sidesa
```

---

## Langkah 4 — Jalankan Deploy Otomatis

Script `deploy.sh` akan melakukan seluruh proses provisioning:

```bash
sudo chmod +x deploy.sh
sudo DOMAIN=sidesa.geezplay.site API_DOMAIN=api.sidesa.geezplay.site ./deploy.sh
```

Saat diminta konfirmasi, ketik `y`.

### Yang dilakukan `deploy.sh`:
1. Install **Nginx, PHP 8.2, PostgreSQL, Node.js 22, Composer, PM2, Certbot**.
2. Membuat **database & user PostgreSQL** (`siadesa_db` / `siadesa`) dengan password acak (tercatat di output — **simpan!**).
3. Deploy **backend**:
   - Menulis `backend/.env` (koneksi DB + `JWT_SECRET` acak).
   - `npm install`, `prisma generate`, `prisma db push`.
   - Seed data awal (desa, akun admin/kades/warga, layanan).
   - Menjalankan API via **PM2** (`siadesa-api`).
4. Deploy **frontend**:
   - Menulis `.env` produksi (`APP_ENV=production`, `APP_URL`, `VITE_API_URL=https://api.sidesa.geezplay.site`).
   - `composer install`, `npm run build`, `artisan optimize` (config/route/view cache).
5. Konfigurasi **Nginx** untuk domain utama + subdomain API.
6. Aktifkan **Firewall UFW** (SSH + Nginx).
7. Pasang **SSL Let's Encrypt** + redirect HTTP → HTTPS.

---

## Langkah 5 — Verifikasi

```bash
# Status backend
pm2 status
pm2 logs siadesa-api --lines 50

# Status layanan
sudo systemctl status nginx php8.2-fpm postgresql

# Tes endpoint
curl -I https://sidesa.geezplay.site
curl https://api.sidesa.geezplay.site/api/public/info
```

Buka browser: `https://sidesa.geezplay.site`

### Akun demo hasil seeder
| Peran | Username | Password |
|-------|----------|----------|
| Admin Desa | `199508102020121002` | `password123` |
| Kepala Desa | `196803151992031004` | `password123` |
| Warga | `3201121508900001` | `password123` |

> **Penting:** segera ganti password default melalui menu **Kelola Akun Petugas** dan **Profil** setelah login pertama.

---

## Alur Update (Redeploy)

### Di lokal:
```bash
git add -A
git commit -m "update: ..."
git push
```

### Di VPS:
```bash
cd /var/www/sidesa
sudo ./deploy.sh --update
```

`--update` akan menjalankan: `git pull` → install backend → `prisma generate` + `db push` → build frontend → restart PM2 & Nginx. Data database **tidak dihapus**.

---

## Perintah Operasional Berguna

```bash
# Backend
pm2 status                 # status proses API
pm2 logs siadesa-api       # log realtime
pm2 restart siadesa-api    # restart API
pm2 restart all

# Laravel
cd /var/www/sidesa
php artisan optimize:clear # bersihkan cache
php artisan config:cache   # cache konfigurasi
php artisan migrate:status

# Database (backup & restore)
sudo -u postgres pg_dump siadesa_db > backup_siadesa_$(date +%F).sql
sudo -u postgres psql siadesa_db < backup_siadesa_2026-10-06.sql

# Nginx
sudo nginx -t && sudo systemctl reload nginx

# SSL
sudo certbot renew --dry-run
sudo systemctl status certbot.timer
```

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Halaman 502 Bad Gateway | `pm2 status` — pastikan `siadesa-api` online. Jika mati: `pm2 logs siadesa-api`, lalu `pm2 restart siadesa-api`. |
| Frontend tampil tapi data kosong / "Tidak dapat terhubung ke API" | Cek `VITE_API_URL` di `.env` = `https://api.sidesa.geezplay.site`, lalu rebuild: `npm run build` + `php artisan optimize:clear`. |
| CORS / API diblokir | Pastikan `backend/.env` bagian `FRONTEND_URL="https://sidesa.geezplay.site"`, lalu `pm2 restart siadesa-api`. |
| SSL gagal | Pastikan DNS sudah mengarah ke VPS, lalu jalankan manual: `sudo certbot --nginx -d sidesa.geezplay.site -d api.sidesa.geezplay.site`. |
| Perubahan `.env` tidak berefek | Jalankan `php artisan optimize:clear` (frontend) dan `pm2 restart siadesa-api` (backend). |
| Upload berkas gagal | Pastikan permission: `sudo chown -R www-data:www-data /var/www/sidesa/backend/storage && sudo chmod -R 775 /var/www/sidesa/backend/storage`. |
| Login gagal setelah update | Pastikan `prisma db push` sudah dijalankan (bagian dari `--update`). |

---

## Catatan Keamanan

- File `.env` (root & `backend/`) **tidak pernah** di-commit ke GitHub (sudah di-`.gitignore`).
- Ganti password default semua akun setelah deploy.
- `JWT_SECRET` dan password database dibuat otomatis oleh `deploy.sh` — simpan di tempat aman.
- Dokumen warga disimpan di `backend/storage/uploads` (non-publik).
- Aktifkan backup otomatis (cron) untuk `pg_dump` harian.

---

## Struktur File Penting

```
/var/www/sidesa/
├── deploy.sh                 # Script deploy otomatis
├── .env                      # Konfigurasi Laravel (dibuat saat deploy)
├── app/  resources/  routes/ # Kode Laravel (frontend)
├── public/                   # Document root Nginx
└── backend/
    ├── .env                  # Konfigurasi API + DATABASE_URL (dibuat saat deploy)
    ├── prisma/schema.prisma  # Skema database
    ├── prisma/seed.js        # Data awal
    ├── src/                  # Kode Express API
    └── storage/uploads/      # Berkas dokumen privat
```
