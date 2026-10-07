<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\IjinKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::count();

        $pribadi = IjinKeluar::whereHas('perijinan', function ($q) {
            $q->where('jenis', 'PRIBADI');
        })->whereDate('tanggal_keluar', today())->count();

        $dinas = IjinKeluar::whereHas('perijinan', function ($q) {
            $q->where('jenis', 'DINAS');
        })->whereDate('tanggal_keluar', today())->count();

        $keluar = IjinKeluar::where('status', 'Keluar')
            ->whereDate('tanggal_keluar', today())
            ->count();

        // Grafik Bulanan //
        $grafikPribadi = [];
        $grafikDinas = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $grafikPribadi[] = IjinKeluar::join('perijinan', 'ijin_keluar.id_ijin', '=', 'perijinan.id_ijin')
                ->whereYear('tanggal_keluar', date('Y'))
                ->whereMonth('tanggal_keluar', $bulan)
                ->where('perijinan.jenis', 'PRIBADI')
                ->count();

            $grafikDinas[] = IjinKeluar::join('perijinan', 'ijin_keluar.id_ijin', '=', 'perijinan.id_ijin')
                ->whereYear('tanggal_keluar', date('Y'))
                ->whereMonth('tanggal_keluar', $bulan)
                ->where('perijinan.jenis', 'DINAS')
                ->count();
        }

        return view('dashboard.index', compact(
            'pegawai',
            'pribadi',
            'dinas',
            'keluar',
            'grafikPribadi',
            'grafikDinas'
        ));
    }
}
