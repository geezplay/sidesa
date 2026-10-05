<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiadesaController;

// Public routes
Route::get('/', [SiadesaController::class, 'home'])->name('home');
Route::get('/layanan', [SiadesaController::class, 'services'])->name('services');
Route::get('/layanan/{id}', [SiadesaController::class, 'serviceDetail'])->name('services.detail');
Route::get('/tracking', [SiadesaController::class, 'tracking'])->name('tracking');
Route::get('/verifikasi-surat/{code}', [SiadesaController::class, 'verifyDoc'])->name('verify.doc');

// Auth routes
Route::get('/login', [SiadesaController::class, 'login'])->name('login');
Route::get('/register', [SiadesaController::class, 'register'])->name('register');

// Role: Masyarakat
Route::prefix('warga')->name('resident.')->group(function () {
    Route::get('/dashboard', [SiadesaController::class, 'residentDashboard'])->name('dashboard');
    Route::get('/profil', [SiadesaController::class, 'residentProfile'])->name('profile');
    Route::get('/layanan', [SiadesaController::class, 'residentServices'])->name('services');
    Route::get('/pengajuan/baru', [SiadesaController::class, 'residentCreateApplication'])->name('create');
    Route::get('/pengajuan/riwayat', [SiadesaController::class, 'residentHistory'])->name('history');
});

// Role: Admin Desa (Merangkap Verifikator Pelayanan)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [SiadesaController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/verifikasi', [SiadesaController::class, 'adminVerifications'])->name('verifications');
    Route::get('/verifikasi/{id}', [SiadesaController::class, 'adminVerificationReview'])->name('verification.review');
    Route::get('/penduduk', [SiadesaController::class, 'adminResidents'])->name('residents');
    Route::get('/layanan', [SiadesaController::class, 'adminServices'])->name('services');
    Route::get('/pengaturan', [SiadesaController::class, 'adminSettings'])->name('settings');
    Route::get('/akun', [SiadesaController::class, 'adminAccounts'])->name('accounts');
    Route::get('/surat/cetak/{id}', [SiadesaController::class, 'adminPrintLetter'])->name('print');
});

// Alias backward compatibility untuk Verifikator -> dialihkan langsung ke Admin
Route::get('/verifikator/dashboard', function () {
    return redirect()->route('admin.verifications');
})->name('verifikator.dashboard');
Route::get('/verifikator/review/{id}', function ($id) {
    return redirect()->route('admin.verification.review', ['id' => $id]);
})->name('verifikator.review');

// Role: Kepala Desa (Approval)
Route::prefix('kades')->name('kades.')->group(function () {
    Route::get('/dashboard', [SiadesaController::class, 'kadesDashboard'])->name('dashboard');
    Route::get('/review/{id}', [SiadesaController::class, 'kadesReview'])->name('review');
});
