import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';

import authRoutes from './routes/auth.routes.js';
import applicationRoutes from './routes/application.routes.js';
import adminRoutes from './routes/admin.routes.js';
import kadesRoutes from './routes/kades.routes.js';
import publicRoutes from './routes/public.routes.js';
import { errorHandler } from './middlewares/errorHandler.js';

dotenv.config();

const app = express();

// Middleware Keamanan & CORS
app.use(helmet());
app.use(cors({
  origin: [
    process.env.FRONTEND_URL || 'http://localhost:8000',
    'http://127.0.0.1:8000'
  ],
  credentials: true
}));

app.use(morgan('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Rate limit khusus Login (Maks 10 percobaan / menit)
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

// Routing Modul API
app.use('/api/public', publicRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/applications', applicationRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/kades', kadesRoutes);

// Root Info
app.get('/', (req, res) => {
  res.json({
    success: true,
    name: 'SIADESA Backend API',
    version: '1.0.0',
    database: 'PostgreSQL + Prisma ORM',
    docs: {
      public: '/api/public',
      auth: '/api/auth',
      warga: '/api/applications',
      admin: '/api/admin',
      kades: '/api/kades'
    }
  });
});

// Global Error Handler
app.use(errorHandler);

export default app;
