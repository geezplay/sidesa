import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { prisma } from '../config/database.js';

export const register = async (req, res, next) => {
  try {
    const { nik, name, phone, password } = req.body;

    if (!nik || nik.length !== 16 || isNaN(nik)) {
      return res.status(400).json({
        success: false,
        message: 'NIK wajib 16 digit angka valid (Aturan BR-01)!'
      });
    }

    if (!name || !phone || !password) {
      return res.status(400).json({
        success: false,
        message: 'Semua kolom wajib diisi!'
      });
    }

    const existingUser = await prisma.user.findFirst({
      where: { nik }
    });

    if (existingUser) {
      return res.status(400).json({
        success: false,
        message: 'NIK ini sudah memiliki akun terdaftar di website! Silakan langsung login.'
      });
    }

    // Wajib terdaftar di data penduduk desa terlebih dahulu
    const residentRecord = await prisma.resident.findFirst({
      where: { nik }
    });

    if (!residentRecord) {
      return res.status(400).json({
        success: false,
        message: 'NIK tidak ditemukan dalam Data Penduduk Desa Sukamaju! Anda harus terdaftar sebagai penduduk desa terlebih dahulu. Hubungi Kantor Desa untuk pendataan penduduk.'
      });
    }

    // Default village (Sukamaju)
    const village = await prisma.village.findFirst();
    if (!village) {
      return res.status(500).json({ success: false, message: 'Data desa belum diinisialisasi.' });
    }

    const password_hash = await bcrypt.hash(password, 10);

    const newUser = await prisma.user.create({
      data: {
        village_id: village.id,
        nik,
        name: residentRecord.full_name || name,
        phone,
        password_hash,
        role: 'WARGA'
      }
    });

    // Kaitkan user ke resident
    await prisma.resident.update({
      where: { id: residentRecord.id },
      data: { user_id: newUser.id }
    });

    const token = jwt.sign(
      { userId: newUser.id, role: newUser.role },
      process.env.JWT_SECRET || 'siadesa_jwt_secret_production_ready_2026_super_secure',
      { expiresIn: process.env.JWT_EXPIRES_IN || '12h' }
    );

    res.status(201).json({
      success: true,
      message: 'Pendaftaran berhasil. Silakan lengkapi biodata kependudukan Anda.',
      token,
      user: {
        id: newUser.id,
        nik: newUser.nik,
        name: newUser.name,
        role: newUser.role,
        is_profile_complete: false
      }
    });
  } catch (error) {
    next(error);
  }
};

export const login = async (req, res, next) => {
  try {
    const { loginId, password } = req.body;

    if (!loginId || !password) {
      return res.status(400).json({
        success: false,
        message: 'ID Pengguna (NIK/Username) dan kata sandi wajib diisi!'
      });
    }

    const trimmed = loginId.trim();

    const user = await prisma.user.findFirst({
      where: {
        OR: [
          { nik: trimmed },
          { email: trimmed }
        ]
      },
      include: {
        resident: true,
        village: true
      }
    });

    if (!user) {
      return res.status(400).json({
        success: false,
        message: 'ID Pengguna atau kata sandi salah!'
      });
    }

    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return res.status(400).json({
        success: false,
        message: 'ID Pengguna atau kata sandi salah!'
      });
    }

    const token = jwt.sign(
      { userId: user.id, role: user.role },
      process.env.JWT_SECRET || 'siadesa_jwt_secret_production_ready_2026_super_secure',
      { expiresIn: process.env.JWT_EXPIRES_IN || '12h' }
    );

    const isProfileComplete = !!(
      user.resident &&
      user.resident.family_card_no &&
      user.resident.birth_place &&
      user.resident.birth_date &&
      user.resident.address &&
      user.resident.rt &&
      user.resident.rw
    );

    res.json({
      success: true,
      message: 'Login berhasil.',
      token,
      user: {
        id: user.id,
        nik: user.nik,
        name: user.name,
        role: user.role,
        is_profile_complete: isProfileComplete,
        resident: user.resident,
        village: user.village
      }
    });
  } catch (error) {
    next(error);
  }
};

export const getMe = async (req, res, next) => {
  try {
    const user = req.user;
    const isProfileComplete = !!(
      user.resident &&
      user.resident.family_card_no &&
      user.resident.birth_place &&
      user.resident.birth_date &&
      user.resident.address &&
      user.resident.rt &&
      user.resident.rw
    );

    res.json({
      success: true,
      user: {
        id: user.id,
        nik: user.nik,
        name: user.name,
        role: user.role,
        phone: user.phone,
        email: user.email,
        is_profile_complete: isProfileComplete,
        resident: user.resident,
        village: user.village
      }
    });
  } catch (error) {
    next(error);
  }
};

export const updateProfile = async (req, res, next) => {
  try {
    const user = req.user;
    const {
      family_card_no,
      full_name,
      birth_place,
      birth_date,
      gender,
      address,
      rt,
      rw,
      religion,
      marital_status,
      occupation,
      phone_number
    } = req.body;

    // Validasi field wajib sesuai PRD BR-02
    if (!family_card_no || family_card_no.length !== 16) {
      return res.status(400).json({
        success: false,
        message: 'Nomor Kartu Keluarga (KK) wajib 16 digit angka valid!'
      });
    }

    if (!full_name || !birth_place || !birth_date || !address || !rt || !rw) {
      return res.status(400).json({
        success: false,
        message: 'Semua kolom bertanda wajib (*) harus dilengkapi (Aturan BR-02)!'
      });
    }

    // Upsert resident profile
    const resident = await prisma.resident.upsert({
      where: { nik: user.nik },
      update: {
        family_card_no,
        full_name,
        birth_place,
        birth_date: new Date(birth_date),
        gender: gender || 'Laki-Laki',
        address,
        rt,
        rw,
        religion: religion || 'Islam',
        marital_status: marital_status || 'Kawin',
        occupation: occupation || 'Wiraswasta',
        phone_number: phone_number || user.phone
      },
      create: {
        village_id: user.village_id,
        user_id: user.id,
        nik: user.nik,
        family_card_no,
        full_name,
        birth_place,
        birth_date: new Date(birth_date),
        gender: gender || 'Laki-Laki',
        address,
        rt,
        rw,
        religion: religion || 'Islam',
        marital_status: marital_status || 'Kawin',
        occupation: occupation || 'Wiraswasta',
        phone_number: phone_number || user.phone
      }
    });

    // Update nama user
    await prisma.user.update({
      where: { id: user.id },
      data: { name: full_name }
    });

    res.json({
      success: true,
      message: 'Biodata kependudukan berhasil dilengkapi dan diverifikasi sistem!',
      resident
    });
  } catch (error) {
    next(error);
  }
};
