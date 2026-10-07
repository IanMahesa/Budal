<?php

namespace App\Http\Controllers;

use App\Models\IjinKeluar;
use Illuminate\Http\Request;

class NotifikasiIjinController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifikasi-list');
    }

    public function index()
    {
        $notifikasi = IjinKeluar::with([
            'pegawai',
            'subbag',
            'perijinan',
            'user',
        ])
            ->whereNull('notification_read_at')
            ->orderByDesc('id_transaksi')
            ->get();

        return view('notifikasi.index', ['notif' => $notifikasi]);
    }

    public function read(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:ijin_keluar,id_transaksi'],
        ]);

        IjinKeluar::whereKey($request->integer('id'))
            ->update(['notification_read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function readAll(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:ijin_keluar,id_transaksi'],
        ]);

        IjinKeluar::whereIn('id_transaksi', $request->input('ids'))
            ->update(['notification_read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
