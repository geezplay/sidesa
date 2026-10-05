import { Router } from 'express';
import {
  createApplication,
  getMyApplications,
  resubmitApplication,
  getApplicationById
} from '../controllers/application.controller.js';
import { authenticate, requireRole } from '../middlewares/auth.js';
import { upload } from '../config/multer.js';

const router = Router();

// Detail pengajuan (warga pemilik / admin / kades)
router.get('/my', authenticate, requireRole('WARGA'), getMyApplications);
router.get('/:id', authenticate, getApplicationById);

// Khusus warga
router.post('/', authenticate, requireRole('WARGA'), upload.array('documents', 5), createApplication);
router.put('/:id/resubmit', authenticate, requireRole('WARGA'), upload.array('documents', 5), resubmitApplication);

export default router;
