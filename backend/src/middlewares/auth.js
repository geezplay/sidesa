import jwt from 'jsonwebtoken';
import { prisma } from '../config/database.js';

export const authenticate = async (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Akses ditolak: Token autentikasi tidak ditemukan.'
      });
    }

    const token = authHeader.split(' ')[1];
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'siadesa_jwt_secret_production_ready_2026_super_secure');

    const user = await prisma.user.findUnique({
      where: { id: decoded.userId },
      include: {
        resident: true,
        village: true
      }
    });

    if (!user || !user.is_active) {
      return res.status(401).json({
        success: false,
        message: 'Sesi akun tidak valid atau akun telah dinonaktifkan.'
      });
    }

    req.user = user;
    next();
  } catch (error) {
    return res.status(401).json({
      success: false,
      message: 'Token kedaluwarsa atau tidak valid.',
      error: error.message
    });
  }
};

export const requireRole = (...roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ success: false, message: 'Tidak terotentikasi.' });
    }

    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: `Akses ditolak: Anda tidak memiliki izin untuk role ${roles.join(' atau ')}.`
      });
    }

    next();
  };
};
