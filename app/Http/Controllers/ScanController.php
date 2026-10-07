<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\IjinKeluar;
use App\Models\Perijinan;
use App\Models\QrCodes;
use Carbon\Carbon;

class ScanController extends Controller
{
    private function resolveTransaksiData(QrCodes $qr, ?string $jenisIzin = null): array
    {
        $idPeg = $qr->id_peg ?? optional($qr->pegawai)->id_peg;
        $idSubag = $qr->id_subag ?? optional($qr->pegawai->subbag)->id_subag ?? optional($qr->subbag)->id_subag;
        $idBag = optional($qr->subbag)->id_bag ?? optional($qr->pegawai->subbag)->id_bag;
        $idIjin = $qr->id_ijin ?? optional($qr->perijinan)->id_ijin;

        if (!$idIjin && $jenisIzin) {
            $query = Perijinan::query()->where('jenis', $jenisIzin);

            if ($idSubag) {
                $query->where(function ($sub) use ($idSubag) {
                    $sub->whereNull('id_subag')->orWhere('id_subag', $idSubag);
                });
            }

            $perijinan = $query->first();
            $idIjin = optional($perijinan)->id_ijin;
        }

        return [
            'idPeg' => $idPeg,
            'idSubag' => $idSubag,
            'idBag' => $idBag,
            'idIjin' => $idIjin,
        ];
    }

    public function index()
    {
        return view('scan.index');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'kode_qr' => 'required'
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

        $qr = $query->with(['pegawai.subbag.bagian', 'subbag.bagian', 'perijinan'])->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.'
            ], 404);
        }

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
            if (preg_match('/(\d{2}:\d{2}:\d{2})$/', $jamKeluarRaw, $matches)) {
                $jamKeluar = $matches[1];
            } else {
                $jamKeluar = $jamKeluarRaw;
            }

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
                'message' => 'Transaksi kembali berhasil dicatat.',
                'data' => [
                    'id_transaksi' => $existing->id_transaksi,
                    'id_qrcode' => $existing->id_qrcode,
                    'status' => $existing->status,
                ]
            ]);
        }

        $jenisTarget = $qr->jenis_qr === 'SUBBAG' ? 'DINAS' : 'PRIBADI';
        $perijinanOptions = Perijinan::query()
            ->where('jenis', $jenisTarget)
            ->when(optional($qr->pegawai->subbag)->id_subag ?? optional($qr->subbag)->id_subag, function ($query, $idSubag) {
                $query->where(function ($sub) use ($idSubag) {
                    $sub->whereNull('id_subag')->orWhere('id_subag', $idSubag);
                });
            })
            ->select('id_ijin', 'izin', 'jenis')
            ->get()
            ->map(function ($item) {
                return [
                    'id_ijin' => $item->id_ijin,
                    'izin' => $item->izin,
                    'jenis' => $item->jenis,
                ];
            })
            ->toArray();

        $pegawai = $qr->pegawai;
        $subbag = optional($pegawai)->subbag ?? $qr->subbag;

        return response()->json([
            'success' => true,
            'message' => 'Silakan pilih jenis izin.',
            'data' => [
                'id_qrcode' => $qr->id_qrcode,
                'nomor_kartu' => $qr->nomor_kartu,
                'kode_qr' => $qr->kode_qr ?? null,
                'jenis_qr' => $qr->jenis_qr ?? null,
                'jenis_izin' => $jenisTarget,
                'perijinan_jenis' => optional($qr->perijinan)->jenis ?? $jenisTarget,
                'nama_pegawai' => optional($pegawai)->nama ?? null,
                'nik' => optional($pegawai)->nik ?? null,
                'nama_subbag' => optional($subbag)->sub_bag ?? null,
                'perijinan_options' => $perijinanOptions,
            ]
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'kode_qr' => 'required',
            'jenis_izin' => 'required|in:PRIBADI,DINAS',
            'id_ijin' => 'nullable|exists:perijinan,id_ijin',
            'foto_pegawai' => 'required|string',
        ]);

        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $request->foto_pegawai, $matches)) {
            return response()->json([
                'success' => false,
                'message' => 'Format foto pegawai tidak valid.'
            ], 422);
        }

        $photoData = base64_decode(substr($request->foto_pegawai, strpos($request->foto_pegawai, ',') + 1), true);

        if ($photoData === false || strlen($photoData) > 5 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Foto pegawai tidak valid atau terlalu besar.'
            ], 422);
        }

        $query = QrCodes::query();

        if (Schema::hasColumn('qrcode', 'kode_qr')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('kode_qr', $request->kode_qr)
                    ->orWhere('nomor_kartu', $request->kode_qr);
            });
        } else {
            $query->where('nomor_kartu', $request->kode_qr);
        }

        $qr = $query->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.'
            ], 404);
        }

        $data = $this->resolveTransaksiData($qr, $request->jenis_izin);

        if ($request->filled('id_ijin')) {
            $data['idIjin'] = $request->id_ijin;
        }

        if (!$data['idPeg'] || !$data['idSubag'] || !$data['idBag'] || !$data['idIjin']) {
            return response()->json([
                'success' => false,
                'message' => 'Data QR tidak lengkap untuk membuat transaksi keluar.'
            ], 422);
        }

        $photoPath = 'izin-foto/' . uniqid('pegawai_', true) . '.' . ($matches[1] === 'jpg' ? 'jpg' : $matches[1]);
        Storage::disk('public')->put($photoPath, $photoData);

        $transaksi = IjinKeluar::where('id_qrcode', $qr->id_qrcode)
            ->where('status', IjinKeluar::STATUS_KELUAR)
            ->whereNull('jam_masuk')
            ->latest('id_transaksi')
            ->first();

        if ($transaksi) {
            $transaksi->update([
                'id_peg' => $data['idPeg'],
                'id_ijin' => $data['idIjin'],
                'id_bag' => $data['idBag'],
                'id_subag' => $data['idSubag'],
                'keterangan' => $request->jenis_izin === 'PRIBADI' ? 'Ijin Pribadi' : 'Perjalanan Dinas',
                'foto_pegawai' => $photoPath,
            ]);

            if (Schema::hasColumn('ijin_keluar', 'jenis_scan')) {
                $transaksi->update([
                    'jenis_scan' => $request->jenis_izin === 'DINAS' ? IjinKeluar::SCAN_SUBBAG : IjinKeluar::SCAN_PEGAWAI,
                ]);
            }
        } else {
            $transaksi = IjinKeluar::create([
                'id_qrcode' => $qr->id_qrcode,
                'id_peg' => $data['idPeg'],
                'id_ijin' => $data['idIjin'],
                'id_bag' => $data['idBag'],
                'id_subag' => $data['idSubag'],
                'tanggal_keluar' => now()->toDateString(),
                'jam_keluar' => now()->format('H:i:s'),
                'status' => IjinKeluar::STATUS_KELUAR,
                'keterangan' => $request->jenis_izin === 'PRIBADI' ? 'Ijin Pribadi' : 'Perjalanan Dinas',
                'created_by' => auth()->id(),
                'foto_pegawai' => $photoPath,
            ]);

            if (Schema::hasColumn('ijin_keluar', 'jenis_scan')) {
                $transaksi->update([
                    'jenis_scan' => $request->jenis_izin === 'DINAS' ? IjinKeluar::SCAN_SUBBAG : IjinKeluar::SCAN_PEGAWAI,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pilihan izin berhasil disimpan.'
        ]);
    }
}