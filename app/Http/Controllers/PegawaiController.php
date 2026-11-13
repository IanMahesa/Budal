<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use App\Models\IjinKeluar;
use App\Models\Pegawai;
use App\Models\SubBagians;
use App\Models\QrCodes;
use App\Models\User;
use App\Imports\PegawaiImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Exports\RekapHarianExport;
use App\Exports\RekapHarianMultiExport;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pegawai-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:pegawai-create', ['only' => ['create', 'store', 'import']]);
        $this->middleware('permission:pegawai-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pegawai-delete', ['only' => ['destroy']]);
        $this->middleware('permission:pegawai-create|pegawai-edit', ['only' => ['getSubBagian']]);
        $this->middleware('permission:rekap-list', ['only' => ['rekap', 'exportHarian']]);
    }

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
        $validated = $request->validate([
            'nama'            => 'required|string|max:100',
            'nik'             => 'required|digits_between:5,8|unique:pegawai,nik',
            'jenis_kelamin'   => 'required|in:L,P',
            'jabatan'         => 'required|in:Direktur,Manajer,A.Manajer,Staff',
            'id_bag'          => 'nullable|required_unless:jabatan,Direktur|exists:bagian,id_bag',
            'id_subag'        => 'nullable|required_if:jabatan,A.Manajer,Staff|exists:subag,id_subag',
            'status'          => 'required|in:Aktif,Tidak Aktif',
        ]);
        $validated = $this->validatePegawaiOrganization($validated);

        DB::transaction(function () use ($validated) {
            $pegawai = Pegawai::create([
                'nama'            => $validated['nama'],
                'nik'             => $validated['nik'],
                'jenis_kelamin'   => $validated['jenis_kelamin'],
                'jabatan'         => $validated['jabatan'],
                'id_bag'          => $validated['id_bag'],
                'id_subag'        => $validated['id_subag'],
                'status'          => $validated['status'],
                'is_delete'       => 0,
            ]);

            $this->syncUserForPegawai($pegawai);
            $this->createQrCodeForPegawai($pegawai);
        });

        return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $pegawai = Pegawai::with('subbag')->findOrFail($id);

        $bagian = Bagians::orderBy('bag')->get();
        $idBag = $pegawai->id_bag ?: optional($pegawai->subbag)->id_bag;

        $subbag = SubBagians::where('id_bag', $idBag)
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

        $validated = $request->validate([
            'nama'            => 'required|string|max:100',
            'nik'             => 'required|digits_between:5,8|unique:pegawai,nik,' . $pegawai->id_peg . ',id_peg',
            'jenis_kelamin'   => 'required|in:L,P',
            'jabatan'         => 'required|in:Direktur,Manajer,A.Manajer,Staff',
            'id_bag'          => 'nullable|required_unless:jabatan,Direktur|exists:bagian,id_bag',
            'id_subag'        => 'nullable|required_if:jabatan,A.Manajer,Staff|exists:subag,id_subag',
            'status'          => 'required|in:Aktif,Tidak Aktif',
        ]);
        $validated = $this->validatePegawaiOrganization($validated);

        DB::transaction(function () use ($pegawai, $validated) {
            $pegawai->update([
                'nama'            => $validated['nama'],
                'nik'             => $validated['nik'],
                'jenis_kelamin'   => $validated['jenis_kelamin'],
                'jabatan'         => $validated['jabatan'],
                'id_bag'          => $validated['id_bag'],
                'id_subag'        => $validated['id_subag'],
                'status'          => $validated['status'],
            ]);

            QrCodes::withoutGlobalScopes()
                ->where('id_peg', $pegawai->id_peg)
                ->where('jenis_qr', 'PEGAWAI')
                ->update(['id_subag' => $pegawai->id_subag]);

            $this->syncUserForPegawai($pegawai);
        });

        return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diubah.');
    }

    private function validatePegawaiOrganization(array $validated): array
    {
        $validated['id_bag'] = $validated['id_bag'] ?? null;
        $validated['id_subag'] = $validated['id_subag'] ?? null;

        if ($validated['jabatan'] === 'Manajer') {
            $validated['id_subag'] = null;
        } elseif ($validated['jabatan'] === 'Direktur' && $validated['id_subag']) {
            $subbag = SubBagians::findOrFail($validated['id_subag']);
            $validated['id_bag'] = $validated['id_bag'] ?: $subbag->id_bag;
        }

        if ($validated['id_subag']) {
            $subbagBelongsToBagian = SubBagians::where('id_subag', $validated['id_subag'])
                ->where('id_bag', $validated['id_bag'])
                ->exists();

            if (!$subbagBelongsToBagian) {
                throw ValidationException::withMessages([
                    'id_subag' => 'Sub bagian tidak sesuai dengan bagian yang dipilih.',
                ]);
            }
        }

        return $validated;
    }

    private function syncUserForPegawai(Pegawai $pegawai): void
    {
        $pegawai->load('subbag');
        $user = User::where('id_peg', $pegawai->id_peg)->first();

        if (!$user) {
            $user = User::whereNull('id_peg')
                ->where('username', $pegawai->nik)
                ->first();
        }

        $isNewUser = !$user;
        $user = $user ?: new User;

        $user->id_peg = $pegawai->id_peg;
        $user->name = $pegawai->nama;
        $user->username = $pegawai->nik;
        $user->jabatan = $pegawai->jabatan;
        $user->id_bag = in_array($pegawai->jabatan, ['Manajer', 'A.Manajer', 'Staff'], true)
            ? ($pegawai->id_bag ?: optional($pegawai->subbag)->id_bag)
            : null;
        $user->id_subag = in_array($pegawai->jabatan, ['A.Manajer', 'Staff'], true)
            ? $pegawai->id_subag
            : null;

        if ($isNewUser) {
            $user->password = Hash::make('12345678');
            $user->is_delete = 0;
        }

        $user->save();
    }

    private function createQrCodeForPegawai(Pegawai $pegawai): void
    {
        $nomorKartu = strtoupper($pegawai->nik);

        if (QrCodes::withoutGlobalScopes()->where('nomor_kartu', $nomorKartu)->exists()) {
            throw ValidationException::withMessages([
                'nik' => 'NIK sudah digunakan sebagai nomor QR Code.',
            ]);
        }

        QrCodes::create([
            'jenis_qr' => 'PEGAWAI',
            'nama_kartu' => strtoupper($pegawai->nama),
            'nomor_kartu' => $nomorKartu,
            'id_peg' => $pegawai->id_peg,
            'id_subag' => $pegawai->id_subag,
            'status' => $pegawai->status === 'Aktif' ? 'Aktif' : 'Nonaktif',
            'tanggal_generate' => now(),
            'is_delete' => 0,
        ]);
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
        