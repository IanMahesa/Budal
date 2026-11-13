<?php

namespace App\Http\Controllers;

use App\Models\IjinKeluar;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class RekapPegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rekappeg-list');
    }

    public function index(Request $request)
    {
        $pegawai = Pegawai::where('nik', $request->user()->username)->first();

        if (!$pegawai) {
            return view('rekappeg.index', [
                'pegawai' => null,
                'transaksi' => collect(),
                'akunBelumTerhubung' => true,
            ]);
        }

        $query = IjinKeluar::with(['perijinan', 'pegawai.subbag.bagian'])
            ->where('id_peg', $pegawai->id_peg)
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('jam_keluar');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_keluar', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_keluar', $request->tahun);
        }

        if ($request->filled('jenis')) {
            $query->whereHas('perijinan', function ($perijinanQuery) use ($request) {
                $perijinanQuery->where('jenis', $request->jenis);
            });
        }

        $transaksi = $query->get();

        return view('rekappeg.index', [
            'pegawai' => $pegawai,
            'transaksi' => $transaksi,
            'akunBelumTerhubung' => false,
        ]);
    }
}
