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

class GenerateController extends Controller
{
    public function index()
    {
        $qrcode = QrCodes::with(['pegawai.subbag'])
                    ->where('jenis_qr', 'PEGAWAI')
                    ->orderBy('id_qrcode','desc')
                    ->get();

        return view('geneqr.index', compact('qrcode'));
    }

   public function create()
    {
        $pegawai = Pegawai::where('status', 'Aktif')
            ->orderBy('nama')
            ->get();

        $bagian = Bagians::orderBy('bag')->get();

        $nextIdQrcode = (int) QrCodes::withoutGlobalScopes()->max('id_qrcode') + 1;

        return view('geneqr.create', compact('pegawai', 'bagian', 'nextIdQrcode'));
    }


    public function getPerijinanBySubbag($id_subag)
    {
        $perijinan = Perijinan::where('jenis', 'PRIBADI')
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
            'jenis_qr'    => 'required|in:PEGAWAI,SUBBAG',
            'nama_kartu'  => 'required|string|max:100',
            'nomor_kartu' => 'required|string|max:30|unique:qrcode,nomor_kartu',
            'status'      => 'required|in:Aktif,Nonaktif',
        ];

        if ($request->jenis_qr == 'PEGAWAI') {
            $rules['id_peg'] = 'required|exists:pegawai,id_peg,status,Aktif';
        } else {
            $rules['id_subag'] = 'required|exists:subag,id_subag';
            $rules['id_ijin'] = 'nullable|exists:perijinan,id_ijin';
            $rules['id_peg'] = 'required|exists:pegawai,id_peg,status,Aktif';
        }

        $validated = $request->validate($rules);

        $idPeg = null;

        if ($request->jenis_qr == 'PEGAWAI') {
            $idPeg = $validated['id_peg'];
        } elseif ($request->filled('id_peg')) {
            $idPeg = $request->id_peg;
        }

        QrCodes::create([
            'jenis_qr'         => $validated['jenis_qr'],
            'nama_kartu'       => strtoupper($validated['nama_kartu']),
            'nomor_kartu'      => strtoupper($validated['nomor_kartu']),
            'id_peg'           => $idPeg,
            'id_subag'         => $request->jenis_qr == 'SUBBAG'
                                    ? $validated['id_subag']
                                    : null,
            'id_ijin'           => $validated['jenis_qr'] == 'SUBBAG'
                                    ? ($validated['id_ijin'] ?? null)
                                    : null,
            'status'           => $validated['status'],
            'tanggal_generate' => Carbon::now(),
            'is_delete'        => 0,
        ]);

        return redirect()
                ->route('geneqr.index')
                ->with('success', 'QR Code berhasil dibuat.');
    }

    public function edit($id)
    {
        $qrcode = QrCodes::findOrFail($id);

        $pegawai = Pegawai::with('subbag.bagian')
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get();

        $bagian = Bagians::orderBy('bag')->get();

        $subbag = SubBagians::with('bagian')
            ->orderBy('sub_bag')
            ->get();

        return view('geneqr.edit', compact(
            'qrcode',
            'pegawai',
            'subbag',
            'bagian'
        ));
    }

    public function update(Request $request, $id)
    {
        // Sesuaikan primary key jika menggunakan id_qrcode atau id
        $geneqr = QrCodes::findOrFail($id);

        $rules = [
            'jenis_qr'    => 'required|in:PEGAWAI,SUBBAG',
            'nama_kartu'  => 'required|string|max:100',       
            'nomor_kartu' => 'required|string|max:30|unique:qrcode,nomor_kartu,' . $id . ',id_qrcode',
            'status'      => 'required|in:Aktif,Nonaktif',
        ];

        if ($request->jenis_qr == 'PEGAWAI') {
            $rules['id_peg'] = 'required|exists:pegawai,id_peg,status,Aktif';
        } else {
            $rules['id_subag'] = 'required|exists:subag,id_subag';
            $rules['id_ijin']  = 'nullable|exists:perijinan,id_ijin';
            $rules['id_peg']   = 'required|exists:pegawai,id_peg,status,Aktif';
        }

        $validated = $request->validate($rules);

        $idPeg = null;
        if ($request->jenis_qr == 'PEGAWAI') {
            $idPeg = $validated['id_peg'];
        } elseif ($request->filled('id_peg')) {
            $idPeg = $request->id_peg;
        }
        
        if ($request->jenis_qr == 'SUBBAG' && $idPeg) {
            $pegawaiSudahMemilikiQr = QrCodes::where('jenis_qr', 'SUBBAG')
                ->where('id_peg', $idPeg)
                ->where('id_qrcode', '!=', $id)
                ->exists();

            if ($pegawaiSudahMemilikiQr) {
                return back()
                    ->withInput()
                    ->with('error', 'Pegawai tersebut sudah memiliki QR Code pada Sub Bagian lain.');
            }
        }

        $geneqr->update([
            'jenis_qr'    => $validated['jenis_qr'],
            'nama_kartu'  => strtoupper($validated['nama_kartu']),
            'nomor_kartu' => strtoupper($validated['nomor_kartu']),
            'id_peg'      => $idPeg,
            'id_subag'    => $request->jenis_qr == 'SUBBAG' ? $validated['id_subag'] : null,
            'id_ijin'     => $request->jenis_qr == 'SUBBAG' ? ($validated['id_ijin'] ?? null) : null,
            'status'      => $validated['status'],
        ]);

        return redirect()
            ->route('geneqr.index')
            ->with('success', 'Data QR Code berhasil diperbarui.');
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
            ->route('geneqr.index')
            ->with('success', 'QR Code berhasil dihapus.');
    }

    public function show($id) 
    { 
        $qrcode = QrCodes::with([ 'pegawai', 'subbag' ])->findOrFail($id); 
        
        return view('geneqr.show', compact('qrcode')); }

    public function print(Request $request)
    {
        $ids = $request->input('id_qrcode', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()
                ->route('geneqr.index')
                ->with('error', 'Pilih minimal satu QR Code untuk dicetak.');
        }

        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return redirect()
                ->route('geneqr.index')
                ->with('error', 'Pilih minimal satu QR Code untuk dicetak.');
        }

        $qrcode = QrCodes::with([
            'pegawai.subbag.bagian',
            'subbag.bagian'
        ])
        ->whereIn('id_qrcode', $ids)
        ->get();

        QrCodes::whereIn('id_qrcode', $ids)
            ->update([
                'tanggal_cetak' => now()
            ]);

        return view('geneqr.print', compact('qrcode'));
    }

}
