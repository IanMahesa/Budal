<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use Illuminate\Http\Request;

class BagianController extends Controller
{
    public function index()
    {
        $bagian = Bagians::orderBy('id_bag', 'desc')->get();

        return view('bagian.index', compact('bagian'));
    }

    public function create()
    {
        return view('bagian.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bag' => 'required|max:100',
            'kode_bag' => 'required|max:10|unique:bagian,kode_bag',
        ]);

        $bagian = Bagians::create([
            'bag' => $request->bag,
            'kode_bag' => strtoupper($request->kode_bag),
            'is_delete' => 0,
        ]);

        // Gabungkan kode bagian dengan id_bag
        $bagian->kode_bag = strtoupper($request->kode_bag) . $bagian->id_bag;
        $bagian->save();

        return redirect()->route('bagian.index')
            ->with('success', 'Data bagian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $bagian = Bagians::findOrFail($id);

        return view('bagian.edit', compact('bagian'));
    }

    public function update(Request $request, $id)
{
    $bagian = Bagians::findOrFail($id);

    $request->validate([
        'bag' => 'required|max:100',
        'kode_bag' => 'required|max:10',
    ]);

    $bagian->update([
        'bag' => $request->bag,
        'kode_bag' => strtoupper($request->kode_bag) . $bagian->id_bag,
    ]);

    return redirect()->route('bagian.index')
        ->with('success', 'Data bagian berhasil diperbarui.');
}

    public function destroy($id)
    {
        $bagian = Bagians::findOrFail($id);

        $bagian->is_delete = 1;
        $bagian->save();

        return redirect()->route('bagian.index')
            ->with('success', 'Data bagian berhasil dihapus.');
    }
}
