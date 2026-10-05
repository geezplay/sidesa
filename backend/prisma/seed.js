import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log('Seeding database SIADESA di PostgreSQL...');

  // 1. Village Sukamaju
  const village = await prisma.village.upsert({
    where: { code: 'SKM' },
    update: {},
    create: {
      code: 'SKM',
      name: 'Desa Sukamaju',
      district: 'Kecamatan Jonggol',
      regency: 'Kabupaten Bogor',
      province: 'Jawa Barat',
      address: 'Jl. Raya Desa Sukamaju No. 01 Kode Pos 16830',
      phone: '(0251) 123-4567',
      email: 'pemdes@sukamaju.desa.id',
      official_head: 'Drs. H. Mulyono',
      official_nip: '19680315 199203 1 004',
      service_hours: 'Senin - Jumat, 08.00 - 14.00 WIB'
    }
  });

  console.log(`Desa ready: ${village.name} (${village.code})`);

  // Default Password Hash (password123)
  const defaultPasswordHash = await bcrypt.hash('password123', 10);

  // 2. Users (Admin, Kades, Warga)
  // Admin Desa (Merangkap Verifikator Pelayanan)
  const adminUser = await prisma.user.upsert({
    where: { nik: '199508102020121002' },
    update: {},
    create: {
      village_id: village.id,
      nik: '199508102020121002',
      name: 'Rian Pratama (Admin & Verifikator)',
      email: 'admin@desasukamaju.go.id',
      phone: '081277665544',
      password_hash: defaultPasswordHash,
      role: 'ADMIN'
    }
  });

  // Kepala Desa
  const kadesUser = await prisma.user.upsert({
    where: { nik: '196803151992031004' },
    update: {},
    create: {
      village_id: village.id,
      nik: '196803151992031004',
      name: 'Drs. H. Mulyono',
      email: 'kades@desasukamaju.go.id',
      phone: '081288990011',
      password_hash: defaultPasswordHash,
      role: 'KADES'
    }
  });

  // Warga Contoh (Budi Santoso)
  const residentUser = await prisma.user.upsert({
    where: { nik: '3201121508900001' },
    update: {},
    create: {
      village_id: village.id,
      nik: '3201121508900001',
      name: 'Budi Santoso',
      email: 'budi@warga.id',
      phone: '081298765432',
      password_hash: defaultPasswordHash,
      role: 'WARGA'
    }
  });

  // 3. Resident Profile Budi Santoso
  const budiProfile = await prisma.resident.upsert({
    where: { nik: '3201121508900001' },
    update: {},
    create: {
      village_id: village.id,
      user_id: residentUser.id,
      nik: '3201121508900001',
      family_card_no: '3201122005080015',
      full_name: 'Budi Santoso',
      birth_place: 'Bogor',
      birth_date: new Date('1990-08-15'),
      gender: 'Laki-Laki',
      address: 'Jl. Merpati No. 14',
      rt: '002',
      rw: '005',
      religion: 'Islam',
      marital_status: 'Kawin',
      occupation: 'Wiraswasta (Toko Kelontong)',
      phone_number: '081298765432',
      is_verified: true
    }
  });

  // 4. Data Penduduk Tambahan
  const otherResidents = [
    {
      nik: '3201125203920004',
      family_card_no: '3201122005080015',
      full_name: 'Siti Rahmawati',
      birth_place: 'Bandung',
      birth_date: new Date('1992-03-12'),
      gender: 'Perempuan',
      address: 'Jl. Merpati No. 14',
      rt: '002',
      rw: '005',
      religion: 'Islam',
      marital_status: 'Kawin',
      occupation: 'Guru Honorer',
      phone_number: '081344556677'
    },
    {
      nik: '3201120101850007',
      family_card_no: '3201121004120033',
      full_name: 'Ahmad Subagyo',
      birth_place: 'Sukabumi',
      birth_date: new Date('1985-01-01'),
      gender: 'Laki-Laki',
      address: 'Jl. Melati RT 001 / RW 002 No. 08',
      rt: '001',
      rw: '002',
      religion: 'Islam',
      marital_status: 'Kawin',
      occupation: 'Petani / Pekebun',
      phone_number: '085211223344'
    }
  ];

  for (const res of otherResidents) {
    await prisma.resident.upsert({
      where: { nik: res.nik },
      update: {},
      create: {
        village_id: village.id,
        ...res
      }
    });
  }

  // 5. Layanan Administrasi (Seluruh Layanan Wajib Approval Kades Sesuai Instruksi)
  const servicesData = [
    {
      code: 'SKU',
      name: 'Surat Keterangan Usaha',
      category: 'Surat Keterangan',
      description: 'Surat keterangan resmi dari pemerintah desa/kelurahan untuk menerangkan legalitas operasional usaha mikro atau kecil milik warga.',
      estimation_days: 1,
      requires_approval: true,
      requirements: [
        'Foto / Scan e-KTP Pemohon (Asli / Jelas)',
        'Foto / Scan Kartu Keluarga (KK)',
        'Surat Pengantar dari Ketua RT/RW',
        'Foto Tempat Usaha / Kegiatan Usaha'
      ]
    },
    {
      code: 'SKD',
      name: 'Surat Keterangan Domisili',
      category: 'Surat Keterangan',
      description: 'Surat keterangan domisili tempat tinggal sementara atau tetap bagi penduduk di wilayah desa/kelurahan.',
      estimation_days: 1,
      requires_approval: true,
      requirements: [
        'Foto / Scan e-KTP Pemohon',
        'Foto / Scan Kartu Keluarga (KK)',
        'Surat Pengantar RT/RW setempat'
      ]
    },
    {
      code: 'SKTM',
      name: 'Surat Keterangan Tidak Mampu',
      category: 'Bantuan Sosial',
      description: 'Surat untuk keperluan pengajuan beasiswa, jaminan kesehatan daerah (PBI/Jamkesda), atau keringanan biaya pendidikan.',
      estimation_days: 1,
      requires_approval: true,
      requirements: [
        'e-KTP Pemohon & Orang Tua/Wali',
        'Kartu Keluarga (KK)',
        'Surat Pengantar RT/RW dengan stempel',
        'Foto Rumah Tampak Depan'
      ]
    },
    {
      code: 'SKCK',
      name: 'Surat Pengantar SKCK',
      category: 'Pengantar',
      description: 'Surat pengantar rekomendasi kelakuan baik dari desa untuk pembuatan SKCK di Polsek / Polres setempat.',
      estimation_days: 1,
      requires_approval: true, // Wajib approval Kades
      requirements: [
        'e-KTP Pemohon',
        'Kartu Keluarga (KK)',
        'Surat Pengantar RT/RW'
      ]
    },
    {
      code: 'SKK',
      name: 'Surat Keterangan Kelahiran',
      category: 'Kependudukan',
      description: 'Surat pengantar keterangan lahir bagi bayi baru lahir untuk dasar pembuatan Akta Kelahiran di Disdukcapil.',
      estimation_days: 2,
      requires_approval: true,
      requirements: [
        'Surat Kelahiran dari Bidan / Rumah Sakit',
        'e-KTP Ayah & Ibu',
        'Kartu Keluarga (KK)',
        'Buku Nikah Orang Tua'
      ]
    },
    {
      code: 'SKMT',
      name: 'Surat Keterangan Kematian',
      category: 'Kependudukan',
      description: 'Surat pencatatan warga meninggal dunia untuk pelaporan Disdukcapil dan penerbitan akta kematian.',
      estimation_days: 1,
      requires_approval: true,
      requirements: [
        'Surat Kematian dari Dokter / RS (jika ada)',
        'e-KTP yang Meninggal & e-KTP Pelapor',
        'Kartu Keluarga (KK)',
        'Surat Pengantar RT/RW'
      ]
    }
  ];

  for (const s of servicesData) {
    const srv = await prisma.service.upsert({
      where: {
        village_id_code: {
          village_id: village.id,
          code: s.code
        }
      },
      update: {},
      create: {
        village_id: village.id,
        code: s.code,
        name: s.name,
        category: s.category,
        description: s.description,
        estimation_days: s.estimation_days,
        requires_approval: true
      }
    });

    // Syarat
    for (const reqName of s.requirements) {
      await prisma.serviceRequirement.create({
        data: {
          service_id: srv.id,
          name: reqName,
          is_mandatory: true
        }
      });
    }
  }

  // 6. Pengaturan Website Desa
  const settings = [
    { key: 'hero_title', value: 'Administrasi Desa Sukamaju Kini Serba Digital' },
    { key: 'hero_subtitle', value: 'Ajukan surat keterangan usaha, domisili, SKTM, pengantar SKCK, dan permohonan lainnya secara online. Pantau status pengajuan langsung dari rumah.' },
    { key: 'announcement', value: 'Seluruh permohonan surat keterangan gratis tanpa biaya dan wajib diverifikasi oleh Kepala Desa.' },
    { key: 'all_services_require_kades', value: 'true' }
  ];

  for (const set of settings) {
    await prisma.setting.upsert({
      where: {
        village_id_key: {
          village_id: village.id,
          key: set.key
        }
      },
      update: { value: set.value },
      create: {
        village_id: village.id,
        key: set.key,
        value: set.value
      }
    });
  }

  // 7. Contoh Aplikasi Permohonan (Menunggu Approval Kades)
  const skuService = await prisma.service.findFirst({
    where: { village_id: village.id, code: 'SKU' }
  });

  if (skuService) {
    const app = await prisma.application.upsert({
      where: { tracking_number: 'ADM-20261005-000124' },
      update: {},
      create: {
        village_id: village.id,
        user_id: residentUser.id,
        resident_id: budiProfile.id,
        service_id: skuService.id,
        tracking_number: 'ADM-20261005-000124',
        purpose: 'Pengajuan kredit usaha mikro KUR Bank BRI Unit Sukamaju',
        business_name: 'Warung Sembako Berkah Budi',
        business_type: 'Perdagangan Bahan Pokok',
        business_address: 'Jl. Merpati No. 14, RT 002 / RW 005',
        status: 'MENUNGGU_PERSETUJUAN',
        verifier_notes: 'Berkas e-KTP, KK, dan Surat Pengantar RT telah diperiksa lengkap & valid oleh Admin Desa.',
        approval_notes: null
      }
    });

    await prisma.statusHistory.createMany({
      data: [
        {
          application_id: app.id,
          status: 'DIAJUKAN',
          actor_name: 'Budi Santoso (Pemohon)',
          notes: 'Permohonan dikirimkan secara online.'
        },
        {
          application_id: app.id,
          status: 'MENUNGGU_PERSETUJUAN',
          actor_name: 'Rian Pratama (Admin Desa)',
          notes: 'Berkas dinyatakan lengkap dan diteruskan ke Kepala Desa untuk persetujuan.'
        }
      ],
      skipDuplicates: true
    });
  }

  console.log('Seeding PostgreSQL selesai dengan sukses!');
}

main()
  .catch((e) => {
    console.error('Error saat seeding database:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
