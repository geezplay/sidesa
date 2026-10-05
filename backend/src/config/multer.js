import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { v4 as uuidv4 } from 'uuid';

const uploadDir = path.resolve('storage', 'uploads');

if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const uniqueName = `${Date.now()}-${uuidv4()}${ext}`;
    cb(null, uniqueName);
  }
});

const fileFilter = (req, file, cb) => {
  const allowedMime = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
  if (allowedMime.includes(file.mimetype)) {
    cb(null, true);
  } else {
    cb(new Error('Format file tidak didukung! Hanya diperbolehkan PDF, JPG, JPEG, dan PNG.'), false);
  }
};

export const upload = multer({
  storage,
  limits: {
    fileSize: 2 * 1024 * 1024 // 2 MB max per PRD §35
  },
  fileFilter
});
