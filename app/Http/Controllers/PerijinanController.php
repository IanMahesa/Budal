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
    public function index()
    {
        $perijinan = Perijinan::with('subbag')
            ->orderBy('id_ijin', 'desc')
            ->get();

        return view('ijin.index', compact('perijinan'));
    }

    public function create(Request $request)
    {
        $bagian = Bagians::orderBy('bag')->get();
        $jenis = strtoupper($request->query('jenis', ''));
        $jenis = in_array($jenis, ['PRIBADI', 'DINAS'], true) ? $jenis : null;

        return view('ijin.create', compact('bagian', 'jenis'));
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
        $request->validate([
            'jenis'  => 'required|in:PRIBADI,DINAS',
            'izin' => 'required|string|max:100|unique:perijinan,izin',
            'kode'   => 'required|string|max:5|unique:perijinan,kode',
            'id_subag' => 'required_if:jenis,DINAS|nullable|exists:subag,id_subag',
        ],[
            'jenis.required'      => 'Jenis wajib dipilih.',
            'izin.required'       => 'Nama perijinan wajib diisi.',
            'izin.unique'         => 'Nama perijinan sudah digunakan.',
            'kode.required'       => 'Kode wajib diisi.',
            'kode.unique'         => 'Kode sudah digunakan.',
            'id_subag.required_if'=> 'Sub Bagian wajib dipilih untuk perijinan dinas.',
        ]);

        Perijinan::create([
            'izin'      => $request->izin,
            'kode'      => $request->kode,
            'jenis'     => strtoupper($request->jenis),
            'id_subag'  => $request->jenis == 'DINAS'
                        ? $request->id_subag
                        : null,
            'is_delete' => 0,
        ]);

        return redirect()->route('ijin.index')
            ->with('success', 'Data perijinan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $perijinan = Perijinan::findOrFail($id);

        $bagian = Bagians::orderBy('bag')->get();

        $subbagian = SubBagians::orderBy('sub_bag')->get();

        return view('ijin.edit', compact(
            'perijinan',
            'bagian',
            'subbagian'
        ));
    }

    public function update(Request $request, $id)
    {
        $perijinan = Perijinan::findOrFail($id);

        $request->validate([
            'jenis' => 'required|in:PRIBADI,DINAS',
            'izin'  => ['required', 'string', 'max:100',
                        Rule::unique('perijinan', 'izin')->ignore($perijinan->id_ijin, 'id_ijin'), ],
            'kode'  => ['required', 'string', 'max:5',
                        Rule::unique('perijinan', 'kode')->ignore($perijinan->id_ijin, 'id_ijin'),],
            'id_subag' => 'required_if:jenis,DINAS|nullable|exists:subag,id_subag',

            ],[
            'jenis.required'       => 'Jenis wajib dipilih.',
            'izin.required'        => 'Nama perijinan wajib diisi.',
            'izin.unique'          => 'Nama perijinan sudah digunakan.',
            'kode.required'        => 'Kode wajib diisi.',
            'kode.unique'          => 'Kode sudah digunakan.',
            'id_subag.required_if' => 'Sub Bagian wajib dipilih untuk perijinan dinas.',
            ]);

        $perijinan->update([
            'jenis'    => $request->jenis,
            'izin'     => $request->izin,
            'kode'     => strtoupper($request->kode),
            'id_subag' => $request->jenis == 'DINAS' ? $request->id_subag : null,
        ]);

        return redirect()->route('ijin.index')
            ->with('success', 'Data perijinan berhasil diubah.');
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