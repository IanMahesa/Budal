<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Statistik Pegawai
        $totalPegawai = Pegawai::count();
        $pegawaiAktif = Pegawai::where('status', 'Aktif')->count();
        $pegawaiTidakAktif = Pegawai::where('status', 'Tidak Aktif')->count();

        return view('admin.index', compact(
            'totalPegawai',
            'pegawaiAktif',
            'pegawaiTidakAktif'
        ));
    }
}