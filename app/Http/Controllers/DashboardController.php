<?php

namespace App\Http\Controllers;

use App\Models\IjinKeluar;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;

class DashboardController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::count();

        $todayCounts = IjinKeluar::leftJoin(
            'perijinan',
            'ijin_keluar.id_ijin',
            '=',
            'perijinan.id_ijin'
        )
            ->whereDate('ijin_keluar.tanggal_keluar', today())
            ->selectRaw("
                SUM(CASE
                    WHEN perijinan.jenis = 'PRIBADI'
                        OR (perijinan.jenis IS NULL AND ijin_keluar.jenis_scan = 'PEGAWAI')
                    THEN 1 ELSE 0
                END) as pribadi
            ")
            ->selectRaw("
                SUM(CASE
                    WHEN perijinan.jenis = 'DINAS'
                        OR (perijinan.jenis IS NULL AND ijin_keluar.jenis_scan = 'SUBBAG')
                    THEN 1 ELSE 0
                END) as dinas
            ")
            ->first();

        $pribadi = (int) $todayCounts->pribadi;
        $dinas = (int) $todayCounts->dinas;

        $keluar = IjinKeluar::where('status', IjinKeluar::STATUS_KELUAR)
            ->whereDate('ijin_keluar.tanggal_keluar', today())
            ->count();

        $monthlyCounts = IjinKeluar::leftJoin(
            'perijinan',
            'ijin_keluar.id_ijin',
            '=',
            'perijinan.id_ijin'
        )
            ->whereYear('ijin_keluar.tanggal_keluar', now()->year)
            ->selectRaw('MONTH(ijin_keluar.tanggal_keluar) as bulan')
            ->selectRaw("
                SUM(CASE
                    WHEN perijinan.jenis = 'PRIBADI'
                        OR (perijinan.jenis IS NULL AND ijin_keluar.jenis_scan = 'PEGAWAI')
                    THEN 1 ELSE 0
                END) as pribadi
            ")
            ->selectRaw("
                SUM(CASE
                    WHEN perijinan.jenis = 'DINAS'
                        OR (perijinan.jenis IS NULL AND ijin_keluar.jenis_scan = 'SUBBAG')
                    THEN 1 ELSE 0
                END) as dinas
            ")
            ->groupBy(DB::raw('MONTH(ijin_keluar.tanggal_keluar)'))
            ->get()
            ->keyBy('bulan');

        $grafikPribadi = [];
        $grafikDinas = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $counts = $monthlyCounts->get($bulan);
            $grafikPribadi[] = (int) optional($counts)->pribadi;
            $grafikDinas[] = (int) optional($counts)->dinas;
        }

        $lembur = DB::table('lembur')
            ->where('is_delete', 0)
            ->select('status')
            ->get();

        return view('dashboard.index', compact(
            'pegawai',
            'pribadi',
            'dinas',
            'keluar',
            'grafikPribadi',
            'grafikDinas',
            'lembur'
        ));
    }
}
