<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DetailLembur;
use App\Models\IjinKeluar;
use App\Models\QrCodes;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:scan-list');
    }

    private function resolveTransaksiData(QrCodes $qr): array
    {
        $pegawai = $qr->pegawai;
        $subbag = $qr->subbag ?? optional($pegawai)->subbag;
        $idPeg = $qr->id_peg ?? optional($qr->pegawai)->id_peg;
        $idSubag = $qr->id_subag ?? optional($subbag)->id_subag;
        $idBag = optional($subbag)->id_bag;
        return [
            'idPeg' => $idPeg,
            'idSubag' => $idSubag,
            'idBag' => $idBag,
        ];
    }

    public function index()
    {
        return view('scan.index');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'kode_qr' => ['required', 'string'],
            'mode' => ['nullable', 'in:ijin,lembur'],
            'id_lembur' => ['nullable', 'integer', 'exists:lembur,id_lembur'],
        ]);

        $query = QrCodes::query();

        if (Schema::hasColumn('qrcode', 'kode_qr')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('kode_qr', $request->kode_qr)
                    ->orWhere('nomor_kartu', $request->kode_qr);
            });
        } else {
            $query->where('nomor_kartu', $request->kode_qr);
        }

        // Pastikan relasi 'perijinan' tidak wajib dimuat lagi jika tidak digunakan
        $qr = $query->with(['pegawai.subbag.bagian', 'subbag.bagian'])->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.'
            ], 404);
        }

        if ($request->input('mode', 'ijin') === 'lembur') {
            return $this->prosesScanLembur(
                $qr,
                $request->filled('id_lembur') ? (int) $request->input('id_lembur') : null
            );
        }

        $scanAt = now();
        if ($qr->jenis_qr === 'SUBBAG' && $qr->tanggal_dinas) {
            $tanggalDinas = $qr->tanggal_dinas instanceof Carbon
                ? $qr->tanggal_dinas
                : Carbon::parse($qr->tanggal_dinas);

            if (!$tanggalDinas->isSameDay($scanAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code dinas hanya dapat digunakan pada tanggal dinas yang tertera.'
                ], 422);
            }
        }

        $waktuKeluar = $qr->waktu_keluar
            ? substr((string) $qr->waktu_keluar, 0, 5) . ':00'
            : null;
        $waktuScan = $scanAt->format('H:i:s');

        $existing = IjinKeluar::where('id_qrcode', $qr->id_qrcode)
            ->where('status', IjinKeluar::STATUS_KELUAR)
            ->whereNull('jam_masuk')
            ->first();

        if ($existing) {
            $tanggalMasuk = now()->toDateString();
            $jamMasuk = now()->format('H:i:s');

            $tanggalKeluar = $existing->tanggal_keluar instanceof Carbon
                ? $existing->tanggal_keluar->format('Y-m-d')
                : $existing->tanggal_keluar;

            $jamKeluarRaw = trim((string) $existing->jam_keluar);
            $jamKeluar = preg_match('/(\d{2}:\d{2}:\d{2})$/', $jamKeluarRaw, $matches) ? $matches[1] : $jamKeluarRaw;

            $keluarAt = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalKeluar . ' ' . $jamKeluar);
            $durasiMenit = Carbon::parse($tanggalMasuk . ' ' . $jamMasuk)->diffInMinutes($keluarAt);

            $existing->update([
                'tanggal_masuk' => $tanggalMasuk,
                'jam_masuk' => $jamMasuk,
                'durasi_menit' => $durasiMenit,
                'status' => IjinKeluar::STATUS_KEMBALI,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ijin kembali berhasil dicatat.',
                'data' => [
                    'id_transaksi' => $existing->id_transaksi,
                    'id_qrcode' => $existing->id_qrcode,
                    'status' => $existing->status,
                ]
            ]);
        }

        if ($qr->jenis_qr === 'SUBBAG') {
            if ($waktuKeluar && $waktuScan < $waktuKeluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Absen keluar baru dapat dilakukan mulai pukul ' . substr($waktuKeluar, 0, 5) . '.'
                ], 422);
            }

        }

        $jenisIzin = $qr->jenis_qr === 'SUBBAG' ? 'DINAS' : 'PRIBADI';
        $data = $this->resolveTransaksiData($qr);

        if (!$data['idPeg'] || !$data['idSubag'] || !$data['idBag']) {
            return response()->json([
                'success' => false,
                'message' => 'Data QR tidak lengkap untuk membuat transaksi keluar.'
            ], 422);
        }

        $transaksiData = [
            'id_qrcode' => $qr->id_qrcode,
            'id_peg' => $data['idPeg'],
            'id_bag' => $data['idBag'],
            'id_subag' => $data['idSubag'],
            'tanggal_keluar' => now()->toDateString(),
            'jam_keluar' => now()->format('H:i:s'),
            'status' => IjinKeluar::STATUS_KELUAR,
            'keterangan' => $qr->ket,
            'created_by' => auth()->id(),
        ];

        if (Schema::hasColumn('ijin_keluar', 'jenis_scan')) {
            $transaksiData['jenis_scan'] = $jenisIzin === 'DINAS'
                ? IjinKeluar::SCAN_SUBBAG
                : IjinKeluar::SCAN_PEGAWAI;
        }

        $transaksi = IjinKeluar::create($transaksiData);

        return response()->json([
            'success' => true,
            'message' => 'Ijin keluar berhasil dicatat.',
            'data' => [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_qrcode' => $transaksi->id_qrcode,
                'status' => $transaksi->status,
            ]
        ]);
    }

    private function prosesScanLembur(QrCodes $qr, ?int $idLembur)
    {
        if ($qr->jenis_qr !== 'PEGAWAI' || $qr->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Scan lembur hanya menerima QR pegawai yang aktif.',
            ], 422);
        }

        $scanAt = now();

        $candidates = DetailLembur::with('lembur')
            ->where('id_qrcode', $qr->id_qrcode)
            ->where('is_delete', 0)
            ->whereIn('status_kehadiran', ['Belum Hadir', 'Belum Mulai', 'Hadir'])
            ->whereHas('lembur', function ($query) use ($scanAt) {
                $query->whereDate('tanggal_lembur', $scanAt->toDateString())
                    ->whereIn('status', ['Terjadwal', 'Berlangsung'])
                    ->where('is_delete', 0);
            })
            ->get();

        if (!$idLembur && $candidates->count() > 1) {
            $choices = $candidates->map(function ($candidate) {
                $lembur = $candidate->lembur;

                return [
                    'id' => $candidate->id_lembur,
                    'label' => '#' . $candidate->id_lembur
                        . ' | ' . $lembur->jenis_lembur
                        . ($lembur->keterangan ? ' | ' . $lembur->keterangan : ''),
                ];
            })->values();

            return response()->json([
                'success' => false,
                'requires_selection' => true,
                'message' => 'QR ini terdaftar pada beberapa kegiatan lembur hari ini. Pilih kegiatan yang sesuai.',
                'choices' => $choices,
            ], 409);
        }

        if (!$idLembur && $candidates->count() === 1) {
            $idLembur = (int) $candidates->first()->id_lembur;
        }

        if (!$idLembur) {
            return response()->json([
                'success' => false,
                'message' => 'QR pegawai tidak terdaftar pada kegiatan lembur aktif hari ini.',
            ], 422);
        }

        return DB::transaction(function () use ($qr, $idLembur, $scanAt) {
            $detail = DetailLembur::with('lembur')
                ->where('id_lembur', $idLembur)
                ->where('id_qrcode', $qr->id_qrcode)
                ->where('is_delete', 0)
                ->whereIn('status_kehadiran', ['Belum Hadir', 'Belum Mulai', 'Hadir'])
                ->whereHas('lembur', function ($query) use ($scanAt) {
                    $query->whereDate('tanggal_lembur', $scanAt->toDateString())
                        ->whereIn('status', ['Terjadwal', 'Berlangsung'])
                        ->where('is_delete', 0);
                })
                ->lockForUpdate()
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR pegawai tidak terdaftar pada kegiatan lembur yang dipilih hari ini.',
                ], 422);
            }

            if ($detail->status_kehadiran === 'Selesai' || $detail->jam_selesai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Absensi lembur pegawai ini sudah selesai.',
                ], 422);
            }

            if (!$detail->jam_mulai) {
                $lembur = $detail->lembur;
                if ($lembur->jenis_lembur === 'TERENCANA' && $lembur->jam_mulai) {
                    $jadwalMulai = Carbon::parse(
                        $lembur->tanggal_lembur->format('Y-m-d') . ' ' . $lembur->jam_mulai
                    );

                    if ($scanAt->lt($jadwalMulai)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Scan mulai lembur baru dapat dilakukan mulai pukul '
                                . $jadwalMulai->format('H:i') . '.',
                        ], 422);
                    }
                }

                $detail->update([
                    'jam_mulai' => $scanAt,
                    'status_kehadiran' => 'Hadir',
                ]);
                $detail->lembur->update(['status' => 'Berlangsung']);

                return response()->json([
                    'success' => true,
                    'message' => 'Jam mulai lembur berhasil dicatat untuk ' . optional($detail->pegawai)->nama . '.',
                    'data' => [
                        'id_lembur' => $detail->id_lembur,
                        'status' => $detail->status_kehadiran,
                        'jam' => $scanAt->format('H:i:s'),
                    ],
                ]);
            }

            $jamMulai = Carbon::parse($detail->jam_mulai);
            if ($scanAt->lte($jamMulai)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scan selesai harus dilakukan setelah scan mulai.',
                ], 422);
            }

            $totalMenit = (int) $jamMulai->diffInMinutes($scanAt);
            $detail->update([
                'jam_selesai' => $scanAt,
                'total_menit' => $totalMenit,
                'status_kehadiran' => 'Selesai',
            ]);

            $pesertaBelumSelesai = DetailLembur::where('id_lembur', $idLembur)
                ->where('is_delete', 0)
                ->where('status_kehadiran', '!=', 'Selesai')
                ->exists();
            if (!$pesertaBelumSelesai) {
                $detail->lembur->update(['status' => 'Selesai']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Jam selesai lembur berhasil dicatat. Durasi: '
                    . floor($totalMenit / 60) . ' jam ' . ($totalMenit % 60) . ' menit.',
                'data' => [
                    'id_lembur' => $detail->id_lembur,
                    'status' => $detail->status_kehadiran,
                    'jam' => $scanAt->format('H:i:s'),
                    'total_menit' => $totalMenit,
                ],
            ]);
        });
    }
}