import { prisma } from '../config/database.js';

// Katalog Layanan Publik
export const getPublicServices = async (req, res, next) => {
  try {
    const services = await prisma.service.findMany({
      where: { is_active: true },
      include: { requirements: true },
      orderBy: { code: 'asc' }
    });

    res.json({ success: true, services });
  } catch (error) {
    next(error);
  }
};

// Tracking Tiket (ADM-YYYYMMDD-XXXXXX)
export const trackApplication = async (req, res, next) => {
  try {
    const { code } = req.params;

    const app = await prisma.application.findFirst({
      where: {
        tracking_number: code.trim().toUpperCase()
      },
      include: {
        service: true,
        histories: {
          orderBy: { created_at: 'asc' }
        }
      }
    });

    if (!app) {
      return res.status(404).json({
        success: false,
        message: 'Nomor pengajuan tidak ditemukan dalam sistem.'
      });
    }

    res.json({
      success: true,
      application: app
    });
  } catch (error) {
    next(error);
  }
};

// Verifikasi Keaslian Dokumen QR Code Publik (UU PDP: Disensor)
export const verifyDocumentQR = async (req, res, next) => {
  try {
    const { code } = req.params;

    const app = await prisma.application.findFirst({
      where: { qr_uuid: code.trim() },
      include: {
        service: true,
        resident: true,
        village: true
      }
    });

    if (!app || app.status !== 'SELESAI') {
      return res.status(404).json({
        success: false,
        valid: false,
        message: 'Dokumen tidak terdaftar atau belum disahkan oleh Kepala Desa.'
      });
    }

    // Nama pemohon disamarkan demi privasi (UU PDP No. 27/2022)
    const rawName = app.resident ? app.resident.full_name : 'Warga';
    const sensoredName = rawName
      .split(' ')
      .map(w => w[0] + '***')
      .join(' ');

    res.json({
      success: true,
      valid: true,
      document: {
        id: app.id,
        letter_number: app.letter_number,
        service_name: app.service.name,
        applicant_name_censored: sensoredName,
        issued_at: app.updated_at,
        signer_head: app.village.official_head,
        village_name: app.village.name,
        qr_uuid: app.qr_uuid,
        status: 'VALID'
      }
    });
  } catch (error) {
    next(error);
  }
};

// Info Website & Banner Beranda
export const getPublicWebsiteInfo = async (req, res, next) => {
  try {
    const village = await prisma.village.findFirst();
    const settings = await prisma.setting.findMany();

    const settingsMap = {};
    settings.forEach(s => {
      settingsMap[s.key] = s.value;
    });

    res.json({
      success: true,
      village,
      settings: settingsMap
    });
  } catch (error) {
    next(error);
  }
};
