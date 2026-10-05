import { Router } from 'express';
import {
  getPublicServices,
  trackApplication,
  verifyDocumentQR,
  getPublicWebsiteInfo
} from '../controllers/public.controller.js';

const router = Router();

router.get('/info', getPublicWebsiteInfo);
router.get('/services', getPublicServices);
router.get('/tracking/:code', trackApplication);
router.get('/verify-doc/:code', verifyDocumentQR);

export default router;
