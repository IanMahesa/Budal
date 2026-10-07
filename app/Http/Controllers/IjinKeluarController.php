<?php

namespace App\Http\Controllers;

use App\Models\IjinKeluar;
use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\Bagians;
use App\Models\SubBagians;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\IjinKeluarExport;
use Maatwebsite\Excel\Facades\Excel;

class IjinKeluarController extends Controller
{
    public function index()
    {
        $validated = request()->validate([
            'tanggal' => ['nullable', 'date'],
            'jenis' => ['nullable', Rule::in(['PRIBADI', 'DINAS'])],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();
        $jenis = $validated['jenis'] ?? '';

        $transaksi = IjinKeluar::with([
            'pegawai',
            'bagian',
            'subbag',
            'perijinan',
            'user'
        ])
        ->whereDate('tanggal_keluar', $tanggal)
        ->when($jenis, function ($query) use ($jenis) {
            $query->whereHas('perijinan', function ($perijinan) use ($jenis) {
                $perijinan->where('jenis', $jenis);
            });
        })
        ->orderByDesc('id_transaksi')
        ->get();

        return view('keluar.index', compact('transaksi', 'tanggal', 'jenis'));
    }

    public function show($id)
    {
        $ijinKeluar = IjinKeluar::with([
            'pegawai',
            'bagian',
            'subbag',
            'perijinan',
            'qrcode',
            'user'
        ])->findOrFail($id);

        return view('keluar.show', compact('ijinKeluar'));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['nullable', Rule::in(['PRIBADI', 'DINAS'])],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        return Excel::download(
             new IjinKeluarExport(
                 $validated['jenis'] ?? null,
                 $validated['tanggal_mulai'] ?? null,
                 $validated['tanggal_akhir'] ?? null
             ),
            'Laporan_Ijin_Keluar_'.date('Ymd_His').'.xlsx'
        );
    }
}
