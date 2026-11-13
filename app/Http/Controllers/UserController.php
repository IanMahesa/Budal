<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Models\Bagians;
use App\Models\SubBagians;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $users = User::with('role')
        ->where(function ($query) {
                $query->where('is_delete', 0)
                    ->orWhereNull('is_delete');
            })
        ->where(function ($query) {
            $query->whereHas('pegawai', function ($pegawaiQuery) {
                $pegawaiQuery->where('status', 'Aktif')
                    ->where('is_delete', 0);
            })->orWhere(function ($unlinkedQuery) {
                $unlinkedQuery->whereNull('id_peg')
                    ->whereNotExists(function ($pegawaiQuery) {
                        $pegawaiQuery->selectRaw('1')
                            ->from('pegawai')
                            ->whereColumn('pegawai.nik', 'users.username')
                            ->where(function ($inactiveQuery) {
                                $inactiveQuery->where('pegawai.status', '!=', 'Aktif')
                                    ->orWhere('pegawai.is_delete', '!=', 0)
                                    ->orWhereNull('pegawai.is_delete');
                            });
                    });
            });
        })
        ->orderBy('id', 'desc')->paginate(10);

        return view('user.index', compact('users'));
    }

    public function create()
    {
        $roles = Roles::where('is_delete', 0)
            ->orWhereNull('is_delete')
            ->orderBy('name')
            ->get();

        $pegawai = Pegawai::where('status', 'Aktif')
            ->whereDoesntHave('user')
            ->whereNotIn('nik', User::select('username'))
            ->orderBy('nama')
            ->get(['id_peg', 'nama', 'nik']);

        return view('user.create', compact('roles', 'pegawai'));
    }

    public function store(Request $request)
    {
        $pegawaiData = $request->validate([
            'id_peg' => [
                'required',
                'exists:pegawai,id_peg,status,Aktif',
                Rule::unique('users', 'id_peg'),
            ],
        ]);
        $pegawai = Pegawai::where('status', 'Aktif')
            ->findOrFail($pegawaiData['id_peg']);
        $pegawai->load('subbag');

        $idBag = in_array($pegawai->jabatan, ['Manajer', 'A.Manajer', 'Staff'], true)
            ? ($pegawai->id_bag ?: optional($pegawai->subbag)->id_bag)
            : null;
        $idSubag = in_array($pegawai->jabatan, ['A.Manajer', 'Staff'], true)
            ? $pegawai->id_subag
            : null;

        $request->merge([
            'name' => $pegawai->nama,
            'username' => $pegawai->nik,
            'jabatan' => $pegawai->jabatan,
            'id_bag' => $idBag,
            'id_subag' => $idSubag,
        ]);

        $validated = $this->validateUser($request);

        $role = Roles::findOrFail($validated['role']);
        $user = User::create([
            'id_peg' => $pegawai->id_peg,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'id_role' => $role->id,
            'jabatan' => $validated['jabatan'],
            'id_bag' => $validated['id_bag'] ?? null,
            'id_subag' => $validated['id_subag'] ?? null,
            'is_delete' => 0,
        ]);
        $user->assignRole($role);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $usernameRule = $user
            ? Rule::unique('users', 'username')->ignore($user->id)
            : 'unique:users,username';

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', $usernameRule],
            'password' => $user ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,id',
            'jabatan' => 'required|in:Direktur,Manajer,A.Manajer,Staff',
            'id_bag' => 'nullable|required_unless:jabatan,Direktur|exists:bagian,id_bag',
            'id_subag' => 'nullable|required_if:jabatan,A.Manajer,Staff|exists:subag,id_subag',
        ]);

        if ($validated['jabatan'] === 'Direktur') {
            $validated['id_bag'] = null;
            $validated['id_subag'] = null;
        } elseif ($validated['jabatan'] === 'Manajer') {
            $validated['id_subag'] = null;
        }

        if (!empty($validated['id_subag'])) {
            $subbagBelongsToBagian = SubBagians::where('id_subag', $validated['id_subag'])
                ->where('id_bag', $validated['id_bag'])
                ->exists();

            if (!$subbagBelongsToBagian) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'id_subag' => 'Sub bagian tidak sesuai dengan bagian yang dipilih.',
                ]);
            }
        }

        return $validated;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Roles::where('is_delete', 0)
            ->orWhereNull('is_delete')
            ->orderBy('name')
            ->get();
        $bagian = Bagians::orderBy('bag')->get();

        return view('user.edit', compact('user', 'roles', 'bagian'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,id',
        ]);

        $role = Roles::findOrFail($validated['role']);
        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->id_role = $role->id;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        $user->syncRoles($role);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->is_delete = 1;
        $user->save();

        return redirect()->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}