<?php

namespace App\Http\Controllers;

use App\Exports\LemburWorkbookExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LemburController extends Controller
{
    // ==========================================
    // INDEX
    // ==========================================
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'jenis' => ['nullable', 'in:TERENCANA,DADAKAN'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();
        $jenis = $validated['jenis'] ?? '';

        // Data kegiatan lembur
        $lembur = DB::table('lembur as l')
            ->leftJoin('bagian as b', 'l.id_bag', '=', 'b.id_bag')
            ->leftJoin('subag as s', 'l.id_subag', '=', 's.id_subag')
            ->leftJoin('users as u', 'l.created_by', '=', 'u.id')
            ->where('l.is_delete', 0)
            ->whereDate('l.tanggal_lembur', $tanggal)
            ->when($jenis !== '', function ($query) use ($jenis) {
                $query->where('l.jenis_lembur', $jenis);
            })
            ->select(
                'l.*',
                'b.bag',
                's.sub_bag',
                'u.name as nama_pembuat'
            )
            ->orderByDesc('l.tanggal_lembur')
            ->orderByDesc('l.id_lembur')
            ->get();

        $pegawaiLembur = DB::table('detail_lembur as dl')
            ->join('pegawai as p', 'dl.id_peg', '=', 'p.id_peg')
            ->where('dl.is_delete', 0)
            ->whereIn('dl.id_lembur', $lembur->pluck('id_lembur'))
            ->select(
                'dl.id_lembur',
                'p.nama',
                'dl.jam_mulai',
                'dl.jam_selesai',
                'l.jam_mulai as jadwal_jam_mulai',
                'l.jam_selesai as jadwal_jam_selesai'
            )
            ->leftJoin('lembur as l', 'dl.id_lembur', '=', 'l.id_lembur')
            ->orderBy('p.nama')
            ->get()
            ->map(function ($dataPegawai) {
                $menitPadaWaktu = function ($waktu) {
                    if (!$waktu) {
                        return null;
                    }

                    $waktu = Carbon::parse($waktu);

                    return ($waktu->hour * 60) + $waktu->minute;
                };

                $jadwalMulai = $menitPadaWaktu($dataPegawai->jadwal_jam_mulai);
                $jadwalSelesai = $menitPadaWaktu($dataPegawai->jadwal_jam_selesai);
                $scanMulai = $menitPadaWaktu($dataPegawai->jam_mulai);
                $scanSelesai = $menitPadaWaktu($dataPegawai->jam_selesai);

                $dataPegawai->selisih_mulai_menit = $jadwalMulai !== null && $scanMulai !== null
                    ? $jadwalMulai - $scanMulai
                    : null;
                $dataPegawai->selisih_selesai_menit = $jadwalSelesai !== null && $scanSelesai !== null
                    ? $scanSelesai - $jadwalSelesai
                    : null;

                $durasiJadwal = null;
                if ($jadwalMulai !== null && $jadwalSelesai !== null) {
                    $durasiJadwal = $jadwalSelesai - $jadwalMulai;
                    if ($durasiJadwal < 0) {
                        $durasiJadwal += 24 * 60;
                    }
                }

                $durasiAktual = null;
                if ($dataPegawai->jam_mulai && $dataPegawai->jam_selesai) {
                    $durasiAktual = (int) Carbon::parse($dataPegawai->jam_mulai)
                        ->diffInMinutes(Carbon::parse($dataPegawai->jam_selesai));
                }

                $dataPegawai->durasi_scan_aktual_menit = $durasiAktual;
                $dataPegawai->selisih_durasi_menit = $durasiAktual !== null && $durasiJadwal !== null
                    ? $durasiAktual - $durasiJadwal
                    : null;

                return $dataPegawai;
            })
            ->groupBy('id_lembur');

        // Dropdown bagian
        $bagian = DB::table('bagian')
            ->orderBy('bag')
            ->get();

        // Dropdown subbagian
        $subag = DB::table('subag')
            ->orderBy('sub_bag')
            ->get();

        // Pegawai aktif beserta bagian dan subbagian
        $pegawai = DB::table('pegawai as p')
            ->join('subag as s', 'p.id_subag', '=', 's.id_subag')
            ->where('p.status', 'Aktif')
            ->select(
                'p.id_peg',
                'p.nama',
                'p.id_subag',
                's.id_bag'
            )
            ->orderBy('p.nama')
            ->get();

        return view('lembur.index', compact(
            'lembur',
            'pegawaiLembur',
            'bagian',
            'subag',
            'pegawai',
            'tanggal',
            'jenis'
        ));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['nullable', Rule::in(['TERENCANA', 'DADAKAN'])],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        return Excel::download(
             new LemburWorkbookExport(
                 $validated['jenis'] ?? null,
                 $validated['tanggal_mulai'] ?? null,
                 $validated['tanggal_akhir'] ?? null
             ),
            'Laporan_Lembur_'.date('Ymd_His').'.xlsx'
        );
    }
  
}