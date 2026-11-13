<?php

namespace App\Http\Controllers;

use App\Models\IjinKeluar;
use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\Bagians;
use App\Models\SubBagians;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\IjinKeluarWorkbookExport;
use Maatwebsite\Excel\Facades\Excel;

class IjinKeluarController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:keluar-list');
    }

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
            'qrcode',
            'perijinan',
            'user'
        ])
        ->whereDate('tanggal_keluar', $tanggal)
        ->when($jenis, function ($query) use ($jenis) {
            $jenisScan = $jenis === 'DINAS'
                ? IjinKeluar::SCAN_SUBBAG
                : IjinKeluar::SCAN_PEGAWAI;

            $query->where(function ($query) use ($jenis, $jenisScan) {
                $query->whereHas('perijinan', function ($perijinan) use ($jenis) {
                    $perijinan->where('jenis', $jenis);
                })->orWhere('jenis_scan', $jenisScan);
            });
        })
        ->orderByDesc('id_transaksi')
        ->get();

        $transaksi->each(function ($row) {
            $jenisIjin = optional($row->perijinan)->jenis;
            if (!$jenisIjin) {
                $jenisIjin = $row->jenis_scan === IjinKeluar::SCAN_SUBBAG
                    ? 'DINAS'
                    : ($row->jenis_scan === IjinKeluar::SCAN_PEGAWAI ? 'PRIBADI' : null);
            }

            $row->durasi_scan_jenis = $jenisIjin;
            $row->durasi_scan_menit = null;

            if ($row->status !== IjinKeluar::STATUS_KEMBALI || !$row->jam_masuk) {
                return;
            }

            if ($jenisIjin === 'PRIBADI' && $row->jam_keluar) {
                $tanggalKeluar = $row->tanggal_keluar instanceof \Carbon\Carbon
                    ? $row->tanggal_keluar->format('Y-m-d')
                    : $row->tanggal_keluar;
                $tanggalMasuk = $row->tanggal_masuk
                    ? ($row->tanggal_masuk instanceof \Carbon\Carbon
                        ? $row->tanggal_masuk->format('Y-m-d')
                        : $row->tanggal_masuk)
                    : $tanggalKeluar;

                $keluarAt = \Carbon\Carbon::parse($tanggalKeluar . ' ' . $row->jam_keluar);
                $masukAt = \Carbon\Carbon::parse($tanggalMasuk . ' ' . $row->jam_masuk);
                $row->durasi_scan_menit = (int) $keluarAt->diffInMinutes($masukAt);
                return;
            }

            if ($jenisIjin !== 'DINAS' || !optional($row->qrcode)->waktu_masuk) {
                return;
            }

            $batasMasuk = \Carbon\Carbon::parse($row->qrcode->waktu_masuk);
            $jamMasuk = \Carbon\Carbon::parse($row->jam_masuk);
            $row->durasi_scan_menit = ($batasMasuk->hour * 60 + $batasMasuk->minute)
                - ($jamMasuk->hour * 60 + $jamMasuk->minute);
        });

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
             new IjinKeluarWorkbookExport(
                 $validated['jenis'] ?? null,
                 $validated['tanggal_mulai'] ?? null,
                 $validated['tanggal_akhir'] ?? null
             ),
            'Laporan_Ijin_Keluar_'.date('Ymd_His').'.xlsx'
        );
    }
}
