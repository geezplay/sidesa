import app from './app.js';

const PORT = process.env.PORT || 5000;

app.listen(PORT, () => {
  console.log(`===============================================`);
  console.log(`🚀 SIADESA Backend Server running on port ${PORT}`);
  console.log(`📡 URL API: http://localhost:${PORT}`);
  console.log(`🗄️ Database: PostgreSQL (Prisma ORM Connected)`);
  console.log(`===============================================`);
});
