import { prisma } from '../config/database.js';

export const createApplication = async (req, res, next) => {
  try {
    const user = req.user;
    const {
      service_id,
      purpose,
      business_name,
      business_type,
      business_address
    } = req.body;

    // Pastikan profil kependudukan sudah lengkap (PRD BR-02)
    if (!user.resident || !user.resident.family_card_no || !user.resident.address) {
      return res.status(403).json({
        success: false,
        message: 'Akses ditolak: Anda wajib melengkapi biodata kependudukan (KK, TTL, RT/RW) sebelum mengajukan permohonan surat (Aturan BR-02).'
      });
    }

    if (!service_id || !purpose) {
      return res.status(400).json({
        success: false,
        message: 'Jenis layanan dan keperluan surat wajib diisi!'
      });
    }

    // Generate Nomor Tiket Unik: ADM-YYYYMMDD-XXXXXX
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const dateStr = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}`;
    const randomHex = Math.floor(100000 + Math.random() * 900000);
    const tracking_number = `ADM-${dateStr}-${randomHex}`;

    const newApp = await prisma.application.create({
      data: {
        village_id: user.village_id,
        user_id: user.id,
        resident_id: user.resident.id,
        service_id,
        tracking_number,
        purpose,
        business_name: business_name || null,
        business_type: business_type || null,
        business_address: business_address || null,
        status: 'DIAJUKAN'
      },
      include: {
        service: true
      }
    });

    // Simpan dokumen jika ada file upload
    if (req.files && req.files.length > 0) {
      for (const file of req.files) {
        await prisma.applicationDocument.create({
          data: {
            application_id: newApp.id,
            file_name: file.originalname,
            file_path: file.path,
            mime_type: file.mimetype,
            file_size: file.size
          }
        });
      }
    }

    // Catat riwayat status
    await prisma.statusHistory.create({
      data: {
        application_id: newApp.id,
        status: 'DIAJUKAN',
        actor_name: `${user.name} (Pemohon)`,
        notes: 'Permohonan surat berhasil dikirimkan secara online.'
      }
    });

    res.status(201).json({
      success: true,
      message: 'Permohonan surat berhasil dikirim dan masuk antrean loket desa.',
      application: newApp
    });
  } catch (error) {
    next(error);
  }
};

// Ambil detail satu pengajuan (pemilik warga, admin, atau kades di desa yang sama)
export const getApplicationById = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;

    const app = await prisma.application.findFirst({
      where: { id },
      include: {
        service: true,
        resident: true,
        documents: true,
        histories: { orderBy: { created_at: 'asc' } },
        user: { select: { name: true, nik: true } }
      }
    });

    if (!app) {
      return res.status(404).json({ success: false, message: 'Permohonan tidak ditemukan.' });
    }

    // Warga hanya boleh melihat miliknya sendiri
    if (user.role === 'WARGA' && app.user_id !== user.id) {
      return res.status(403).json({ success: false, message: 'Akses ditolak.' });
    }

    // Admin & Kades hanya boleh melihat pengajuan di desa yang sama
    if (user.role !== 'WARGA' && app.village_id !== user.village_id) {
      return res.status(403).json({ success: false, message: 'Akses ditolak.' });
    }

    res.json({ success: true, application: app });
  } catch (error) {
    next(error);
  }
};

export const getMyApplications = async (req, res, next) => {
  try {
    const user = req.user;

    const apps = await prisma.application.findMany({
      where: { user_id: user.id },
      include: {
        service: true,
        documents: true,
        histories: {
          orderBy: { created_at: 'asc' }
        }
      },
      orderBy: { created_at: 'desc' }
    });

    res.json({
      success: true,
      applications: apps
    });
  } catch (error) {
    next(error);
  }
};

export const resubmitApplication = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;

    const app = await prisma.application.findFirst({
      where: { id, user_id: user.id }
    });

    if (!app) {
      return res.status(404).json({ success: false, message: 'Permohonan tidak ditemukan.' });
    }

    if (app.status !== 'PERLU_PERBAIKAN') {
      return res.status(400).json({
        success: false,
        message: 'Hanya permohonan berstatus PERLU PERBAIKAN yang dapat diajukan ulang.'
      });
    }

    const updated = await prisma.application.update({
      where: { id },
      data: {
        status: 'DIAJUKAN'
      }
    });

    await prisma.statusHistory.create({
      data: {
        application_id: id,
        status: 'DIAJUKAN',
        actor_name: `${user.name} (Pemohon)`,
        notes: 'Dokumen perbaikan telah diunggah kembali oleh pemohon.'
      }
    });

    res.json({
      success: true,
      message: 'Perbaikan berkas berhasil dikirimkan kembali.',
      application: updated
    });
  } catch (error) {
    next(error);
  }
};
