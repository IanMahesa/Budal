<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Roles;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-list', ['only' => ['show']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

   public function index(Request $request)
    {
        $roles = Roles::with('permissions')->where(function ($query) {
                $query->where('is_delete', 0)
                    ->orWhereNull('is_delete');
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('role.index', compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $permissionMatrix = $this->permissionMatrix();
        $permissionNames = $this->ensurePermissionMatrixPermissions($permissionMatrix);

        $permissions = Permission::whereIn('name', $permissionNames)->get();

        return view('role.create', [
            'permissions' => $permissions,
            'permissionMatrix' => $permissionMatrix,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Roles::create([
            'name' => $request->input('name'),
            'guard_name' => 'web',
            'is_delete' => 0,
        ]);
        $role->syncPermissions($this->expandManagePermissions($request->input('permissions', [])));

        return redirect()->route('role.index')
            ->with('success', 'Role created successfully');
    }

    public function show($id)
    {
        $role = Roles::findOrFail($id);
        $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $id)
            ->get();

        return view('role.show', compact('role', 'rolePermissions'));
    }

    public function edit($id)
    {
        $role = Roles::findOrFail($id);
        $permissionMatrix = $this->permissionMatrix();
        $this->ensurePermissionMatrixPermissions($permissionMatrix);
        $permission = Permission::get();
        $rolePermissions = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $id)
            ->pluck('permissions.name')
            ->all();

        // Kelompokkan dan urutkan sesuai config/permissions.php
        $grouped = $this->groupAndSortPermissions($permission);

        return view('role.edit', [
            'role' => $role,
            'permission' => $permission,
            'rolePermissions' => $rolePermissions,
            'groupedPermissions' => $grouped,
            'permissionMatrix' => $permissionMatrix,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Roles::findOrFail($id);
        $role->name = $request->input('name');
        $role->guard_name = 'web';
        $role->is_delete = 0;
        $role->save();
        $role->syncPermissions($this->expandManagePermissions($request->input('permissions', [])));

        return redirect()->route('role.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        try {
            $role = Roles::findOrFail($id);
            $role->is_delete = 1;
            $role->save();

            return redirect()->route('role.index')
                ->with('success', 'Role berhasil ditandai terhapus.');
        } catch (\Exception $e) {
            return redirect()->route('role.index')->with('error', 'Gagal menghapus role.');
        }
    }

     private function groupAndSortPermissions($permissions)
    {
        $collection = $permissions instanceof \Illuminate\Support\Collection
            ? $permissions
            : collect($permissions);

        $groupsOrder = config('permissions.groups_order', []);
        $itemsOrder = config('permissions.items_order', []);

        $grouped = $collection->groupBy(function ($item) {
            return explode('-', $item->name)[0];
        });

        $sorted = collect();

        // Tambahkan grup sesuai urutan di config
        foreach ($groupsOrder as $group) {
            if ($grouped->has($group)) {
                $sortedGroup = $grouped->get($group)->sortBy(function ($perm) use ($itemsOrder, $group) {
                    $name = $perm->name;
                    $pos = strpos($name, '-');
                    $suffix = $pos !== false ? substr($name, $pos + 1) : $name;
                    $order = $itemsOrder[$group] ?? [];
                    $idx = array_search($suffix, $order, true);
                    return $idx === false ? 1000 + crc32($name) : $idx;
                })->values();
                $sorted->put($group, $sortedGroup);
            }
        }

        // Tambahkan sisa grup yang tidak ada di config (urut alfabet)
        $remaining = $grouped->keys()->diff($groupsOrder)->sort();
        foreach ($remaining as $group) {
            $sorted->put($group, $grouped->get($group)->sortBy('name')->values());
        }

        return $sorted;
    }

    private function permissionMatrix()
    {
        $features = [
            'dashboard', 'user', 'role', 'bagian', 'subag', 'pegawai', 'perijinan',
            'geneqr', 'scan', 'keluar', 'rekap', 'rekappeg', 'notifikasi', 'qrcode',
        ];

        return collect($features)->mapWithKeys(function ($feature) {
            if ($feature === 'user') {
                return [$feature => [
                    'create' => 'user-create',
                    'list' => 'user-list',
                    'update' => 'user-edit',
                    'delete' => 'user-delete',
                    'manage' => null,
                ]];
            }

            if ($feature === 'role') {
                return [$feature => [
                    'create' => 'role-create',
                    'list' => 'role-list',
                    'update' => 'role-edit',
                    'delete' => 'role-delete',
                    'manage' => null,
                ]];
            }

            if ($feature === 'bagian') {
                return [$feature => [
                    'create' => 'bagian-create',
                    'list' => 'bagian-list',
                    'update' => 'bagian-edit',
                    'delete' => 'bagian-delete',
                    'manage' => null,
                ]];
            }

            if ($feature === 'subag') {
                return [$feature => [
                    'create' => 'subag-create',
                    'list' => 'subag-list',
                    'update' => 'subag-edit',
                    'delete' => 'subag-delete',
                    'manage' => null,
                ]];
            }

            if ($feature === 'scan') {
                return [$feature => [
                    'create' => null,
                    'list' => 'scan-list',
                    'update' => null,
                    'delete' => null,
                    'manage' => null,
                ]];
            }
        
            if ($feature === 'dashboard') {
                return [$feature => [
                    'create' => null,
                    'list' => 'dashboard-list',
                    'update' => null,
                    'delete' => null,
                    'manage' => null,
                ]];
            }

            if ($feature === 'rekappeg') {
                return [$feature => [
                    'create' => null,
                    'list' => 'rekappeg-list',
                    'update' => null,
                    'delete' => null,
                    'manage' => null,
                ]];
            }

            return [$feature => [
                'create' => $feature . '-create',
                'list' => $feature === 'role' ? 'role-list' : $feature . '-list',
                'update' => $feature === 'role' ? 'role-edit' : $feature . '-edit',
                'delete' => $feature . '-delete',
                'manage' => $feature . '-manage',
            ]];
        })->all();
    }

    private function ensurePermissionMatrixPermissions(array $permissionMatrix)
    {
        $permissionNames = collect($permissionMatrix)
            ->flatMap(function ($actions) {
                return array_values($actions);
            })
            ->filter()
            ->unique()
            ->values();

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        return $permissionNames;
    }

    private function expandManagePermissions(array $permissions): array
    {
        $expanded = collect($permissions);

        foreach ($permissions as $permission) {
            if (!str_ends_with($permission, '-manage')) {
                continue;
            }

            $feature = substr($permission, 0, -7);
            $expanded = $expanded->merge([
                $feature . '-create',
                $feature . '-list',
                $feature . '-edit',
                $feature . '-delete',
            ]);
        }

        return $expanded->unique()->values()->all();
    }
}