<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'Username atau password salah.']);
        }

        $request->session()->regenerate();

        // 1. Ambil data user yang sedang login
        $user = Auth::user();

        // 2. Lakukan pengecekan role dan atur redirect-nya
        // (Pastikan method hasRole() tersedia, biasanya jika menggunakan Spatie Laravel Permission)
        
        if ($user->hasRole('Staf')) {
            // Contoh: Jika role-nya Pegawai, diarahkan ke halaman scan/absensi
            return redirect()->route('qrcode.index');
        }

        if ($user->hasRole('Operator')) {
            // Contoh: Jika role-nya Operator, diarahkan ke halaman rekap
            return redirect()->route('scan.index');
        }

        // 3. Default redirect (untuk Admin / role lain yang tetap ke dashboard)
        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}