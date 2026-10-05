<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiadesaController extends Controller
{
    // Public Pages
    public function home()
    {
        return view('public.index');
    }

    public function services()
    {
        return view('public.services');
    }

    public function serviceDetail($id)
    {
        return view('public.service-detail', ['id' => $id]);
    }

    public function tracking(Request $request)
    {
        $code = $request->query('kode', '');
        return view('public.tracking', ['code' => $code]);
    }

    public function verifyDoc($code)
    {
        return view('public.verify-doc', ['code' => $code]);
    }

    // Auth
    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    // Role: Masyarakat
    public function residentDashboard()
    {
        return view('resident.dashboard');
    }

    public function residentProfile()
    {
        return view('resident.profile');
    }

    public function residentCreateApplication(Request $request)
    {
        $serviceId = $request->query('layanan', 'sku');
        return view('resident.create', ['serviceId' => $serviceId]);
    }

    public function residentHistory()
    {
        return view('resident.history');
    }

    public function residentServices()
    {
        return view('resident.services');
    }

    // Role: Admin Desa (Merangkap Verifikasi Pelayanan)
    public function adminDashboard()
    {
        return view('admin.dashboard');
    }

    public function adminVerifications()
    {
        return view('admin.verifications');
    }

    public function adminVerificationReview($id)
    {
        return view('admin.verification-review', ['id' => $id]);
    }

    public function adminResidents()
    {
        return view('admin.residents');
    }

    public function adminServices()
    {
        return view('admin.services');
    }

    public function adminSettings()
    {
        return view('admin.settings');
    }

    public function adminAccounts()
    {
        return view('admin.accounts');
    }

    public function adminPrintLetter($id)
    {
        return view('admin.print-letter', ['id' => $id]);
    }

    // Role: Kades
    public function kadesDashboard()
    {
        return view('kades.index');
    }

    public function kadesReview($id)
    {
        return view('kades.review', ['id' => $id]);
    }
}
