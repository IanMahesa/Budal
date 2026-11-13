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
    public function __construct()
    {
        $this->middleware('permission:rekap-list');
    }

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
            ->leftJoin('perijinan as i', 'ik.id_ijin', '=', 'i.id_ijin')

            ->select(
                'p.id_peg',
                'p.nik',
                'p.nama',

                'b.bag',
                'sb.sub_bag',

                DB::raw("COUNT(CASE WHEN i.jenis='PRIBADI' OR (i.jenis IS NULL AND ik.jenis_scan='PEGAWAI') THEN 1 END) as jml_pribadi"),

                DB::raw("SUM(CASE WHEN i.jenis='PRIBADI' OR (i.jenis IS NULL AND ik.jenis_scan='PEGAWAI')
                        THEN ik.durasi_menit ELSE 0 END)
                        as durasi_pribadi"),

                DB::raw("COUNT(CASE WHEN i.jenis='DINAS' OR (i.jenis IS NULL AND ik.jenis_scan='SUBBAG') THEN 1 END)
                        as jml_dinas"),

                DB::raw("SUM(CASE WHEN i.jenis='DINAS' OR (i.jenis IS NULL AND ik.jenis_scan='SUBBAG')
                        THEN ik.durasi_menit ELSE 0 END)
                        as durasi_dinas"),

                DB::raw("SUM(ik.durasi_menit) as total_durasi")
            );

        $applyDateFilters = function ($query, $dateColumn) use ($request) {
            if (!filter_var($request->input('filter_applied', false), FILTER_VALIDATE_BOOLEAN)) {
                return;
            }

            $periode = $request->input('periode', 'harian');
            if ($periode === 'harian' && $request->filled('tanggal')) {
                $query->whereDate($dateColumn, $request->input('tanggal'));
            } elseif ($periode === 'bulanan') {
                if ($request->filled('bulan')) {
                    $query->whereMonth($dateColumn, $request->input('bulan'));
                }
                if ($request->filled('tahun')) {
                    $query->whereYear($dateColumn, $request->input('tahun'));
                }
            } elseif ($periode === 'tahunan' && $request->filled('tahun')) {
                $query->whereYear($dateColumn, $request->input('tahun'));
            } elseif ($periode === 'range'
                && $request->filled('tanggal_awal')
                && $request->filled('tanggal_akhir')) {
                $query->whereBetween($dateColumn, [
                    $request->input('tanggal_awal'),
                    $request->input('tanggal_akhir'),
                ]);
            }
        };

        $applyDateFilters($query, 'ik.tanggal_keluar');

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
            ->get()
            ->keyBy('id_peg');

        $lemburQuery = DB::table('detail_lembur as dl')
            ->join('lembur as l', 'dl.id_lembur', '=', 'l.id_lembur')
            ->join('pegawai as p', 'dl.id_peg', '=', 'p.id_peg')
            ->join('subag as sb', 'p.id_subag', '=', 'sb.id_subag')
            ->join('bagian as b', 'sb.id_bag', '=', 'b.id_bag')
            ->where('dl.is_delete', 0)
            ->where('l.is_delete', 0)
            ->where('dl.status_kehadiran', 'Selesai')
            ->select(
                'p.id_peg',
                'p.nik',
                'p.nama',
                'b.bag',
                'sb.sub_bag',
                DB::raw('COUNT(dl.id_detail_lembur) as jml_lembur'),
                DB::raw('SUM(COALESCE(dl.total_menit, 0)) as durasi_lembur')
            );

        $applyDateFilters($lemburQuery, 'l.tanggal_lembur');

        if ($request->filled('id_bag')) {
            $lemburQuery->where('l.id_bag', $request->input('id_bag'));
        }
        if ($request->filled('id_subag')) {
            $lemburQuery->where('l.id_subag', $request->input('id_subag'));
        }
        if ($request->filled('id_peg')) {
            $lemburQuery->where('dl.id_peg', $request->input('id_peg'));
        }

        $lemburData = $lemburQuery
            ->groupBy('p.id_peg', 'p.nik', 'p.nama', 'b.bag', 'sb.sub_bag')
            ->get();

        foreach ($data as $row) {
            $row->jml_lembur = 0;
            $row->durasi_lembur = 0;
        }

        foreach ($lemburData as $row) {
            if (isset($data[$row->id_peg])) {
                $data[$row->id_peg]->jml_lembur = (int) $row->jml_lembur;
                $data[$row->id_peg]->durasi_lembur = (int) $row->durasi_lembur;
                continue;
            }

            $row->jml_pribadi = 0;
            $row->durasi_pribadi = 0;
            $row->jml_dinas = 0;
            $row->durasi_dinas = 0;
            $row->total_durasi = 0;
            $row->jml_lembur = (int) $row->jml_lembur;
            $row->durasi_lembur = (int) $row->durasi_lembur;
            $data->put($row->id_peg, $row);
        }

        return response()->json($data->sortBy('nama')->values());
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
