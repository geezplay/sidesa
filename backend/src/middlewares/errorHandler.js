export const errorHandler = (err, req, res, next) => {
  console.error('Server Error:', err);

  if (err.code === 'LIMIT_FILE_SIZE') {
    return res.status(400).json({
      success: false,
      message: 'Ukuran file melebihi 2 MB maksimum.'
    });
  }

  if (err.name === 'ValidationError' || err.issues) {
    return res.status(400).json({
      success: false,
      message: 'Validasi input gagal.',
      errors: err.issues || err.message
    });
  }

  const statusCode = err.statusCode || 500;
  res.status(statusCode).json({
    success: false,
    message: err.message || 'Terjadi kesalahan internal pada server.',
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack })
  });
};
