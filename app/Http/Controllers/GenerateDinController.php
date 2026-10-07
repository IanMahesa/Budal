<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\SubBagians;
use App\Models\QrCodes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDinController extends Controller
{
    public function index()
    {
        $qrcode = QrCodes::with(['pegawai', 'subbag'])
        ->where('jenis_qr', 'SUBBAG')
        ->orderBy('id_qrcode', 'desc')
        ->get();

    return view('geneqrdin.index', compact('qrcode'));
    }

    public function create()
    {
        $bagian = Bagians::orderBy('bag')->get();

        $nextIdQrcode = (int) QrCodes::withoutGlobalScopes()->max('id_qrcode') + 1;

        return view('geneqrdin.create', compact('bagian', 'nextIdQrcode'));
    }

    public function getPerijinanBySubbag($id_subag)
    {
        $perijinan = Perijinan::where('jenis', 'DINAS')
            ->where('id_subag', $id_subag)
            ->orderBy('izin')
            ->select('id_ijin', 'izin')
            ->get();

        return response()->json($perijinan);
    }

    public function getPegawaiBySubag($id_subag)
    {
        $pegawai = Pegawai::where('id_subag', $id_subag)
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->select('id_peg', 'nama', 'nik', 'status')
            ->get();

        return response()->json($pegawai);
    }

    public function store(Request $request)
    {
        $rules = [
            'jenis_qr' => 'required|in:SUBBAG',
            'status'   => 'required|in:Aktif,Nonaktif',
            'ket'      => 'required|string|max:100',
            'tanggal_dinas' => 'required|date',
            'id_subag' => 'required|exists:subag,id_subag',
            'id_peg'   => 'required|array|min:1',
            'id_peg.*' => 'required|distinct|exists:pegawai,id_peg',
        ];

        $messages = [
            'jenis_qr.required' => 'Jenis QR wajib diisi.',
            'jenis_qr.in'       => 'Jenis QR tidak valid.',

            'status.required'   => 'Status wajib diisi.',
            'status.in'         => 'Status tidak valid.',

            'ket.required'      => 'Keterangan wajib diisi.',
            'ket.max'           => 'Keterangan maksimal 100 karakter.',

            'id_subag.required' => 'Sub Bagian wajib dipilih.',
            'id_subag.exists'   => 'Sub Bagian tidak ditemukan.',

            'id_peg.required'   => 'Minimal pilih 1 pegawai.',
            'id_peg.array'      => 'Data pegawai tidak valid.',
            'id_peg.min'        => 'Minimal pilih 1 pegawai.',
            'id_peg.*.required' => 'Pegawai wajib dipilih.',
            'id_peg.*.distinct' => 'Pegawai tidak boleh dipilih lebih dari satu kali.',
            'id_peg.*.exists'   => 'Pegawai tidak ditemukan.',
        ];

        $validated = $request->validate($rules, $messages);

        $pegawaiIds = $validated['id_peg'];

        $pegawaiValidCount = Pegawai::whereIn('id_peg', $pegawaiIds)
            ->where('id_subag', $validated['id_subag'])
            ->where('status', 'Aktif')
            ->count();

        if ($pegawaiValidCount !== count($pegawaiIds)) {
            return back()
                ->withErrors([
                    'id_peg' => 'Pastikan semua pegawai aktif dan berasal dari Sub Bagian yang dipilih.'
                ])
                ->withInput();
        }

        $pegawaiSudahMemilikiQr = QrCodes::withoutGlobalScopes()
            ->where('jenis_qr', 'SUBBAG')
            ->whereIn('id_peg', $pegawaiIds)
            ->whereDate('tanggal_dinas', $validated['tanggal_dinas'])
            ->exists();

        if ($pegawaiSudahMemilikiQr) {
            return back()
                ->withErrors([
                    'id_peg' => 'Salah satu pegawai sudah memiliki QR Code DINAS pada tanggal tersebut.'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $pegawaiIds) {
            $nomorAwal = (int) QrCodes::withoutGlobalScopes()->max('id_qrcode') + 1;

            $subbag = \App\Models\SubBagians::find($validated['id_subag']);

            $namaKartu = 'QR SUBBAG';

            if ($subbag) {
                $namaKartu .= ' - ' . strtoupper($subbag->sub_bag);
            }

            foreach ($pegawaiIds as $index => $idPeg) {
                $idQrcode = $nomorAwal + $index;
                $nomorKartu = str_pad(
                    $idQrcode,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                QrCodes::create([
                    'jenis_qr'         => 'SUBBAG',
                    'nama_kartu'       => $namaKartu,
                    'nomor_kartu'      => $nomorKartu,
                    'id_peg'           => $idPeg,
                    'id_subag'         => $validated['id_subag'],
                    'id_ijin'          => null,
                    'status'           => $validated['status'],
                    'tanggal_dinas'    => $validated['tanggal_dinas'],
                    'tanggal_generate' => Carbon::now(),
                    'ket'              => ucfirst($validated['ket']),
                ]);
            }
        });

        return redirect()
            ->route('geneqrdin.index')
            ->with(
                'success',
                'QR Code untuk ' . count($pegawaiIds) . ' pegawai berhasil dibuat.'
            );
    }


public function edit($id)
{
    $qrcode = QrCodes::with([
        'pegawai',
        'subbag.bagian'
    ])->findOrFail($id);

    $pegawai = Pegawai::with('subbag.bagian')
        ->where('status', 'Aktif')
        ->orderBy('nama')
        ->get();

    $bagian = Bagians::orderBy('bag')->get();

    $subbag = SubBagians::with('bagian')
        ->orderBy('sub_bag')
        ->get();

    $selectedBagianId = $qrcode->subbag->id_bag ?? null;
    $selectedSubbagId = $qrcode->id_subag;
    $selectedPegawaiList = $qrcode->pegawai
        ? [[
            'id_peg' => $qrcode->pegawai->id_peg,
            'nama_label' => $qrcode->pegawai->nama . ' - ' . $qrcode->pegawai->nik,
        ]]
        : [];

    return view('geneqrdin.edit', compact(
        'qrcode',
        'pegawai',
        'subbag',
        'bagian',
        'selectedBagianId',
        'selectedSubbagId',
        'selectedPegawaiList'
    ));
}


public function update(Request $request, $id)
{
    $geneqr = QrCodes::findOrFail($id);

    $rules = [
        'jenis_qr'      => 'required|in:PEGAWAI,SUBBAG',

        'nama_kartu'    => 'required|string|max:100',

        'nomor_kartu'   => [
            'required',
            'string',
            'max:30',
            'unique:qrcode,nomor_kartu,' . $id . ',id_qrcode',
        ],

        'status'        => 'required|in:Aktif,Nonaktif',

        'ket'           => 'nullable|string|max:100',

        'tanggal_dinas' => 'required|date',
    ];


    if ($request->jenis_qr == 'PEGAWAI') {

        $rules['id_peg'] =
            'required|exists:pegawai,id_peg,status,Aktif';

    } else {

        $rules['id_subag'] =
            'required|exists:subag,id_subag';

        $rules['id_ijin'] =
            'nullable|exists:perijinan,id_ijin';

        $rules['id_peg'] =
            'required|exists:pegawai,id_peg,status,Aktif';
    }


    $validated = $request->validate($rules);


    /*
    |--------------------------------------------------------------------------
    | Pegawai
    |--------------------------------------------------------------------------
    */

    $idPeg = $validated['id_peg'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Cek apakah pegawai sudah mempunyai QR SUBBAG lain
    |--------------------------------------------------------------------------
    */

    if ($request->jenis_qr == 'SUBBAG' && $idPeg) {

        $pegawaiSudahMemilikiQr = QrCodes::where('jenis_qr', 'SUBBAG')
            ->where('id_peg', $idPeg)
            ->where('id_qrcode', '!=', $id)
            ->exists();


        if ($pegawaiSudahMemilikiQr) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pegawai tersebut sudah memiliki QR Code pada Sub Bagian lain.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Data
    |--------------------------------------------------------------------------
    */

    $geneqr->update([

        'jenis_qr' =>
            $validated['jenis_qr'],

        'nama_kartu' =>
            strtoupper($validated['nama_kartu']),

        'nomor_kartu' =>
            strtoupper($validated['nomor_kartu']),

        'id_peg' =>
            $idPeg,

        'id_subag' =>
            $request->jenis_qr == 'SUBBAG'
                ? $validated['id_subag']
                : null,

        'id_ijin' =>
            $request->jenis_qr == 'SUBBAG'
                ? ($validated['id_ijin'] ?? null)
                : null,

        'status' =>
            $validated['status'],

        'ket' =>
            !empty($validated['ket'])
                ? strtoupper($validated['ket'])
                : null,

        'tanggal_dinas' =>
            $validated['tanggal_dinas'],
    ]);


    return redirect()
        ->route('geneqrdin.index')
        ->with(
            'success',
            'Data QR Code berhasil diperbarui.'
        );
}

    public function destroy($id)
    {
        $qrcode = QrCodes::findOrFail($id);

        // Cek apakah QR pernah dipakai transaksi
        if ($qrcode->ijinKeluar()->exists()) {

            return redirect()
                ->route('geneqrdin.index')
                ->with('error', 'QR Code tidak dapat dihapus karena sudah pernah digunakan.');
        }

        $qrcode->is_delete = 1;
        $qrcode->save();

        return redirect()
            ->route('geneqrdin.index')
            ->with('success', 'QR Code berhasil dihapus.');
    }

    public function show($id) 
    { 
        $qrcode = QrCodes::with([ 'pegawai', 'subbag' ])->findOrFail($id); 
        
        return view('geneqrdin.show', compact('qrcode')); 
    }
}
