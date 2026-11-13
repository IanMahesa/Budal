<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Perijinan;
use App\Models\Bagians;
use App\Models\SubBagians;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerijinanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:perijinan-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:perijinan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:perijinan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:perijinan-delete', ['only' => ['destroy']]);
    }

   public function index()
    {
       $perijinan = Perijinan::orderBy('id_ijin', 'desc')->get();

        return view('ijin.index', compact('perijinan'));
    }

    public function create()
    {
        return view('ijin.create');
    }

    public function getPegawaiBySubag($id_subag)
    {
        $pegawai = Pegawai::where('id_subag', $id_subag)
            ->orderBy('nama')
            ->select('id_peg', 'nama', 'nik')
            ->get();

        return response()->json($pegawai);
    }

    public function show($id)
    {
        $perijinan = Perijinan::with('subbag')->findOrFail($id);

        return view('ijin.show', compact('perijinan'));
    }

    public function store(Request $request)
    {
       $validated = $request->validate([
            'izin' => 'required|string|max:100|unique:perijinan,izin',
            'kode' => 'required|string|max:5|unique:perijinan,kode',
        ], [
            'izin.required' => 'Jenis ijin wajib diisi.',
            'izin.string'   => 'Jenis ijin harus berupa teks.',
            'izin.max'      => 'Jenis ijin maksimal 100 karakter.',
            'izin.unique'   => 'Jenis ijin sudah terdaftar.',

            'kode.required' => 'Kode wajib diisi.',
            'kode.string'   => 'Kode harus berupa teks.',
            'kode.max'      => 'Kode maksimal 5 karakter.',
            'kode.unique'   => 'Kode sudah digunakan.',
        ]);

        // Simpan data
        Perijinan::create([
            'jenis' => 'PRIBADI',
            'izin' => $validated['izin'],
            'kode' => strtoupper($validated['kode']),
        ]);

        // Kembali dengan pesan sukses
        return redirect()
            ->route('ijin.index')
            ->with('success', 'Data ijin pribadi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $i = Perijinan::findOrFail($id);

        return view('ijin.edit', compact('i'));
    }

    public function update(Request $request, $id)
    {
        $i = Perijinan::findOrFail($id);

        $validated = $request->validate([
            'izin' => [
                'required',
                'string',
                'max:255',
            ],
            'kode' => [
                'required',
                'string',
                'max:5',
                Rule::unique('perijinan', 'kode')->ignore($i->id_ijin, 'id_ijin'),
            ],
        ], [
            'izin.required' => 'Jenis ijin wajib diisi.',
            'izin.string'   => 'Jenis ijin harus berupa teks.',
            'izin.max'      => 'Jenis ijin maksimal 255 karakter.',
            'kode.required' => 'Kode wajib diisi.',
            'kode.string'   => 'Kode harus berupa teks.',
            'kode.max'      => 'Kode maksimal 5 karakter.',
            'kode.unique'   => 'Kode sudah digunakan, silakan gunakan kode lain.',
        ]);

        $i->izin = $validated['izin'];
        $i->kode = strtoupper($validated['kode']);
        $i->save();

        return redirect()
            ->route('ijin.index')
            ->with('success', 'Data ijin pribadi berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $perijinan = Perijinan::findOrFail($id);

        if ($perijinan->ijinKeluar()->count() > 0) {
            return redirect()
                ->route('ijin.index')
                ->with('error', 'Data tidak dapat dihapus karena masih digunakan.');
        }

        $perijinan->is_delete = 1;
        $perijinan->save();

        return redirect()->route('ijin.index')
            ->with('success', 'Data perijinan berhasil dihapus.');
    }
}