<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\SubBagians;
use App\Models\QrCodes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDinController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:geneqr-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:geneqr-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:geneqr-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:geneqr-delete', ['only' => ['destroy']]);
        $this->middleware('permission:geneqr-create|geneqr-edit', [
            'only' => ['getPegawaiBySubag', 'getPerijinanBySubbag'],
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $isUnitScoped = $user->id_bag
            || $user->id_subag
            || in_array($user->jabatan, ['Manajer', 'A.Manajer'], true);
        $qrcode = QrCodes::with(['pegawai', 'subbag'])
            ->where('jenis_qr', 'SUBBAG')
            ->when(
                $isUnitScoped,
                function ($query) use ($user) {
                    $query->whereHas('pegawai', function ($pegawaiQuery) use ($user) {
                        if ($user->id_bag) {
                            $pegawaiQuery->where('id_bag', $user->id_bag);
                        } else {
                            $pegawaiQuery->whereRaw('1 = 0');
                        }

                        if ($user->id_subag) {
                            $pegawaiQuery->where('id_subag', $user->id_subag);
                        }
                    });
                }
            )
            ->orderBy('id_qrcode', 'desc')
            ->get();

        return view('geneqrdin.index', compact('qrcode'));
    }

    public function create()
    {
        $bagian = Bagians::orderBy('bag')->get();
        $user = auth()->user();
        $jabatan = $user->jabatan;
        $selectedBagianId = in_array($jabatan, ['Manajer', 'A.Manajer'], true)
            ? $user->id_bag
            : null;
        $selectedSubbagId = $jabatan === 'A.Manajer'
            ? $user->id_subag
            : null;

        $nextIdQrcode = (int) QrCodes::withoutGlobalScopes()->max('id_qrcode') + 1;

        return view('geneqrdin.create', compact(
            'bagian',
            'nextIdQrcode',
            'jabatan',
            'selectedBagianId',
            'selectedSubbagId'
        ));
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

    public function getDinasPegawai(Request $request)
    {
        $validated = $request->validate([
            'id_bag' => 'required|exists:bagian,id_bag',
            'id_subag' => 'nullable|exists:subag,id_subag',
        ]);
        $user = auth()->user();
        $idBag = (int) $validated['id_bag'];
        $idSubag = $validated['id_subag'] ?? null;

        if (in_array($user->jabatan, ['Manajer', 'A.Manajer'], true)
            && (int) $user->id_bag !== $idBag) {
            abort(403);
        }

        if ($user->jabatan === 'A.Manajer'
            && (int) $user->id_subag !== (int) $idSubag) {
            abort(403);
        }

        if ($idSubag && !SubBagians::where('id_subag', $idSubag)->where('id_bag', $idBag)->exists()) {
            abort(422, 'Sub bagian tidak sesuai dengan bagian yang dipilih.');
        }

        $pegawai = $this->dinasPegawaiQuery($user, $idBag, $idSubag)
            ->orderBy('nama')
            ->select('id_peg', 'nama', 'nik', 'status')
            ->get();

        return response()->json($pegawai);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $subbagRule = in_array($user->jabatan, ['Direktur'], true)
            ? 'nullable|exists:subag,id_subag'
            : 'required|exists:subag,id_subag';

        $rules = [
            'id_bag' => 'required|exists:bagian,id_bag',
            'id_subag' => $subbagRule,
            'entries' => 'required|array|min:1',
            'entries.*.id_peg' => 'required|distinct|exists:pegawai,id_peg',
            'entries.*.ket' => 'required|string|max:100',
            'entries.*.tanggal_dinas' => 'required|date',
            'entries.*.waktu_keluar' => 'required|date_format:H:i',
            'entries.*.waktu_masuk' => 'required|date_format:H:i|after:entries.*.waktu_keluar',
        ];

        $messages = [
            'id_bag.required' => 'Bagian wajib dipilih.',
            'id_subag.required' => 'Sub Bagian wajib dipilih.',
            'id_subag.exists'   => 'Sub Bagian tidak ditemukan.',
            'entries.required' => 'Minimal isi satu data pegawai.',
            'entries.array' => 'Data pegawai tidak valid.',
            'entries.min' => 'Minimal isi satu data pegawai.',
            'entries.*.id_peg.required' => 'Pegawai wajib dipilih pada setiap baris.',
            'entries.*.id_peg.distinct' => 'Pegawai tidak boleh dipilih lebih dari satu kali.',
            'entries.*.id_peg.exists' => 'Pegawai tidak ditemukan.',
            'entries.*.ket.required' => 'Keterangan wajib diisi pada setiap baris.',
            'entries.*.ket.max' => 'Keterangan maksimal 100 karakter.',
            'entries.*.tanggal_dinas.required' => 'Tanggal dinas wajib diisi pada setiap baris.',
            'entries.*.tanggal_dinas.date' => 'Tanggal dinas tidak valid.',
            'entries.*.waktu_keluar.required' => 'Waktu mulai absen keluar wajib diisi pada setiap baris.',
            'entries.*.waktu_keluar.date_format' => 'Format waktu keluar tidak valid.',
            'entries.*.waktu_masuk.required' => 'Batas akhir absen masuk wajib diisi pada setiap baris.',
            'entries.*.waktu_masuk.date_format' => 'Format waktu masuk tidak valid.',
            'entries.*.waktu_masuk.after' => 'Batas akhir absen masuk harus setelah waktu keluar.',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['id_subag'] = $validated['id_subag'] ?? null;

        if (in_array($user->jabatan, ['Manajer', 'A.Manajer'], true)
            && (int) $user->id_bag !== (int) $validated['id_bag']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_bag' => 'Bagian harus sesuai dengan bagian akun Anda.',
            ]);
        }

        if ($user->jabatan === 'A.Manajer'
            && (int) $user->id_subag !== (int) $validated['id_subag']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_subag' => 'Sub bagian harus sesuai dengan sub bagian akun Anda.',
            ]);
        }

        if ($validated['id_subag']
            && !SubBagians::where('id_subag', $validated['id_subag'])
                ->where('id_bag', $validated['id_bag'])
                ->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_subag' => 'Sub bagian tidak sesuai dengan bagian yang dipilih.',
            ]);
        }

        $entries = array_values($validated['entries']);
        $pegawaiIds = array_column($entries, 'id_peg');

        $pegawaiValidCount = $this->dinasPegawaiQuery(
            $user,
            (int) $validated['id_bag'],
            $validated['id_subag']
        )->whereIn('id_peg', $pegawaiIds)
            ->count();

        if ($pegawaiValidCount !== count($pegawaiIds)) {
            return back()
                ->withErrors([
                    'id_peg' => 'Pastikan pegawai yang dipilih aktif dan sesuai dengan bagian, sub bagian, serta jabatan yang ditentukan.'
                ])
                ->withInput();
        }

        foreach ($entries as $index => $entry) {
            $pegawaiSudahMemilikiQr = QrCodes::withoutGlobalScopes()
                ->where('jenis_qr', 'SUBBAG')
                ->where('id_peg', $entry['id_peg'])
                ->whereDate('tanggal_dinas', $entry['tanggal_dinas'])
                ->exists();

            if ($pegawaiSudahMemilikiQr) {
                return back()
                    ->withErrors([
                        "entries.{$index}.id_peg" => 'Pegawai sudah memiliki QR Code DINAS pada tanggal tersebut.'
                    ])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $entries) {
            $nomorAwal = (int) QrCodes::withoutGlobalScopes()->max('id_qrcode') + 1;

            $subbag = \App\Models\SubBagians::find($validated['id_subag']);

            $namaKartu = 'QR SUBBAG';

            if ($subbag) {
                $namaKartu .= ' - ' . strtoupper($subbag->sub_bag);
            }

            foreach ($entries as $index => $entry) {
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
                    'id_peg'           => $entry['id_peg'],
                    'id_subag'         => $validated['id_subag'],
                    'id_ijin'          => null,
                    'status'           => 'Aktif',
                    'tanggal_dinas'    => $entry['tanggal_dinas'],
                    'waktu_keluar'     => $entry['waktu_keluar'],
                    'waktu_masuk'      => $entry['waktu_masuk'],
                    'tanggal_generate' => Carbon::now(),
                    'ket'              => ucfirst($entry['ket']),
                ]);
            }
        });

        return redirect()
            ->route('geneqrdin.index')
            ->with(
                'success',
                'QR Code untuk ' . count($entries) . ' pegawai berhasil dibuat.'
            );
    }

    private function dinasPegawaiQuery(User $user, int $idBag, ?int $idSubag)
    {
        $query = Pegawai::where('status', 'Aktif')
            ->where('id_bag', $idBag);

        if ($user->jabatan === 'Manajer') {
            return $idSubag
                ? $query->where('jabatan', 'A.Manajer')->where('id_subag', $idSubag)
                : $query->whereRaw('1 = 0');
        }

        if ($user->jabatan === 'A.Manajer') {
            return $idSubag
                ? $query->where('jabatan', 'Staff')->where('id_subag', $idSubag)
                : $query->whereRaw('1 = 0');
        }

        if ($user->jabatan === 'Direktur') {
            return $idSubag
                ? $query->where('jabatan', 'A.Manajer')->where('id_subag', $idSubag)
                : $query->where('jabatan', 'Manajer');
        }

        if ($idSubag) {
            return $query->where('id_subag', $idSubag);
        }

        return $query->whereRaw('1 = 0');
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
        'waktu_keluar'  => 'required|date_format:H:i',
        'waktu_masuk'   => 'required|date_format:H:i|after:waktu_keluar',
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

        'waktu_keluar' =>
            $validated['waktu_keluar'],

        'waktu_masuk' =>
            $validated['waktu_masuk'],
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
        $qrcode = QrCodes::with(['pegawai.subbag', 'subbag'])->findOrFail($id);
        
        return view('geneqrdin.show', compact('qrcode')); 
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'id_qrcode' => ['required', 'array', 'min:1'],
            'id_qrcode.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('qrcode', 'id_qrcode')
                    ->where('jenis_qr', 'SUBBAG')
                    ->where('is_delete', 0),
            ],
        ]);

        $ids = $validated['id_qrcode'];
        $qrcode = QrCodes::with(['pegawai.subbag.bagian', 'subbag.bagian'])
            ->where('jenis_qr', 'SUBBAG')
            ->whereIn('id_qrcode', $ids)
            ->get();

        QrCodes::where('jenis_qr', 'SUBBAG')
            ->whereIn('id_qrcode', $ids)
            ->update(['tanggal_cetak' => now()]);

        return view('geneqr.print', compact('qrcode'));
    }
}
