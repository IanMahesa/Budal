<?php

namespace App\Http\Controllers;

use App\Models\DetailLembur;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class RekapLemburController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rekappeg-list');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2024', 'max:' . now()->year],
            'jenis' => ['nullable', 'in:TERENCANA,DADAKAN'],
        ]);

        $user = $request->user();
        $pegawai = $user->pegawai ?: Pegawai::where('nik', $user->username)->first();

        if (!$pegawai) {
            return view('rekaplem.index', [
                'pegawai' => null,
                'transaksi' => collect(),
                'akunBelumTerhubung' => true,
            ]);
        }

        $query = DetailLembur::with(['lembur', 'pegawai.subbag.bagian'])
            ->select('detail_lembur.*')
            ->join('lembur', 'detail_lembur.id_lembur', '=', 'lembur.id_lembur')
            ->where('detail_lembur.id_peg', $pegawai->id_peg)
            ->where('detail_lembur.is_delete', 0)
            ->where('lembur.is_delete', 0)
            ->when(isset($filters['bulan']), function ($query) use ($filters) {
                $query->whereMonth('lembur.tanggal_lembur', $filters['bulan']);
            })
            ->when(isset($filters['tahun']), function ($query) use ($filters) {
                $query->whereYear('lembur.tanggal_lembur', $filters['tahun']);
            })
            ->when(isset($filters['jenis']), function ($query) use ($filters) {
                $query->where('lembur.jenis_lembur', $filters['jenis']);
            })
            ->orderByDesc('lembur.tanggal_lembur')
            ->orderByDesc('detail_lembur.id_detail_lembur');

        return view('rekaplem.index', [
            'pegawai' => $pegawai,
            'transaksi' => $query->get(),
            'akunBelumTerhubung' => false,
        ]);
    }
}
