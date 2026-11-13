<?php

namespace App\Http\Controllers;

use App\Models\DetailLembur;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class GenLemburController extends Controller
{
    private function detailLemburForUser(int $id): DetailLembur
    {
        $user = auth()->user();
        abort_unless($user->id_bag, 403, 'Akun Anda belum memiliki bagian.');

        return DetailLembur::with(['lembur', 'pegawai.subbag.bagian', 'qrcode'])
            ->where('is_delete', 0)
            ->whereHas('lembur', function ($query) use ($user) {
                $query->where('is_delete', 0)
                    ->where('id_bag', $user->id_bag)
                    ->when($user->id_subag, function ($subbagQuery) use ($user) {
                        $subbagQuery->where('id_subag', $user->id_subag);
                    });
            })
            ->findOrFail($id);
    }

    public function create()
    {
        $user = auth()->user();
        abort_unless($user->id_bag, 403, 'Akun Anda belum memiliki bagian.');

        $bagian = DB::table('bagian')
            ->where('id_bag', $user->id_bag)
            ->where(function ($query) {
                $query->where('is_delete', 0)->orWhereNull('is_delete');
            })
            ->get();

        $subag = DB::table('subag')
            ->where('id_bag', $user->id_bag)
            ->when($user->id_subag, function ($query) use ($user) {
                $query->where('id_subag', $user->id_subag);
            })
            ->where(function ($query) {
                $query->where('is_delete', 0)->orWhereNull('is_delete');
            })
            ->get();

        $pegawai = DB::table('pegawai as p')
            ->join('subag as s', 'p.id_subag', '=', 's.id_subag')
            ->where('p.status', 'Aktif')
            ->where(function ($query) {
                $query->where('p.is_delete', 0)->orWhereNull('p.is_delete');
            })
            ->where(function ($query) {
                $query->where('s.is_delete', 0)->orWhereNull('s.is_delete');
            })
            ->where('s.id_bag', $user->id_bag)
            ->when($user->id_subag, function ($query) use ($user) {
                $query->where('p.id_subag', $user->id_subag);
            })
            ->select('p.id_peg', 'p.nama', 'p.id_subag', 's.id_bag')
            ->selectSub(function ($query) {
                $query->from('qrcode')
                    ->select('id_qrcode')
                    ->whereColumn('qrcode.id_peg', 'p.id_peg')
                    ->where('qrcode.jenis_qr', 'PEGAWAI')
                    ->where('qrcode.status', 'Aktif')
                    ->where(function ($activeQuery) {
                        $activeQuery->where('qrcode.is_delete', 0)
                            ->orWhereNull('qrcode.is_delete');
                    })
                    ->orderByDesc('id_qrcode')
                    ->limit(1);
            }, 'id_qrcode')
            ->orderBy('p.nama')
            ->get();

        $hasSubbag = (bool) $user->id_subag;

        return view('genlembur.create', compact('bagian', 'subag', 'pegawai', 'hasSubbag'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->id_bag, 403, 'Akun Anda belum memiliki bagian.');

        $idBag = (int) $user->id_bag;
        $idSubag = $user->id_subag ? (int) $user->id_subag : null;

        $validator = Validator::make($request->all(), [
            'jenis_lembur' => 'required|in:TERENCANA,DADAKAN',
            'id_peg' => 'required|array|min:1',
            'id_peg.*' => 'required|distinct|exists:pegawai,id_peg',
            'entries' => 'required|array',
            'entries.*.tanggal_lembur' => 'required|date',
            'entries.*.jam_mulai' => 'nullable|date_format:H:i',
            'entries.*.jam_selesai' => 'nullable|date_format:H:i',
            'entries.*.keterangan' => 'nullable|string|max:2000',
        ], [
            'id_bag.required' => 'Bagian wajib dipilih.',
            'id_subag.exists' => 'Subbagian tidak valid.',
            'jenis_lembur.required' => 'Jenis lembur wajib dipilih.',
            'id_peg.required' => 'Pilih minimal satu pegawai.',
            'id_peg.min' => 'Pilih minimal satu pegawai.',
            'id_peg.*.distinct' => 'Pegawai tidak boleh dipilih dua kali.',
            'entries.*.tanggal_lembur.required' => 'Tanggal lembur wajib diisi untuk pegawai yang dipilih.',
            'entries.*.tanggal_lembur.date' => 'Tanggal lembur tidak valid.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->input('id_peg', []) as $idPegawai) {
                $entry = $request->input('entries.' . $idPegawai, []);
                $jamMulai = $entry['jam_mulai'] ?? null;
                $jamSelesai = $entry['jam_selesai'] ?? null;

                if ($request->jenis_lembur === 'TERENCANA') {
                    if (!$jamMulai) {
                        $validator->errors()->add(
                            "entries.{$idPegawai}.jam_mulai",
                            'Jam mulai wajib diisi untuk pegawai yang dipilih pada lembur terencana.'
                        );
                    }

                    if (!$jamSelesai) {
                        $validator->errors()->add(
                            "entries.{$idPegawai}.jam_selesai",
                            'Jam selesai wajib diisi untuk pegawai yang dipilih pada lembur terencana.'
                        );
                    }
                }

                if ($jamMulai && $jamSelesai
                    && Carbon::parse($jamSelesai)->lessThanOrEqualTo(Carbon::parse($jamMulai))) {
                    $validator->errors()->add(
                        "entries.{$idPegawai}.jam_selesai",
                        'Jam selesai harus lebih besar dari jam mulai.'
                    );
                }
            }

        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pegawaiTerpilih = DB::table('pegawai as p')
            ->join('subag as s', 'p.id_subag', '=', 's.id_subag')
            ->whereIn('p.id_peg', $request->input('id_peg', []))
            ->where('p.status', 'Aktif')
            ->where(function ($query) {
                $query->where('p.is_delete', 0)->orWhereNull('p.is_delete');
            })
            ->where('s.id_bag', $idBag)
            ->when($idSubag, function ($query) use ($idSubag) {
                $query->where('p.id_subag', $idSubag);
            })
            ->select('p.id_peg')
            ->selectSub(function ($query) {
                $query->from('qrcode')
                    ->select('id_qrcode')
                    ->whereColumn('qrcode.id_peg', 'p.id_peg')
                    ->where('qrcode.jenis_qr', 'PEGAWAI')
                    ->where('qrcode.status', 'Aktif')
                    ->where(function ($activeQuery) {
                        $activeQuery->where('qrcode.is_delete', 0)
                            ->orWhereNull('qrcode.is_delete');
                    })
                    ->orderByDesc('id_qrcode')
                    ->limit(1);
            }, 'id_qrcode')
            ->get();

        if ($pegawaiTerpilih->count() !== count($request->input('id_peg', []))) {
            return back()->withErrors([
                'id_peg' => 'Pegawai yang dipilih harus aktif dan sesuai bagian/subbagian.',
            ])->withInput();
        }

        if ($pegawaiTerpilih->contains(function ($pegawai) {
            return !$pegawai->id_qrcode;
        })) {
            return back()->withErrors([
                'id_peg' => 'Setiap pegawai yang dipilih harus memiliki QR Code pegawai aktif.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $pegawaiTerpilih, $idBag, $idSubag, $user) {
                foreach ($pegawaiTerpilih as $pegawai) {
                    $entry = $request->input('entries.' . $pegawai->id_peg);
                    $jamMulai = $entry['jam_mulai'] ?? null;
                    $jamSelesai = $entry['jam_selesai'] ?? null;
                    $durasi = $jamMulai && $jamSelesai
                        ? Carbon::parse($jamSelesai)->diffInMinutes(Carbon::parse($jamMulai))
                        : null;

                    $lembur = Lembur::create([
                        'id_bag' => $idBag,
                        'id_subag' => $idSubag,
                        'tanggal_lembur' => $entry['tanggal_lembur'],
                        'jenis_lembur' => $request->jenis_lembur,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'durasi_menit' => $durasi,
                        'keterangan' => $entry['keterangan'] ?? null,
                        'status' => 'Terjadwal',
                        'created_by' => $user->id,
                        'is_delete' => 0,
                    ]);

                    DetailLembur::create([
                        'id_lembur' => $lembur->id_lembur,
                        'id_peg' => $pegawai->id_peg,
                        'id_qrcode' => $pegawai->id_qrcode,
                        'status_kehadiran' => 'Belum Hadir',
                        'is_delete' => 0,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Gagal membuat data QR lembur. Silakan coba kembali.');
        }

        return redirect()
            ->route('genlembur.index')
            ->with('success', 'Data lembur dan QR peserta berhasil dibuat.');
    }

    public function index()
    {
        $lembur = DetailLembur::with([
            'lembur',
            'pegawai.subbag',
            'qrcode',
        ])
            ->where('is_delete', 0)
            ->whereHas('lembur', function ($query) {
                $query->where('is_delete', 0);
            })
            ->orderByDesc('id_detail_lembur')
            ->get();

        return view('genlembur.index', compact('lembur'));
    }

    public function show(int $id)
    {
        $user = auth()->user();
        abort_unless($user->id_bag, 403, 'Akun Anda belum memiliki bagian.');

        $detailLembur = DetailLembur::with(['lembur', 'pegawai.subbag.bagian', 'qrcode'])
            ->where('id_qrcode', $id)
            ->where('is_delete', 0)
            ->whereHas('lembur', function ($query) use ($user) {
                $query->where('is_delete', 0)
                    ->where('id_bag', $user->id_bag)
                    ->when($user->id_subag, function ($subbagQuery) use ($user) {
                        $subbagQuery->where('id_subag', $user->id_subag);
                    });
            })
            ->firstOrFail();

        $qrcode = $detailLembur->qrcode;
        abort_unless($qrcode, 404);

        return view('genlembur.show', compact('qrcode', 'detailLembur'));
    }

    public function edit(int $id)
    {
        $detailLembur = $this->detailLemburForUser($id);

        return view('genlembur.edit', compact('detailLembur'));
    }

    public function update(Request $request, int $id)
    {
        $detailLembur = $this->detailLemburForUser($id);

        $validator = Validator::make($request->all(), [
            'jenis_lembur' => 'required|in:TERENCANA,DADAKAN',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:2000',
        ], [
            'jenis_lembur.required' => 'Jenis lembur wajib dipilih.',
            'tanggal_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tanggal_lembur.date' => 'Tanggal lembur tidak valid.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->jenis_lembur === 'TERENCANA') {
                if (!$request->jam_mulai) {
                    $validator->errors()->add('jam_mulai', 'Jam mulai wajib diisi untuk lembur terencana.');
                }

                if (!$request->jam_selesai) {
                    $validator->errors()->add('jam_selesai', 'Jam selesai wajib diisi untuk lembur terencana.');
                }
            }

            if ($request->jam_mulai && $request->jam_selesai
                && Carbon::parse($request->jam_selesai)->lessThanOrEqualTo(Carbon::parse($request->jam_mulai))) {
                $validator->errors()->add('jam_selesai', 'Jam selesai harus lebih besar dari jam mulai.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $jamMulai = $request->jenis_lembur === 'TERENCANA' ? $request->jam_mulai : null;
        $jamSelesai = $request->jenis_lembur === 'TERENCANA' ? $request->jam_selesai : null;

        try {
            DB::transaction(function () use ($detailLembur, $request, $jamMulai, $jamSelesai) {
                $detailLembur->lembur->update([
                    'tanggal_lembur' => $request->tanggal_lembur,
                    'jenis_lembur' => $request->jenis_lembur,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'durasi_menit' => $jamMulai && $jamSelesai
                        ? Carbon::parse($jamSelesai)->diffInMinutes(Carbon::parse($jamMulai))
                        : null,
                    'keterangan' => $request->keterangan,
                    'updated_by' => $request->user()->id,
                ]);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Gagal memperbarui data lembur. Silakan coba kembali.');
        }

        return redirect()
            ->route('genlembur.index')
            ->with('success', 'Data lembur berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $detailLembur = $this->detailLemburForUser($id);

        DB::transaction(function () use ($detailLembur) {
            $detailLembur->update(['is_delete' => 1]);

            $masihAdaPeserta = DetailLembur::where('id_lembur', $detailLembur->id_lembur)
                ->where('is_delete', 0)
                ->exists();

            if (!$masihAdaPeserta) {
                $detailLembur->lembur->update(['is_delete' => 1]);
            }
        });

        return redirect()
            ->route('genlembur.index')
            ->with('success', 'Peserta lembur berhasil dihapus.');
    }
}
