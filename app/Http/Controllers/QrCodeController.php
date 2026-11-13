<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\QrCodes;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $jenisQr = $request->query('jenis_qr', 'PEGAWAI');

        abort_unless(in_array($jenisQr, ['PEGAWAI', 'SUBBAG'], true), 404);

        $pegawai = Pegawai::with('subbag')
            ->where('nik', $user->username)
            ->first();

        $qrcode = $pegawai
            ? QrCodes::with('subbag')
                ->where('id_peg', $pegawai->id_peg)
                ->where('jenis_qr', $jenisQr)
                ->where('status', 'Aktif')
                ->when($jenisQr === 'SUBBAG', function ($query) {
                    $query->whereDate('tanggal_dinas', now()->toDateString())
                        ->orderByDesc('tanggal_dinas');
                })
                ->orderByDesc('id_qrcode')
                ->first()
            : null;

        return view('qrcode.index', compact('user', 'pegawai', 'qrcode', 'jenisQr'));
    }
}
