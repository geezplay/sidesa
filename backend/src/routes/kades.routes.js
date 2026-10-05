import { Router } from 'express';
import { getKadesApprovals, decideApproval } from '../controllers/kades.controller.js';
import { authenticate, requireRole } from '../middlewares/auth.js';

const router = Router();

router.use(authenticate, requireRole('KADES'));

router.get('/approvals', getKadesApprovals);
router.patch('/approvals/:id', decideApproval);

export default router;
