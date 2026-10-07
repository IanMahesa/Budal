<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use App\Models\IjinKeluar;
use App\Models\Pegawai;
use App\Models\SubBagians;
use App\Models\QrCodes;
use App\Imports\PegawaiImport;
use Illuminate\Http\Request;
use App\Exports\RekapHarianExport;
use App\Exports\RekapHarianMultiExport;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{    
    public function index()
    {
        $pegawai = Pegawai::with(['subbag.bagian'])
            ->orderByDesc('id_peg')
            ->get();

        return view('pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        $bagian = Bagians::orderBy('bag')->get();

        return view('pegawai.create', compact('bagian'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new PegawaiImport;
            Excel::import($import, $request->file('file'));

            if (!$import->hasImportedRows()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => 'Header tidak ditemukan pada sheet Excel. Pastikan kolom nama dan nik berada di sheet yang berisi data.',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pegawai.index')
                ->withErrors(['file' => 'File tidak dapat diproses. Pastikan format header dan isi file sesuai.']);
        }

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diimport.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'            => 'required|string|max:100',
            'nik'             => 'required|digits_between:5,8|unique:pegawai,nik',
            'jenis_kelamin'   => 'required|in:L,P',
            'jabatan'         => 'required|in:Direktur,Manajer,A.Manajer,Staff',
            'id_subag'        => 'required|exists:subag,id_subag',
            'status'          => 'required|in:Aktif,Tidak Aktif',
        ]);

        $pegawai = Pegawai::create([
            'nama'            => $request->nama,
            'nik'             => $request->nik,
            'jenis_kelamin'   => $request->jenis_kelamin,
            'jabatan'         => $request->jabatan,
            'id_subag'        => $request->id_subag,
            'status'          => $request->status,
            'is_delete'       => 0,
        ]);

        

        return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $pegawai = Pegawai::with('subbag')->findOrFail($id);

        $bagian = Bagians::orderBy('bag')->get();

        $subbag = SubBagians::where('id_bag', $pegawai->subbag->id_bag)
                    ->orderBy('sub_bag')
                    ->get();

        return view('pegawai.edit', compact(
            'pegawai',
            'bagian',
            'subbag'
        ));
    }
        
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama'            => 'required|string|max:100',
            'nik'             => 'required|digits_between:5,8|unique:pegawai,nik,' . $pegawai->id_peg . ',id_peg',
            'jenis_kelamin'   => 'required|in:L,P',
            'jabatan'         => 'required|in:Direktur,Manajer,A.Manajer,Staff',
            'id_subag'        => 'required|exists:subag,id_subag',
            'status'          => 'required|in:Aktif,Tidak Aktif',
        ]);

        $pegawai->update([
            'nama'            => $request->nama,
            'nik'             => $request->nik,
            'jenis_kelamin'   => $request->jenis_kelamin,
            'jabatan'         => $request->jabatan,
            'id_subag'        => $request->id_subag,
            'status'          => $request->status,
        ]);

        return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diubah.');
    }
            
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        // Cek apakah pegawai sudah memiliki transaksi izin
        if ($pegawai->ijinKeluar()->exists()) {
            return redirect()->route('pegawai.index')
                ->with('error', 'Pegawai sudah memiliki transaksi izin sehingga tidak dapat dihapus.');
        }

        // Hapus QR Code yang dimiliki pegawai
        $pegawai->qrcode()->update(['is_delete' => 1]);

        // Hapus pegawai
        $pegawai->is_delete = 1;
        $pegawai->save();

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

        public function show($id)
    {
            //
    }
    
    public function rekap(Request $request, $id = null)
    {
        $pegawai = null;

        $pegawaiQuery = Pegawai::query()->with('subbag.bagian')->orderBy('nama');

        if ($id) {
            $pegawai = $pegawaiQuery->findOrFail($id);
            $pegawaiList = collect([$pegawai]);
        } else {
            $pegawaiList = $pegawaiQuery->get();
        }

        $transaksi = collect();

        foreach ($pegawaiList as $pegawaiItem) {
            $query = IjinKeluar::query()
                ->with(['perijinan', 'pegawai.subbag.bagian'])
                ->where('id_peg', $pegawaiItem->id_peg)
                ->orderBy('tanggal_keluar', 'desc')
                ->orderBy('jam_keluar', 'desc');

            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_keluar', $request->bulan);
            }

            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_keluar', $request->tahun);
            }

            if ($request->filled('jenis')) {
                $query->whereHas('perijinan', function ($q) use ($request) {
                    $q->where('jenis', $request->jenis);
                });
            }

            $items = $query->get();

            if ($items->isEmpty()) {
                $transaksi->push((object) [
                    'id_transaksi' => null,
                    'tanggal_keluar' => null,
                    'jam_keluar' => null,
                    'jam_masuk' => null,
                    'durasi_menit' => null,
                    'foto_pegawai' => null,
                    'pegawai' => $pegawaiItem,
                    'perijinan' => null,
                ]);

                continue;
            }

            foreach ($items as $item) {
                $transaksi->push($item);
            }
        }

        return view('pegawai.rekap', compact('pegawai', 'transaksi'));
    }

    public function exportHarian(Request $request, $id = null)
{
    $pegawaiQuery = Pegawai::query()
        ->with('subbag.bagian')
        ->orderBy('nama');

    if ($id) {
        $pegawaiQuery->where('id_peg', $id);
    }

    $pegawaiList = $pegawaiQuery->get();
    $rows = collect();

    foreach ($pegawaiList as $pegawaiItem) {

        $query = IjinKeluar::query()
            ->with([
                'pegawai.subbag.bagian',
                'perijinan'
            ])
            ->where('id_peg', $pegawaiItem->id_peg)
            ->orderBy('tanggal_keluar', 'desc')
            ->orderBy('jam_keluar', 'desc');

        // Filter bulan
        if ($request->filled('bulan')) {
            $query->whereMonth(
                'tanggal_keluar',
                $request->bulan
            );
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear(
                'tanggal_keluar',
                $request->tahun
            );
        }

        /*
         * JANGAN gunakan filter jenis di sini.
         *
         * Karena export harus selalu menghasilkan:
         * Book1 = Pribadi
         * Book2 = Dinas
         */

        $items = $query->get();

        foreach ($items as $item) {
            $rows->push($item);
        }
    }

    return Excel::download(
        new RekapHarianMultiExport(
            $rows,
            $request->tanggal_awal,
            $request->tanggal_akhir
        ),
        'Rekap_Harian_' . now()->format('Ymd_His') . '.xlsx'
    );
}

    public function getSubBagian($id)
    {
        $subbag = SubBagians::where('id_bag', $id)
                    ->orderBy('sub_bag')
                    ->get();

        return response()->json($subbag);
    }
}
        