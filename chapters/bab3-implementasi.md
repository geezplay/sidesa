# IMPLEMENTASI

Bab ini memaparkan implementasi sistem SIADESA secara teknis, mencakup konfigurasi lingkungan pengembangan, struktur basis data, modul autentikasi, halaman publik, alur pengajuan pelayanan, mekanisme verifikasi dan persetujuan, manajemen data master, dashboard administratif, fitur cetak surat, arsitektur frontend, serta strategi deployment ke lingkungan produksi. Setiap subbab disertai potongan kode sumber yang relevan dari implementasi aktual sistem.

## Lingkungan Pengembangan

Proses pengembangan sistem SIADESA dilaksanakan menggunakan seperangkat peralatan perangkat lunak yang mendukung arsitektur *decoupled* antara frontend berbasis Laravel dan backend berbasis Express.js. Pemilihan versi perangkat lunak didasarkan pada ketersediaan fitur terkini serta kompatibilitas antar-komponen sistem. Tabel berikut merangkum spesifikasi lingkungan pengembangan yang digunakan.

| No. | Perangkat Lunak | Versi | Fungsi |
|-----|----------------|-------|--------|
| 1 | Windows 11 | 24H2 | Sistem operasi pengembangan |
| 2 | Visual Studio Code | 1.96.x | *Integrated development environment* |
| 3 | Node.js | 22.x LTS | *Runtime* JavaScript untuk backend Express.js |
| 4 | PHP | 8.2.x | *Runtime* untuk framework Laravel |
| 5 | PostgreSQL | 16.x | Sistem manajemen basis data relasional |
| 6 | Git | 2.47.x | Sistem kontrol versi kode sumber |
| 7 | npm | 10.x | Manajer paket Node.js |
| 8 | Composer | 2.8.x | Manajer paket PHP |
| 9 | Google Chrome | 131.x | Peramban untuk pengujian antarmuka |
| 10 | Postman | 11.x | Pengujian endpoint REST API |

Arsitektur pengembangan mengadopsi pola *decoupled*, di mana Laravel 12.x berfungsi secara eksklusif sebagai *view engine* menggunakan Blade *templates* tanpa mengeksekusi logika bisnis apa pun di sisi server PHP. Seluruh logika bisnis dan operasi data diproses oleh backend Express.js 4.x yang berkomunikasi dengan PostgreSQL melalui Prisma ORM 6.x. Alpine.js 3.x digunakan untuk reaktivitas sisi klien, Tailwind CSS 4.x untuk sistem desain utilitas, dan Vite 7.x sebagai *build tool* untuk kompilasi aset frontend.

## Implementasi Basis Data

Skema basis data sistem SIADESA diimplementasikan menggunakan Prisma ORM yang terhubung ke PostgreSQL [@prisma2024docs]. Prisma menyediakan pendekatan *schema-first* di mana seluruh struktur tabel, relasi, dan *constraint* didefinisikan dalam satu berkas deklaratif bernama `schema.prisma`. Pendekatan ini memastikan konsistensi antara definisi skema dan struktur basis data aktual melalui mekanisme migrasi otomatis.

Konfigurasi koneksi basis data ditetapkan pada blok `datasource` yang membaca URL koneksi dari variabel lingkungan:

```prisma
datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

generator client {
  provider = "prisma-client-js"
}
```

Skema basis data terdiri atas 9 model dan 2 enum. Enum `Role` mendefinisikan tiga peran pengguna dalam sistem, yaitu `WARGA`, `ADMIN`, dan `KADES`. Enum `AppStatus` merepresentasikan seluruh status yang mungkin dalam siklus hidup pengajuan pelayanan:

```prisma
enum Role {
  WARGA
  ADMIN
  KADES
}

enum AppStatus {
  DRAFT
  DIAJUKAN
  PERLU_PERBAIKAN
  MENUNGGU_PERSETUJUAN
  DISETUJUI
  DIPROSES
  SELESAI
  DITOLAK
  DIBATALKAN
}
```

Model utama yang menjadi inti sistem mencakup `Village` sebagai entitas desa, `User` untuk akun pengguna, `Resident` untuk data kependudukan, `Service` untuk jenis layanan, `Application` untuk pengajuan, `ApplicationDocument` untuk dokumen lampiran, `StatusHistory` untuk riwayat perubahan status, `ServiceRequirement` untuk persyaratan layanan, dan `Setting` untuk konfigurasi dinamis. Setiap model menggunakan UUID sebagai *primary key* dan menyimpan *timestamp* pembuatan serta pembaruan secara otomatis.

Relasi antar-model dirancang dengan integritas referensial. Model `Application` berelasi dengan `Village`, `User`, `Resident`, dan `Service`, serta memiliki relasi *one-to-many* dengan `ApplicationDocument` dan `StatusHistory`. Struktur model tersebut dirincikan pada tabel berikut:

| Field | Tipe Data | Atribut dan Constraint |
|:---|:---|:---|
| id | String | *Primary key*, nilai bawaan UUID |
| village_id | String | *Foreign key* menuju `Village` |
| user_id | String | *Foreign key* menuju `User` |
| resident_id | String (opsional) | *Foreign key* menuju `Resident` |
| service_id | String | *Foreign key* menuju `Service` |
| tracking_number | String | Unik (*unique*) |
| purpose | String | Keperluan pengajuan |
| status | AppStatus | *Enum*, nilai bawaan `DIAJUKAN` |
| letter_number | String (opsional) | Nomor surat resmi |
| qr_uuid | String (opsional) | Unik (*unique*), kode validasi QR |
| created_at | DateTime | *Timestamp*, nilai bawaan waktu kini |
| updated_at | DateTime | *Timestamp*, diperbarui otomatis |
| documents | ApplicationDocument[] | Relasi *one-to-many* |
| histories | StatusHistory[] | Relasi *one-to-many* |

Relasi antar-tabel menggunakan integritas referensial dengan perilaku `onDelete` terdefinisi, yaitu `Cascade` pada relasi utama dan `SetNull` pada relasi data penduduk yang bersifat opsional.

Sinkronisasi skema ke basis data dilakukan menggunakan perintah `npx prisma db push` untuk lingkungan pengembangan atau `npx prisma migrate deploy` untuk lingkungan produksi. Data awal (*seed*) dimasukkan melalui berkas `seed.js` yang memuat data Desa Sukamaju, 3 akun pengguna (Admin, Kepala Desa, Warga), 3 data penduduk, 6 jenis layanan administrasi, 4 pengaturan situs, dan 1 contoh pengajuan beserta riwayat statusnya.

## Implementasi Autentikasi dan Otorisasi

Sistem autentikasi SIADESA diimplementasikan menggunakan JSON Web Token (JWT) untuk manajemen sesi *stateless* [@jones2015json] dan bcryptjs untuk *hashing* kata sandi [@provos1999bcrypt]. Pendekatan ini dipilih karena arsitektur *decoupled* yang memisahkan frontend Laravel dari backend Express.js, sehingga mekanisme sesi berbasis *cookie* tradisional tidak dapat diterapkan secara langsung.

### Registrasi Akun

Proses registrasi mengadopsi kebijakan berbasis NIK, di mana calon pengguna wajib terlebih dahulu terdaftar dalam data kependudukan desa oleh administrator. Validasi dilakukan secara bertahap: pertama memastikan NIK berupa 16 digit angka valid, kemudian memeriksa apakah NIK belum memiliki akun, dan terakhir memverifikasi keberadaan NIK dalam tabel `Resident`. Kata sandi di-*hash* menggunakan bcrypt dengan *salt round* 10 sebelum disimpan ke basis data:

```javascript
const residentRecord = await prisma.resident.findFirst({
  where: { nik }
});

if (!residentRecord) {
  return res.status(400).json({
    success: false,
    message: 'NIK tidak ditemukan dalam Data Penduduk Desa Sukamaju!'
  });
}

const password_hash = await bcrypt.hash(password, 10);

const newUser = await prisma.user.create({
  data: {
    village_id: village.id,
    nik,
    name: residentRecord.full_name || name,
    phone,
    password_hash,
    role: 'WARGA'
  }
});
```

Setelah registrasi berhasil, sistem menghasilkan token JWT dengan masa berlaku 12 jam dan mengaitkan akun pengguna baru dengan data penduduk yang bersangkutan.

### Proses Login

Mekanisme login mendukung identifikasi ganda melalui NIK atau alamat *email*. Sistem melakukan pencarian pengguna menggunakan operator `OR` pada Prisma, kemudian membandingkan kata sandi yang dikirimkan dengan *hash* tersimpan menggunakan `bcrypt.compare()`. Pemeriksaan kelengkapan profil juga dilakukan pada saat login untuk menentukan apakah pengguna perlu menyelesaikan biodata kependudukan:

```javascript
const user = await prisma.user.findFirst({
  where: {
    OR: [
      { nik: trimmed },
      { email: trimmed }
    ]
  },
  include: { resident: true, village: true }
});

const isMatch = await bcrypt.compare(password, user.password_hash);

const token = jwt.sign(
  { userId: user.id, role: user.role },
  process.env.JWT_SECRET,
  { expiresIn: '12h' }
);
```

### Pembatasan Laju Akses

Untuk mencegah serangan *brute-force* pada endpoint login, diterapkan mekanisme *rate limiting* menggunakan pustaka `express-rate-limit`. Konfigurasi membatasi maksimal 20 percobaan login per menit dari satu alamat IP:

```javascript
const loginLimiter = rateLimit({
  windowMs: 1 * 60 * 1000,
  max: 20,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    success: false,
    message: 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.'
  }
});

app.use('/api/auth/login', loginLimiter);
```

### Middleware Autentikasi dan Otorisasi

Dua fungsi middleware diimplementasikan untuk mengamankan endpoint API. Fungsi `authenticate()` mengekstrak token JWT dari header `Authorization`, memverifikasi validitasnya, dan memuat data pengguna lengkap beserta relasi `resident` dan `village` dari basis data. Fungsi `requireRole()` menerima daftar peran yang diizinkan dan menolak akses jika peran pengguna tidak sesuai:

```javascript
export const authenticate = async (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({
      success: false,
      message: 'Akses ditolak: Token autentikasi tidak ditemukan.'
    });
  }

  const token = authHeader.split(' ')[1];
  const decoded = jwt.verify(token, process.env.JWT_SECRET);

  const user = await prisma.user.findUnique({
    where: { id: decoded.userId },
    include: { resident: true, village: true }
  });

  if (!user || !user.is_active) {
    return res.status(401).json({
      success: false,
      message: 'Sesi akun tidak valid atau akun telah dinonaktifkan.'
    });
  }

  req.user = user;
  next();
};

export const requireRole = (...roles) => {
  return (req, res, next) => {
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: `Akses ditolak: Anda tidak memiliki izin.`
      });
    }
    next();
  };
};
```

Di sisi klien, token JWT disimpan dalam `localStorage` dengan kunci `siadesa_token`, sedangkan data sesi pengguna disimpan dengan kunci `siadesa_auth_session`. Apabila respons API mengembalikan status 401, token secara otomatis dihapus untuk memaksa pengguna melakukan login ulang.

## Implementasi Halaman Publik

Halaman publik SIADESA dirancang untuk dapat diakses tanpa autentikasi, menyediakan informasi umum mengenai layanan desa kepada masyarakat. Seluruh data halaman publik disajikan oleh empat endpoint REST API pada modul `public.controller.js`.

### Beranda dan Informasi Desa

Endpoint `GET /api/public/info` menyajikan profil desa beserta pengaturan situs. Data yang dikembalikan meliputi nama desa, alamat, nomor telepon, alamat *email*, nama kepala desa, jam pelayanan, serta pengaturan dinamis seperti nama aplikasi dan URL logo. Di sisi frontend, data ini dimuat oleh `SiadesaStore.loadSiteSettings()` saat aplikasi diinisialisasi dan disimpan dalam `localStorage` sebagai *cache* untuk akses cepat.

### Katalog Layanan

Endpoint `GET /api/public/services` menampilkan daftar layanan administrasi yang berstatus aktif beserta persyaratan dokumennya. Kueri Prisma menggunakan relasi `include` untuk memuat data `requirements` secara *eager*:

```javascript
const services = await prisma.service.findMany({
  where: { is_active: true },
  include: { requirements: true },
  orderBy: { code: 'asc' }
});
```

### Pelacakan Pengajuan

Fitur pelacakan memungkinkan pemohon memantau status pengajuan menggunakan nomor tiket berformat `ADM-YYYYMMDD-XXXXXX`. Endpoint `GET /api/public/tracking/:code` melakukan pencarian berdasarkan `tracking_number` dan mengembalikan data pengajuan beserta riwayat perubahan status secara kronologis. Pencarian bersifat *case-insensitive* melalui konversi ke huruf kapital sebelum kueri dilakukan.

### Verifikasi Dokumen QR

Halaman verifikasi dokumen tersedia di *route* `/verifikasi-surat/{code}` dan terhubung ke endpoint `GET /api/public/verify-doc/:code`. Fitur ini memungkinkan pihak ketiga memverifikasi keaslian surat yang diterbitkan oleh desa melalui pemindaian kode QR. Sesuai dengan ketentuan Undang-Undang Pelindungan Data Pribadi (UU PDP No. 27 Tahun 2022), nama pemohon ditampilkan dalam bentuk tersamarkan:

```javascript
const rawName = app.resident ? app.resident.full_name : 'Warga';
const sensoredName = rawName
  .split(' ')
  .map(w => w[0] + '***')
  .join(' ');
```

Informasi yang ditampilkan pada halaman verifikasi meliputi nomor surat, jenis dokumen, nama tersamarkan, tanggal penerbitan, nama desa, serta status validitas dokumen (`VALID` atau `INVALID`).

## Implementasi Pengajuan Pelayanan

Modul pengajuan pelayanan merupakan inti fungsional sistem SIADESA, menangani proses pembuatan permohonan surat administrasi oleh warga desa. Implementasi mencakup validasi kelengkapan profil, unggah dokumen persyaratan, dan pembangkitan nomor pelacakan.

### Validasi Kelengkapan Profil

Sebelum pengajuan dapat diproses, sistem memvalidasi kelengkapan biodata kependudukan pemohon sesuai aturan bisnis BR-02. Pemeriksaan dilakukan terhadap keberadaan data `resident` yang terkait dengan akun pengguna, khususnya nomor Kartu Keluarga dan alamat:

```javascript
if (!user.resident || !user.resident.family_card_no || !user.resident.address) {
  return res.status(403).json({
    success: false,
    message: 'Anda wajib melengkapi biodata kependudukan sebelum mengajukan permohonan surat (Aturan BR-02).'
  });
}
```

### Unggah Dokumen Persyaratan

Pengunggahan berkas dokumen dikelola menggunakan pustaka Multer [@multer2024docs] dengan konfigurasi penyimpanan berbasis *disk*. Setiap berkas yang diunggah diberi nama unik menggunakan kombinasi *timestamp* dan UUID untuk mencegah konflik penamaan. Validasi tipe berkas membatasi format yang diterima hanya pada PDF, JPG, JPEG, dan PNG dengan ukuran maksimal 2 MB per berkas:

```javascript
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const uniqueName = `${Date.now()}-${uuidv4()}${ext}`;
    cb(null, uniqueName);
  }
});

const fileFilter = (req, file, cb) => {
  const allowedMime = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
  if (allowedMime.includes(file.mimetype)) {
    cb(null, true);
  } else {
    cb(new Error('Format file tidak didukung!'), false);
  }
};

export const upload = multer({
  storage,
  limits: { fileSize: 2 * 1024 * 1024 }
});
```

Berkas diunggah ke direktori `storage/uploads/` yang berada di luar direktori publik untuk menjaga keamanan dokumen. Maksimal 5 berkas dapat diunggah dalam satu pengajuan.

### Pembangkitan Nomor Pelacakan

Setiap pengajuan yang berhasil dibuat akan mendapatkan nomor pelacakan unik dengan format `ADM-YYYYMMDD-XXXXXX`, di mana `YYYYMMDD` merupakan tanggal pengajuan dan `XXXXXX` merupakan enam digit angka acak. Nomor ini disimpan dengan *constraint* `@unique` pada basis data untuk menjamin keunikan:

```javascript
const now = new Date();
const pad = (n) => String(n).padStart(2, '0');
const dateStr = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}`;
const randomHex = Math.floor(100000 + Math.random() * 900000);
const tracking_number = `ADM-${dateStr}-${randomHex}`;
```

### Pencatatan Riwayat Status

Setiap pembuatan pengajuan baru secara otomatis mencatat entri pertama pada tabel `StatusHistory` dengan status `DIAJUKAN`. Pencatatan ini menjadi fondasi jejak audit (*audit trail*) yang merekam setiap transisi status beserta aktor dan catatan yang terkait:

```javascript
await prisma.statusHistory.create({
  data: {
    application_id: newApp.id,
    status: 'DIAJUKAN',
    actor_name: `${user.name} (Pemohon)`,
    notes: 'Permohonan surat berhasil dikirimkan secara online.'
  }
});
```

## Implementasi Verifikasi dan Persetujuan

Alur verifikasi dan persetujuan dalam SIADESA mengikuti pola *state machine* dua tahap: verifikasi berkas oleh Admin Desa dan persetujuan akhir oleh Kepala Desa. Setiap transisi status dicatat dalam tabel `StatusHistory` untuk menjaga transparansi dan akuntabilitas proses.

### Verifikasi Berkas oleh Admin

Admin Desa melakukan verifikasi kelengkapan dan keabsahan berkas melalui endpoint `PATCH /api/admin/verifications/:id`. Tiga keputusan tersedia pada tahap ini:

1. **APPROVE** -- berkas dinyatakan valid dan diteruskan ke Kepala Desa, status diubah menjadi `MENUNGGU_PERSETUJUAN`.
2. **REVISE** -- berkas memerlukan perbaikan, status diubah menjadi `PERLU_PERBAIKAN` dan catatan perbaikan wajib diisi.
3. **REJECT** -- pengajuan ditolak, status diubah menjadi `DITOLAK` dengan alasan penolakan wajib dicatat.

```javascript
if (decision === 'APPROVE') {
  newStatus = 'MENUNGGU_PERSETUJUAN';
  actorNote = notes || 'Berkas dinyatakan valid. Diteruskan ke Kepala Desa.';
} else if (decision === 'REVISE') {
  if (!notes) {
    return res.status(400).json({
      success: false,
      message: 'Catatan perbaikan berkas wajib diisi!'
    });
  }
  newStatus = 'PERLU_PERBAIKAN';
  actorNote = notes;
} else if (decision === 'REJECT') {
  if (!notes) {
    return res.status(400).json({
      success: false,
      message: 'Alasan penolakan wajib dicatat!'
    });
  }
  newStatus = 'DITOLAK';
  actorNote = notes;
}
```

Apabila status diubah menjadi `PERLU_PERBAIKAN`, pemohon dapat melakukan pengajuan ulang melalui endpoint `PUT /api/applications/:id/resubmit` yang mengembalikan status ke `DIAJUKAN`. Mekanisme ini membentuk siklus perbaikan yang memungkinkan iterasi hingga berkas dinyatakan lengkap.

### Persetujuan oleh Kepala Desa

Tahap persetujuan akhir ditangani oleh endpoint `PATCH /api/kades/approvals/:id` yang hanya dapat diakses oleh pengguna dengan peran `KADES`. Dua aksi tersedia pada tahap ini: `APPROVE` dan `REJECT`.

Pada saat persetujuan, sistem secara otomatis membangkitkan nomor surat resmi dan UUID untuk kode QR. Nomor surat mengikuti format `500/XXX/CODE/X/YEAR` sesuai konvensi penomoran surat dinas pemerintah desa. UUID dokumen dibangkitkan dengan format `SIADESA-DOC-XXXXXXXX-XXXXXXXX` untuk keperluan verifikasi keaslian:

```javascript
if (action === 'APPROVE') {
  const now = new Date();
  const year = now.getFullYear();
  const seq = Math.floor(100 + Math.random() * 899);
  const letter_number = `500/${seq}/${app.service.code}/X/${year}`;
  const qr_uuid = `SIADESA-DOC-${year}1005-${uuidv4().substring(0, 8).toUpperCase()}`;

  const updated = await prisma.application.update({
    where: { id },
    data: {
      status: 'SELESAI',
      approval_notes: notes || 'Disetujui dan disahkan oleh Kepala Desa.',
      letter_number,
      qr_uuid
    }
  });
}
```

Status pengajuan diubah langsung menjadi `SELESAI` setelah persetujuan, yang menandakan surat resmi telah diterbitkan dan siap dicetak. Keseluruhan diagram transisi status (*state machine*) dapat dirangkum sebagai berikut:

```
DIAJUKAN → MENUNGGU_PERSETUJUAN → SELESAI
    ↕              ↓
PERLU_PERBAIKAN   DITOLAK
    ↓
DIBATALKAN
```

## Implementasi Manajemen Data

Modul manajemen data menyediakan antarmuka administratif untuk mengelola data master yang menjadi fondasi operasional sistem SIADESA. Seluruh operasi manajemen data dilindungi oleh middleware `authenticate()` dan `requireRole('ADMIN')`.

### Manajemen Data Penduduk

Pengelolaan data penduduk mencakup operasi CRUD standar beserta fitur impor massal. Fitur pencarian mendukung pencarian berdasarkan nama, NIK, atau nomor Kartu Keluarga menggunakan operator `contains` dengan mode *case-insensitive* pada Prisma:

```javascript
const where = { village_id: user.village_id };
if (search) {
  where.OR = [
    { full_name: { contains: search, mode: 'insensitive' } },
    { nik: { contains: search } },
    { family_card_no: { contains: search } }
  ];
}

const residents = await prisma.resident.findMany({
  where,
  include: { user: { select: { id: true, role: true, is_active: true } } },
  orderBy: { full_name: 'asc' }
});
```

Fitur impor massal menerima data dalam format JSON melalui endpoint `POST /api/admin/residents/import`. Di sisi frontend, berkas Excel (.xlsx) dibaca menggunakan pustaka XLSX, dikonversi menjadi *array* objek JSON, kemudian dikirimkan ke backend. Proses impor menggunakan pola *upsert*: data penduduk yang sudah ada berdasarkan NIK akan diperbarui, sedangkan data baru akan dibuat. Validasi NIK 16 digit diterapkan untuk setiap baris data:

```javascript
for (const r of residents) {
  if (!r.nik || r.nik.length !== 16) continue;

  const existing = await prisma.resident.findFirst({
    where: { nik: r.nik, village_id: user.village_id }
  });

  if (existing) {
    await prisma.resident.update({ where: { id: existing.id }, data: { ... } });
    updated++;
  } else {
    await prisma.resident.create({ data: { village_id: user.village_id, nik: r.nik, ... } });
    imported++;
  }
}
```

### Manajemen Layanan Administrasi

Pengelolaan jenis layanan mencakup pembuatan, pembaruan, dan penonaktifan layanan beserta daftar persyaratan dokumennya. Setiap layanan memiliki kode unik per desa yang dijamin oleh *compound unique constraint* `@@unique([village_id, code])`. Pada saat pembaruan layanan, seluruh daftar persyaratan diganti secara atomik menggunakan pola *delete-then-recreate*:

```javascript
if (requirements && Array.isArray(requirements)) {
  await prisma.serviceRequirement.deleteMany({
    where: { service_id: svc.id }
  });
  for (const reqName of requirements) {
    if (reqName && reqName.trim()) {
      await prisma.serviceRequirement.create({
        data: { service_id: svc.id, name: reqName.trim(), is_mandatory: true }
      });
    }
  }
}
```

### Manajemen Akun Petugas

Pengelolaan akun petugas (Admin dan Kepala Desa) dilengkapi dengan beberapa aturan pengamanan. Pertama, hanya satu akun dengan peran `KADES` yang diizinkan per desa. Kedua, minimal satu akun `ADMIN` harus selalu ada. Ketiga, pengguna tidak dapat menghapus akunnya sendiri. Validasi-validasi ini diterapkan secara eksplisit pada controller:

```javascript
if (targetRole === 'KADES') {
  const existingKades = await prisma.user.findFirst({
    where: { village_id: user.village_id, role: 'KADES' }
  });
  if (existingKades) {
    return res.status(400).json({
      success: false,
      message: 'Akun Kepala Desa hanya boleh satu.'
    });
  }
}
```

### Pengaturan Situs

Modul pengaturan memungkinkan administrator mengelola profil desa (alamat, telepon, *email*, nama kepala desa, NIP) serta konten dinamis situs (nama aplikasi, URL logo). Pengaturan dinamis disimpan menggunakan pola *key-value* pada tabel `Setting` dengan mekanisme *upsert* untuk menjamin setiap kunci hanya memiliki satu nilai per desa.

## Implementasi Dashboard

Dashboard administratif menyajikan ringkasan statistik operasional pelayanan desa secara *real-time*. Endpoint `GET /api/admin/dashboard` mengagregasi lima metrik utama melalui kueri `count` pada Prisma, di mana setiap kueri difilter berdasarkan `village_id` pengguna yang sedang terautentikasi:

```javascript
const totalPenduduk = await prisma.resident.count({
  where: { village_id: user.village_id }
});

const totalApplications = await prisma.application.count({
  where: { village_id: user.village_id }
});

const needVerify = await prisma.application.count({
  where: { village_id: user.village_id, status: 'DIAJUKAN' }
});

const waitingKades = await prisma.application.count({
  where: { village_id: user.village_id, status: 'MENUNGGU_PERSETUJUAN' }
});

const completed = await prisma.application.count({
  where: { village_id: user.village_id, status: 'SELESAI' }
});
```

Lima metrik yang ditampilkan pada dashboard meliputi:

| No. | Metrik | Filter Status | Keterangan |
|-----|--------|--------------|------------|
| 1 | Total Penduduk | -- | Jumlah seluruh data penduduk terdaftar |
| 2 | Total Pengajuan | -- | Jumlah seluruh pengajuan yang pernah masuk |
| 3 | Menunggu Verifikasi | `DIAJUKAN` | Pengajuan yang belum diverifikasi admin |
| 4 | Menunggu Persetujuan | `MENUNGGU_PERSETUJUAN` | Pengajuan yang menunggu keputusan Kepala Desa |
| 5 | Selesai | `SELESAI` | Pengajuan yang telah diterbitkan suratnya |

Selain metrik agregat, dashboard juga menampilkan 5 pengajuan terbaru beserta informasi layanan dan nama pemohon, yang diambil menggunakan kueri `findMany` dengan opsi `take: 5` dan pengurutan berdasarkan waktu pembuatan terbaru.

## Implementasi Cetak Surat dan Verifikasi Dokumen

### Cetak Surat

Fitur cetak surat diimplementasikan melalui halaman khusus `print-letter.blade.php` yang menampilkan surat dalam format siap cetak. Halaman ini memanfaatkan *media query* CSS `@media print` untuk mengoptimalkan tata letak saat dicetak melalui fungsi cetak bawaan peramban. Komponen surat yang ditampilkan mencakup kop surat resmi desa, nomor surat yang telah dibangkitkan pada tahap persetujuan, isi surat berdasarkan jenis layanan, *placeholder* kode QR yang merujuk pada `qr_uuid`, serta blok tanda tangan Kepala Desa.

Data surat dimuat dari API backend melalui `SiadesaStore.fetchApplicationById()` yang mengembalikan seluruh detail pengajuan termasuk `letter_number`, `qr_uuid`, data pemohon, dan jenis layanan. Tampilan cetak menyembunyikan elemen navigasi dan kontrol antarmuka untuk menghasilkan keluaran cetak yang bersih.

### Verifikasi Dokumen QR

Halaman verifikasi dokumen publik tersedia di *route* `/verifikasi-surat/{code}` dan berfungsi sebagai mekanisme validasi keaslian surat yang diterbitkan. Pihak ketiga dapat mengakses halaman ini melalui pemindaian kode QR yang tercetak pada surat resmi.

Informasi yang ditampilkan pada halaman verifikasi dirancang untuk memberikan konfirmasi keaslian tanpa mengekspos data pribadi pemohon secara berlebihan. Nama pemohon disamarkan dengan menampilkan hanya huruf pertama dari setiap kata yang diikuti oleh tanda asterisk, misalnya "Budi Santoso" ditampilkan sebagai "B*** S***". Pendekatan ini memenuhi prinsip minimisasi data sebagaimana diatur dalam UU PDP No. 27 Tahun 2022.

Respons endpoint verifikasi menampilkan status `VALID` hanya apabila dokumen ditemukan dalam basis data dan statusnya telah `SELESAI`. Kondisi lain menghasilkan status `INVALID` dengan pesan bahwa dokumen tidak terdaftar atau belum disahkan.

## Implementasi Frontend

Arsitektur frontend SIADESA mengadopsi pendekatan *thin server* di mana Laravel berfungsi secara eksklusif sebagai *view engine* tanpa menjalankan logika bisnis apa pun [@laravel2024docs]. Seluruh interaksi data dilakukan melalui panggilan API ke backend Express.js menggunakan klien HTTP berbasis `fetch`.

### Klien API

Modul `api.js` mengimplementasikan klien HTTP tipis yang membungkus `fetch` API dengan fungsionalitas injeksi token otomatis, penanganan respons, dan pembersihan token kedaluwarsa:

```javascript
const TOKEN_KEY = 'siadesa_token';

async function request(path, { method = 'GET', body = null, isForm = false } = {}) {
  const headers = {};
  const token = getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;

  let payload = body;
  if (body && !isForm) {
    headers['Content-Type'] = 'application/json';
    payload = JSON.stringify(body);
  }

  let response;
  try {
    response = await fetch(BASE_URL + path, { method, headers, body: payload });
  } catch (networkError) {
    return { success: false, message: 'Tidak dapat terhubung ke server API.' };
  }

  if (response.status === 401 && token) {
    clearToken();
  }

  // ...
  return { success: true, ...data };
}
```

Klien API menyediakan *shorthand methods* untuk setiap metode HTTP (`get`, `post`, `put`, `patch`, `del`) serta dua metode khusus untuk pengiriman `FormData` (`postForm`, `putForm`) yang digunakan pada fitur unggah berkas.

### Manajemen State dengan SiadesaStore

Kelas `SiadesaStore` berperan sebagai pusat manajemen *state* aplikasi, mengenkapsulasi seluruh operasi data dan menyediakan antarmuka yang konsisten bagi komponen-komponen Alpine.js [@alpinejs2024docs]. Objek *cache* internal menyimpan data layanan, penduduk, pengajuan, dan petugas yang telah dimuat dari API:

```javascript
class SiadesaStore {
  constructor() {
    this.cache = {
      services: [],
      residents: [],
      applications: [],
      approvals: [],
      staff: [],
      applicationDetails: {},
      site: null
    };
    this.ready = false;
  }

  async bootstrap() {
    await Promise.all([this.loadSiteSettings(), this.fetchServices()]);
    if (getToken()) {
      const res = await api.get('/api/auth/me');
      if (res.success && res.user) {
        this._setSession(res.user);
      } else {
        clearToken();
        localStorage.removeItem('siadesa_auth_session');
      }
    }
    this.ready = true;
  }
}
```

Metode `bootstrap()` dipanggil saat aplikasi pertama kali dimuat, memuat pengaturan situs dan katalog layanan secara paralel, kemudian memulihkan sesi pengguna jika token JWT tersedia. Metode-metode *mapper* internal (`_mapUser`, `_mapApplication`, `_mapResident`, `_mapService`, `_mapStaff`) mentransformasikan respons API ke dalam format yang dioptimalkan untuk konsumsi oleh komponen tampilan.

### Tata Letak Berbasis Peran

Dua *layout* Blade utama digunakan untuk memisahkan konteks publik dan autentikasi:

1. **`public.blade.php`** -- digunakan untuk halaman-halaman yang dapat diakses tanpa login seperti beranda, katalog layanan, pelacakan, dan verifikasi dokumen.
2. **`app.blade.php`** -- digunakan untuk *dashboard* terautentikasi dengan *sidebar* navigasi yang disesuaikan berdasarkan peran pengguna (Warga, Admin, Kepala Desa).

Reaktivitas antarmuka diimplementasikan menggunakan direktif Alpine.js `x-data`, `x-show`, `x-bind`, dan `x-on` yang terikat langsung pada metode-metode `SiadesaStore`. Desain responsif dibangun menggunakan kelas utilitas Tailwind CSS 4.x [@tailwindcss2024docs] yang memastikan antarmuka optimal pada perangkat *desktop* maupun *mobile*.

### Controller Laravel

`SiadesaController` pada sisi Laravel terdiri atas 18 metode yang seluruhnya hanya mengembalikan tampilan Blade tanpa logika bisnis:

```php
public function dashboard() {
    return view('siadesa.admin.dashboard');
}
```

Pendekatan ini secara tegas memisahkan tanggung jawab rendering tampilan (Laravel) dari pemrosesan data (Express.js), menghasilkan arsitektur yang modular dan mudah dipelihara.

## Implementasi Deployment

Sistem SIADESA di-*deploy* ke Virtual Private Server (VPS) menggunakan skrip otomatis `deploy.sh` yang mengorkestrasikan seluruh komponen infrastruktur. Konfigurasi server produksi menggunakan dua domain terpisah: domain utama untuk frontend Laravel dan subdomain API untuk backend Express.js.

### Komponen Infrastruktur

Tumpukan teknologi pada lingkungan produksi terdiri atas:

| No. | Komponen | Fungsi |
|-----|----------|--------|
| 1 | Nginx | *Reverse proxy* dan *web server* [@nginx2024docs] |
| 2 | PHP-FPM | *FastCGI process manager* untuk Laravel |
| 3 | PM2 | *Process manager* untuk Node.js backend [@pm2docs2024] |
| 4 | PostgreSQL | Sistem manajemen basis data |
| 5 | Let's Encrypt | Sertifikat SSL/TLS otomatis |

### Konfigurasi Nginx

Nginx dikonfigurasi sebagai *reverse proxy* yang mengarahkan permintaan ke dua *upstream* berbeda. Permintaan ke domain utama dilayani oleh PHP-FPM untuk merender halaman Blade, sedangkan permintaan ke subdomain API diteruskan ke proses Node.js yang dikelola oleh PM2. Konfigurasi SSL ditangani secara otomatis oleh Certbot untuk memperoleh dan memperbarui sertifikat Let's Encrypt.

### Manajemen Proses Backend

PM2 digunakan untuk menjalankan server Express.js sebagai *daemon* dengan fitur *auto-restart* dan pemantauan proses [@pm2docs2024]. Konfigurasi PM2 memastikan backend API tetap berjalan meskipun terjadi *crash* dan secara otomatis dimulai ulang saat server di-*reboot*.

Skrip *deployment* sepanjang 296 baris mengotomasi seluruh proses mulai dari instalasi dependensi, konfigurasi basis data, kompilasi aset frontend, hingga pengaturan *virtual host* Nginx dan perolehan sertifikat SSL. Pendekatan otomatis ini meminimalkan risiko kesalahan konfigurasi manual dan memastikan reprodusibilitas proses *deployment*.
