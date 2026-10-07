<?php

namespace App\Http\Controllers;

use App\Models\Bagians;
use App\Models\SubBagians;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubagController extends Controller
{
     public function index()
    {
        $bagian = Bagians::with([
            'subag.pegawai'
        ])->get();

        return view('subbagian.index', compact('bagian'));
    }

    public function create(Request $request)
    {
        $selectedBagian = $request->query('id_bag');
        
        if ($selectedBagian) {
            $bagian = Bagians::findOrFail($selectedBagian);
        } else {
            $bagian = Bagians::orderBy('bag')->first();
        }

        return view('subbagian.create', compact('bagian'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'id_bag' => 'required|exists:bagian,id_bag',

            'sub_bag' => [
                'required',
                'max:100',
                Rule::unique('subag')->where(function ($query) use ($request) {
                    return $query->where('id_bag', $request->id_bag);
                }),
            ],

            'kode_subag' => 'required|max:10',
        ]);

        $kodeDasar = strtoupper($request->kode_subag);

        $subag = SubBagians::create([
            'id_bag'     => $request->id_bag,
            'sub_bag'    => $request->sub_bag,
            'kode_subag' => $kodeDasar,
            'is_delete'  => 0,
        ]);

        // ID sudah didapat dari database
        $subag->kode_subag = $kodeDasar . '-' . str_pad($subag->id_subag, 3, '0', STR_PAD_LEFT);
        $subag->save();

        return redirect()
            ->route('subag.index')
            ->with('success', 'Data Sub Bagian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $subag = SubBagians::findOrFail($id);

        $bagian = Bagians::findOrFail($subag->id_bag);

        return view('subbagian.edit', compact('subag', 'bagian'));
    }

    public function update(Request $request, $id)
    {
        $subag = SubBagians::findOrFail($id);

        $request->validate([
            'id_bag'        => 'required|exists:bagian,id_bag',
            'sub_bag' => ['required', 'max:100',
                Rule::unique('subag')
                    ->ignore($subag->id_subag, 'id_subag')
                    ->where(function ($query) use ($request) {
                        return $query->where('id_bag', $request->id_bag);
                    }),
            ],
            'kode_subag' => ['required', 'max:10',
                Rule::unique('subag', 'kode_subag')
                    ->ignore($subag->id_subag, 'id_subag')
            ],
        ]);

        $kodeDasar = strtoupper($request->kode_subag);
        $kodeDasar = preg_replace('/-\d{3}$/', '', $kodeDasar);

        $kodeFinal = $kodeDasar . '-' .
                 str_pad($subag->id_subag, 3, '0', STR_PAD_LEFT);

        $subag->update([
            'id_bag'     => $request->id_bag,
            'sub_bag'    => $request->sub_bag,
            'kode_subag' => $kodeFinal,
        ]);

        return redirect()->route('subag.index')
            ->with('success', 'Data subbagian berhasil diubah.');
    }

    public function destroy($id)
    {
        $subag = SubBagians::findOrFail($id);

        if ($subag->pegawai()->count() > 0) {
        return redirect()->route('subag.index')
            ->with('error', 'Sub Bagian tidak dapat dihapus karena masih memiliki pegawai.');
    }

        $subag->is_delete = 1;
        $subag->save();

        return redirect()->route('subag.index')
            ->with('success', 'Data subbagian berhasil dihapus.');
    }

    public function getSubag(Request $request, $id_bag = null)
    {
        $id_bag = $request->query('id_bag', $id_bag);

        $subag = SubBagians::query();

        if (!empty($id_bag)) {
            $subag->where('id_bag', $id_bag);
        }

        $subag = $subag->orderBy('sub_bag')
            ->select('id_subag', 'sub_bag', 'kode_subag', 'id_bag')
            ->get();

        // attach kode_bag from parent bagian
        $subag = $subag->map(function ($item) {
            $kodeBag = '';
            if ($item->id_bag) {
                $bag = Bagians::find($item->id_bag);
                if ($bag) $kodeBag = $bag->kode_bag;
            }
            return [
                'id_subag' => $item->id_subag,
                'sub_bag' => $item->sub_bag,
                'kode_subag' => $item->kode_subag,
                'kode_bag' => $kodeBag,
            ];
        });

        return response()->json($subag);
    }
}
