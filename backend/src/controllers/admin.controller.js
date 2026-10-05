import { prisma } from '../config/database.js';
import bcrypt from 'bcryptjs';

// Dashboard Statistik Admin
export const getAdminDashboard = async (req, res, next) => {
  try {
    const user = req.user;

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

    const recentApps = await prisma.application.findMany({
      where: { village_id: user.village_id },
      include: {
        service: true,
        user: { select: { name: true, nik: true } }
      },
      orderBy: { created_at: 'desc' },
      take: 5
    });

    res.json({
      success: true,
      stats: {
        totalPenduduk,
        totalApplications,
        needVerify,
        waitingKades,
        completed
      },
      recentApps
    });
  } catch (error) {
    next(error);
  }
};

// Semua pengajuan di desa (untuk statistik & arsip)
export const listApplications = async (req, res, next) => {
  try {
    const user = req.user;
    const apps = await prisma.application.findMany({
      where: { village_id: user.village_id },
      include: {
        service: true,
        resident: true,
        user: { select: { name: true, nik: true } },
        documents: true,
        histories: { orderBy: { created_at: 'asc' } }
      },
      orderBy: { created_at: 'desc' }
    });
    res.json({ success: true, applications: apps });
  } catch (error) {
    next(error);
  }
};

// Antrean Verifikasi Berkas
export const getVerifications = async (req, res, next) => {
  try {
    const user = req.user;

    const apps = await prisma.application.findMany({
      where: {
        village_id: user.village_id,
        status: { in: ['DIAJUKAN', 'PERLU_PERBAIKAN'] }
      },
      include: {
        service: true,
        user: { select: { name: true, nik: true } },
        resident: true,
        documents: true
      },
      orderBy: { created_at: 'asc' }
    });

    res.json({ success: true, applications: apps });
  } catch (error) {
    next(error);
  }
};

// Verifikasi Berkas (Admin Loket)
export const verifyApplication = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;
    const { decision, notes } = req.body; // decision: APPROVE / REVISE / REJECT

    const app = await prisma.application.findFirst({
      where: { id, village_id: user.village_id }
    });

    if (!app) {
      return res.status(404).json({ success: false, message: 'Permohonan tidak ditemukan.' });
    }

    let newStatus = null;
    let actorNote = '';

    if (decision === 'APPROVE') {
      // Semua layanan WAJIB diteruskan ke Kepala Desa untuk verifikasi akhir
      newStatus = 'MENUNGGU_PERSETUJUAN';
      actorNote = notes || 'Berkas e-KTP, KK, dan Syarat Khusus dinyatakan valid. Diteruskan ke Kepala Desa untuk verifikasi dan pengesahan resmi.';
    } else if (decision === 'REVISE') {
      if (!notes) {
        return res.status(400).json({ success: false, message: 'Catatan perbaikan berkas wajib diisi!' });
      }
      newStatus = 'PERLU_PERBAIKAN';
      actorNote = notes;
    } else if (decision === 'REJECT') {
      if (!notes) {
        return res.status(400).json({ success: false, message: 'Alasan penolakan wajib dicatat!' });
      }
      newStatus = 'DITOLAK';
      actorNote = notes;
    } else {
      return res.status(400).json({ success: false, message: 'Keputusan tidak valid.' });
    }

    const updated = await prisma.application.update({
      where: { id },
      data: {
        status: newStatus,
        verifier_notes: actorNote
      }
    });

    await prisma.statusHistory.create({
      data: {
        application_id: id,
        status: newStatus,
        actor_name: `${user.name} (Admin Desa)`,
        notes: actorNote
      }
    });

    res.json({
      success: true,
      message: decision === 'APPROVE'
        ? 'Berkas valid dan diteruskan ke Kepala Desa untuk verifikasi akhir.'
        : 'Tindakan verifikasi berhasil dicatat.',
      application: updated
    });
  } catch (error) {
    next(error);
  }
};

// CRUD Master Penduduk
export const listResidents = async (req, res, next) => {
  try {
    const user = req.user;
    const { search } = req.query;

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
      include: {
        user: { select: { id: true, role: true, is_active: true } }
      },
      orderBy: { full_name: 'asc' }
    });

    res.json({ success: true, residents });
  } catch (error) {
    next(error);
  }
};

export const updateResident = async (req, res, next) => {
  try {
    const user = req.user;
    const { nik } = req.params;
    const data = req.body;

    const resident = await prisma.resident.findFirst({
      where: { nik, village_id: user.village_id }
    });

    if (!resident) {
      return res.status(404).json({ success: false, message: 'Data penduduk tidak ditemukan.' });
    }

    const updated = await prisma.resident.update({
      where: { id: resident.id },
      data: {
        family_card_no: data.kk || data.family_card_no,
        full_name: data.name || data.full_name,
        birth_place: data.birth_place,
        birth_date: data.birth_date ? new Date(data.birth_date) : undefined,
        gender: data.gender,
        address: data.address,
        rt: data.rt,
        rw: data.rw,
        religion: data.religion,
        marital_status: data.marital || data.marital_status,
        occupation: data.job || data.occupation,
        phone_number: data.phone || data.phone_number
      }
    });

    // Update nama user jika ada akun terkait
    if (resident.user_id && (data.name || data.full_name)) {
      await prisma.user.update({
        where: { id: resident.user_id },
        data: { name: data.name || data.full_name }
      });
    }

    res.json({
      success: true,
      message: 'Data penduduk berhasil diperbarui!',
      resident: updated
    });
  } catch (error) {
    next(error);
  }
};

export const importResidents = async (req, res, next) => {
  try {
    const user = req.user;
    const { residents } = req.body;

    if (!residents || !Array.isArray(residents) || residents.length === 0) {
      return res.status(400).json({ success: false, message: 'Daftar data penduduk tidak valid.' });
    }

    let imported = 0;
    let updated = 0;

    for (const r of residents) {
      if (!r.nik || r.nik.length !== 16) continue;

      const existing = await prisma.resident.findFirst({
        where: { nik: r.nik, village_id: user.village_id }
      });

      if (existing) {
        await prisma.resident.update({
          where: { id: existing.id },
          data: {
            family_card_no: r.kk || r.family_card_no || existing.family_card_no,
            full_name: r.name || r.full_name || existing.full_name,
            birth_place: r.birth_place || existing.birth_place,
            birth_date: r.birth_date ? new Date(r.birth_date) : existing.birth_date,
            gender: r.gender || existing.gender,
            address: r.address || existing.address,
            rt: r.rt || existing.rt,
            rw: r.rw || existing.rw,
            religion: r.religion || existing.religion,
            marital_status: r.marital || r.marital_status || existing.marital_status,
            occupation: r.job || r.occupation || existing.occupation,
            phone_number: r.phone || r.phone_number || existing.phone_number
          }
        });
        updated++;
      } else {
        await prisma.resident.create({
          data: {
            village_id: user.village_id,
            nik: r.nik,
            family_card_no: r.kk || r.family_card_no || '',
            full_name: r.name || r.full_name || 'Warga Desa',
            birth_place: r.birth_place || 'Bogor',
            birth_date: r.birth_date ? new Date(r.birth_date) : new Date('1990-01-01'),
            gender: r.gender || 'Laki-Laki',
            address: r.address || '',
            rt: r.rt || '001',
            rw: r.rw || '001',
            religion: r.religion || 'Islam',
            marital_status: r.marital || r.marital_status || 'Kawin',
            occupation: r.job || r.occupation || 'Wiraswasta',
            phone_number: r.phone || r.phone_number || ''
          }
        });
        imported++;
      }
    }

    res.json({
      success: true,
      message: `Import selesai: ${imported} data baru ditambahkan, ${updated} data diperbarui.`,
      imported,
      updated
    });
  } catch (error) {
    next(error);
  }
};

// CRUD Master Layanan
export const listServices = async (req, res, next) => {
  try {
    const user = req.user;

    const services = await prisma.service.findMany({
      where: { village_id: user.village_id },
      include: { requirements: true }
    });

    res.json({ success: true, services });
  } catch (error) {
    next(error);
  }
};

export const createService = async (req, res, next) => {
  try {
    const user = req.user;
    const { code, name, category, description, estimation_days, requirements } = req.body;

    if (!code || !name || !description) {
      return res.status(400).json({ success: false, message: 'Kode, nama, dan deskripsi layanan wajib diisi!' });
    }

    const svc = await prisma.service.create({
      data: {
        village_id: user.village_id,
        code: code.toUpperCase(),
        name,
        category: category || 'Surat Keterangan',
        description,
        estimation_days: estimation_days ? parseInt(estimation_days) : 1,
        requires_approval: true // Selalu wajib Kades
      }
    });

    if (requirements && Array.isArray(requirements)) {
      for (const reqName of requirements) {
        await prisma.serviceRequirement.create({
          data: {
            service_id: svc.id,
            name: reqName,
            is_mandatory: true
          }
        });
      }
    }

    res.status(201).json({ success: true, message: 'Jenis layanan baru berhasil dibuat.', service: svc });
  } catch (error) {
    next(error);
  }
};

export const updateService = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;
    const { code, name, category, description, estimation_days, is_active, requirements } = req.body;

    const existing = await prisma.service.findFirst({
      where: { id, village_id: user.village_id }
    });

    if (!existing) {
      return res.status(404).json({ success: false, message: 'Layanan tidak ditemukan!' });
    }

    const svc = await prisma.service.update({
      where: { id },
      data: {
        code: code ? code.toUpperCase() : undefined,
        name: name || undefined,
        category: category || undefined,
        description: description || undefined,
        estimation_days: estimation_days ? parseInt(estimation_days) : undefined,
        is_active: is_active !== undefined ? !!is_active : undefined,
        requires_approval: true // Selalu wajib diverifikasi Kepala Desa
      }
    });

    // Ganti seluruh daftar persyaratan dokumen
    if (requirements && Array.isArray(requirements)) {
      await prisma.serviceRequirement.deleteMany({
        where: { service_id: svc.id }
      });
      for (const reqName of requirements) {
        if (reqName && reqName.trim()) {
          await prisma.serviceRequirement.create({
            data: {
              service_id: svc.id,
              name: reqName.trim(),
              is_mandatory: true
            }
          });
        }
      }
    }

    const updated = await prisma.service.findUnique({
      where: { id: svc.id },
      include: { requirements: true }
    });

    res.json({
      success: true,
      message: `Konfigurasi ${updated.name} berhasil diperbarui!`,
      service: updated
    });
  } catch (error) {
    next(error);
  }
};

// Pengaturan Website Desa (Admin dapat mengatur konten website)
export const getSettings = async (req, res, next) => {
  try {
    const user = req.user;

    const settings = await prisma.setting.findMany({
      where: { village_id: user.village_id }
    });

    const village = await prisma.village.findUnique({
      where: { id: user.village_id }
    });

    res.json({ success: true, village, settings });
  } catch (error) {
    next(error);
  }
};

export const updateSettings = async (req, res, next) => {
  try {
    const user = req.user;
    const { settings, village } = req.body;

    // 1. Update Village Profile
    if (village) {
      await prisma.village.update({
        where: { id: user.village_id },
        data: {
          address: village.address,
          phone: village.phone,
          email: village.email,
          official_head: village.official_head,
          official_nip: village.official_nip,
          service_hours: village.service_hours
        }
      });
    }

    // 2. Update Dynamic Content (banner, announcement, hero)
    if (settings && Array.isArray(settings)) {
      for (const s of settings) {
        await prisma.setting.upsert({
          where: {
            village_id_key: {
              village_id: user.village_id,
              key: s.key
            }
          },
          update: { value: s.value },
          create: {
            village_id: user.village_id,
            key: s.key,
            value: s.value
          }
        });
      }
    }

    res.json({
      success: true,
      message: 'Profil desa dan konten website berhasil diperbarui!'
    });
  } catch (error) {
    next(error);
  }
};

// Tambah satu data penduduk
export const createResident = async (req, res, next) => {
  try {
    const user = req.user;
    const r = req.body;

    if (!r.nik || r.nik.length !== 16) {
      return res.status(400).json({ success: false, message: 'NIK wajib 16 digit angka!' });
    }
    if (!r.name) {
      return res.status(400).json({ success: false, message: 'Nama lengkap wajib diisi!' });
    }

    const existing = await prisma.resident.findFirst({ where: { nik: r.nik } });
    if (existing) {
      return res.status(400).json({ success: false, message: 'NIK sudah terdaftar di data penduduk!' });
    }

    const created = await prisma.resident.create({
      data: {
        village_id: user.village_id,
        nik: r.nik,
        family_card_no: r.kk || '',
        full_name: r.name,
        birth_place: r.birth_place || 'Bogor',
        birth_date: r.birth_date ? new Date(r.birth_date) : new Date('1990-01-01'),
        gender: r.gender || 'Laki-Laki',
        address: r.address || '',
        rt: r.rt || '001',
        rw: r.rw || '001',
        religion: r.religion || 'Islam',
        marital_status: r.marital || 'Kawin',
        occupation: r.job || 'Wiraswasta',
        phone_number: r.phone || ''
      }
    });

    res.status(201).json({ success: true, message: 'Data penduduk berhasil ditambahkan.', resident: created });
  } catch (error) {
    next(error);
  }
};

// ===== Manajemen Akun Petugas (Admin & Kepala Desa) =====
export const listStaff = async (req, res, next) => {
  try {
    const user = req.user;
    const staff = await prisma.user.findMany({
      where: { village_id: user.village_id, role: { in: ['ADMIN', 'KADES'] } },
      select: { id: true, nik: true, name: true, email: true, phone: true, role: true, is_active: true, created_at: true },
      orderBy: { created_at: 'asc' }
    });
    res.json({ success: true, staff });
  } catch (error) {
    next(error);
  }
};

export const createStaff = async (req, res, next) => {
  try {
    const user = req.user;
    const { username, name, nik, phone, email, password, role } = req.body;

    if (!username || !name || !password) {
      return res.status(400).json({ success: false, message: 'Username, Nama, dan Kata Sandi wajib diisi!' });
    }

    const targetRole = role === 'kades' ? 'KADES' : 'ADMIN';

    if (targetRole === 'KADES') {
      const existingKades = await prisma.user.findFirst({
        where: { village_id: user.village_id, role: 'KADES' }
      });
      if (existingKades) {
        return res.status(400).json({ success: false, message: 'Akun Kepala Desa hanya boleh satu. Perbarui akun yang ada.' });
      }
    }

    const dup = await prisma.user.findFirst({
      where: { OR: [{ nik: username }, { email: email || username }] }
    });
    if (dup) {
      return res.status(400).json({ success: false, message: 'Username/Email sudah digunakan!' });
    }

    const password_hash = await bcrypt.hash(password, 10);
    const created = await prisma.user.create({
      data: {
        village_id: user.village_id,
        nik: username,
        name,
        email: email || `${username}@desasukamaju.go.id`,
        phone: phone || '',
        password_hash,
        role: targetRole
      }
    });

    res.status(201).json({
      success: true,
      message: 'Akun petugas berhasil dibuat.',
      staff: { id: created.id, nik: created.nik, name: created.name, role: created.role, email: created.email, phone: created.phone }
    });
  } catch (error) {
    next(error);
  }
};

export const updateStaff = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;
    const { name, nik, phone, email, password } = req.body;

    const target = await prisma.user.findFirst({
      where: { id, village_id: user.village_id, role: { in: ['ADMIN', 'KADES'] } }
    });
    if (!target) {
      return res.status(404).json({ success: false, message: 'Akun tidak ditemukan.' });
    }

    const data = {
      name: name || target.name,
      phone: phone !== undefined ? phone : target.phone,
      email: email || target.email
    };
    if (password) {
      data.password_hash = await bcrypt.hash(password, 10);
    }

    const updated = await prisma.user.update({ where: { id }, data });
    res.json({
      success: true,
      message: 'Data akun berhasil diperbarui.',
      staff: { id: updated.id, nik: updated.nik, name: updated.name, role: updated.role, email: updated.email, phone: updated.phone }
    });
  } catch (error) {
    next(error);
  }
};

export const deleteStaff = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;

    if (id === user.id) {
      return res.status(400).json({ success: false, message: 'Anda tidak dapat menghapus akun Anda sendiri.' });
    }

    const target = await prisma.user.findFirst({
      where: { id, village_id: user.village_id, role: { in: ['ADMIN', 'KADES'] } }
    });
    if (!target) {
      return res.status(404).json({ success: false, message: 'Akun tidak ditemukan.' });
    }

    if (target.role === 'ADMIN') {
      const adminCount = await prisma.user.count({
        where: { village_id: user.village_id, role: 'ADMIN' }
      });
      if (adminCount <= 1) {
        return res.status(400).json({ success: false, message: 'Minimal harus ada 1 akun Admin Desa.' });
      }
    }

    await prisma.user.delete({ where: { id } });
    res.json({ success: true, message: 'Akun berhasil dihapus.' });
  } catch (error) {
    next(error);
  }
};
