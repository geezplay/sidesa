import { prisma } from '../config/database.js';
import { v4 as uuidv4 } from 'uuid';

// Antrean Approval Kades
export const getKadesApprovals = async (req, res, next) => {
  try {
    const user = req.user;

    const apps = await prisma.application.findMany({
      where: {
        village_id: user.village_id,
        status: 'MENUNGGU_PERSETUJUAN'
      },
      include: {
        service: true,
        resident: true,
        documents: true,
        histories: {
          orderBy: { created_at: 'asc' }
        }
      },
      orderBy: { created_at: 'asc' }
    });

    res.json({
      success: true,
      approvals: apps
    });
  } catch (error) {
    next(error);
  }
};

// Keputusan Approval / Verifikasi Akhir oleh Kepala Desa
export const decideApproval = async (req, res, next) => {
  try {
    const user = req.user;
    const { id } = req.params;
    const { action, notes } = req.body; // action: APPROVE / REJECT

    const app = await prisma.application.findFirst({
      where: { id, village_id: user.village_id },
      include: { service: true }
    });

    if (!app) {
      return res.status(404).json({ success: false, message: 'Permohonan tidak ditemukan.' });
    }

    if (action === 'APPROVE') {
      const now = new Date();
      const year = now.getFullYear();
      const seq = Math.floor(100 + Math.random() * 899);
      const letter_number = `500/${seq}/${app.service.code}/X/${year}`;
      const qr_uuid = `SIADESA-DOC-${year}1005-${uuidv4().substring(0, 8).toUpperCase()}`;

      const updated = await prisma.application.update({
        where: { id },
        data: {
          status: 'SELESAI', // Langsung selesai & terbit surat
          approval_notes: notes || 'Disetujui dan disahkan secara digital oleh Kepala Desa.',
          letter_number,
          qr_uuid
        }
      });

      await prisma.statusHistory.create({
        data: {
          application_id: id,
          status: 'SELESAI',
          actor_name: `${user.name} (Kepala Desa)`,
          notes: `Permohonan disetujui & disahkan. Surat resmi nomor ${letter_number} berhasil diterbitkan.`
        }
      });

      return res.json({
        success: true,
        message: 'Permohonan berhasil disetujui dan disahkan oleh Kepala Desa.',
        application: updated
      });
    } else if (action === 'REJECT') {
      if (!notes) {
        return res.status(400).json({ success: false, message: 'Alasan penolakan dari Kepala Desa wajib diisi!' });
      }

      const updated = await prisma.application.update({
        where: { id },
        data: {
          status: 'DITOLAK',
          approval_notes: notes
        }
      });

      await prisma.statusHistory.create({
        data: {
          application_id: id,
          status: 'DITOLAK',
          actor_name: `${user.name} (Kepala Desa)`,
          notes: `Permohonan ditolak oleh Kepala Desa: ${notes}`
        }
      });

      return res.json({
        success: true,
        message: 'Permohonan telah ditolak oleh Kepala Desa.',
        application: updated
      });
    } else {
      return res.status(400).json({ success: false, message: 'Aksi tidak valid.' });
    }
  } catch (error) {
    next(error);
  }
};
