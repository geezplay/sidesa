import { Router } from 'express';
import {
  getAdminDashboard,
  listApplications,
  getVerifications,
  verifyApplication,
  listResidents,
  createResident,
  updateResident,
  importResidents,
  listServices,
  createService,
  updateService,
  getSettings,
  updateSettings,
  listStaff,
  createStaff,
  updateStaff,
  deleteStaff
} from '../controllers/admin.controller.js';
import { authenticate, requireRole } from '../middlewares/auth.js';

const router = Router();

router.use(authenticate, requireRole('ADMIN'));

router.get('/dashboard', getAdminDashboard);
router.get('/applications', listApplications);
router.get('/verifications', getVerifications);
router.patch('/verifications/:id', verifyApplication);

router.get('/residents', listResidents);
router.post('/residents', createResident);
router.post('/residents/import', importResidents);
router.put('/residents/:nik', updateResident);

router.get('/services', listServices);
router.post('/services', createService);
router.put('/services/:id', updateService);

router.get('/settings', getSettings);
router.put('/settings', updateSettings);

// Manajemen akun petugas
router.get('/staff', listStaff);
router.post('/staff', createStaff);
router.put('/staff/:id', updateStaff);
router.delete('/staff/:id', deleteStaff);

export default router;
