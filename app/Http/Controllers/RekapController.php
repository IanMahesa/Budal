<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapPegawaiExport;

use App\Models\Bagians;
use App\Models\SubBagians;
use App\Models\Pegawai;

class RekapController extends Controller
{
    public function index()
    {
        $bagian = Bagians::orderBy('bag')->get();
        $subbag = SubBagians::orderBy('sub_bag')->get();
        $pegawai = Pegawai::orderBy('nama')->get();

        return view('rekap.index', compact(
            'bagian',
            'subbag',
            'pegawai'
        ));
    }

    public function data(Request $request)
    {
        $query = DB::table('ijin_keluar as ik')
            ->join('pegawai as p', 'ik.id_peg', '=', 'p.id_peg')
            ->join('subag as sb', 'ik.id_subag', '=', 'sb.id_subag')
            ->join('bagian as b', 'ik.id_bag', '=', 'b.id_bag')
            ->join('perijinan as i', 'ik.id_ijin', '=', 'i.id_ijin')

            ->select(
                'p.id_peg',
                'p.nik',
                'p.nama',

                'b.bag',
                'sb.sub_bag',

                DB::raw("COUNT(CASE WHEN i.jenis='PRIBADI' THEN 1 END) as jml_pribadi"),

                DB::raw("SUM(CASE WHEN i.jenis='PRIBADI'
                        THEN ik.durasi_menit ELSE 0 END)
                        as durasi_pribadi"),

                DB::raw("COUNT(CASE WHEN i.jenis='DINAS' THEN 1 END)
                        as jml_dinas"),

                DB::raw("SUM(CASE WHEN i.jenis='DINAS'
                        THEN ik.durasi_menit ELSE 0 END)
                        as durasi_dinas"),

                DB::raw("SUM(ik.durasi_menit) as total_durasi")
            );

            $applyFilter = filter_var($request->input('filter_applied', false), FILTER_VALIDATE_BOOLEAN);
            $periode = $request->input('periode', 'harian');
            $tanggal = $request->input('tanggal');
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            $tanggalAwal = $request->input('tanggal_awal');
            $tanggalAkhir = $request->input('tanggal_akhir');

            if ($applyFilter) {
                if ($periode == 'harian') {
                    if (!empty($tanggal)) {
                        $query->whereDate('ik.tanggal_keluar', $tanggal);
                    }
                } elseif ($periode == 'bulanan') {
                    if (!empty($bulan)) {
                        $query->whereMonth('ik.tanggal_keluar', $bulan);
                    }
                    if (!empty($tahun)) {
                        $query->whereYear('ik.tanggal_keluar', $tahun);
                    }
                } elseif ($periode == 'tahunan') {
                    if (!empty($tahun)) {
                        $query->whereYear('ik.tanggal_keluar', $tahun);
                    }
                } elseif ($periode == 'range') {
                    if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
                        $query->whereBetween('ik.tanggal_keluar', [$tanggalAwal, $tanggalAkhir]);
                    }
                }
            }

            if ($request->filled('id_bag')) {
                $query->where('ik.id_bag', $request->id_bag);
            }

            if ($request->filled('id_subag')) {
                $query->where('ik.id_subag', $request->id_subag);
            }

            if ($request->filled('id_peg')) {
                $query->where('ik.id_peg', $request->id_peg);
            }

            $query->whereNotNull('ik.jam_masuk');

            $data = $query
                ->groupBy(
                    'p.id_peg',
                    'p.nik',
                    'p.nama',
                    'b.bag',
                    'sb.sub_bag'
                )
                ->orderBy('p.nama')
                ->get();

            return response()->json($data);
    }

    public function getPegawai(Request $request)
    {
        $query = Pegawai::query()->select('id_peg', 'nama', 'nik');

        if ($request->filled('id_subag')) {
            $query->where('id_subag', $request->id_subag);
        } elseif ($request->filled('id_bag')) {
            $query->whereHas('subbag', function ($subQuery) use ($request) {
                $subQuery->where('id_bag', $request->id_bag);
            });
        }

        $pegawai = $query->orderBy('nama')->get();

        return response()->json($pegawai);
    }

    public function excel(Request $request)
    {
        return Excel::download(
            new RekapPegawaiExport($request),
            'Rekap_Pegawai_'.date('Ymd_His').'.xlsx'
        );
    }
}
