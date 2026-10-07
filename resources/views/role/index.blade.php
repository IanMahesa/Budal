@extends('partials.all')

@section('title', 'Data Role')

@section('content')

<div class="card tabel-card">
        <div class="card-body table-responsive">

            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
            <h4 class="mb-0">
                Data Role
            </h4>

            @can('role-create')
                <a href="{{ route('role.create') }}" class="btn btn-tambah">
                    <i class="fas fa-plus"></i>Tambah
                </a>
            @endcan
        </div>

       <div class="card shadow mb-3">

            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    Daftar Role
                </h6>
            </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            @endif

            {{-- Table --}}
            <div class="table-responsive">
                <table id="tabelUser" class="table table-striped table-hover mb-0"
                    width="100%">
                    <thead>
                        <tr>
                            <th width="70" class="text-center">No</th>
                            <th>Nama Role</th>
                            <th class="text-center">Role</th>
                            <th width="150" class="text-center">Status</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="text-center">
                                    {{ ++$i }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="role-icon me-3">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $role->name }}
                                            </div>
                                            <small class="text-muted">
                                                ID Role: {{ $role->id }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $rolePermissions = $role->permissions
                                            ->sortBy('name')
                                            ->groupBy(function ($permission) {
                                                return explode('-', $permission->name, 2)[0];
                                            });
                                    @endphp

                                    @forelse($rolePermissions as $feature => $permissions)
                                        <div class="mb-1">
                                            <strong>{{ ucfirst($feature) }}</strong>
                                            <span class="text-muted">-&gt;</span>
                                            {{ $permissions->map(function ($permission) {
                                                return ucfirst(explode('-', $permission->name, 2)[1] ?? $permission->name);
                                            })->implode(', ') }}
                                        </div>
                                    @empty
                                        <span class="text-muted">Belum ada permission</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    @if($role->is_delete == 1)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Terhapus
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                       
                                        @can('role-edit')
                                            <a href="{{ route('role.edit', $role->id) }}"
                                               class="btn btn-edit btn-sm"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        
                                        @can('role-delete')
                                            @if($role->is_delete != 1)
                                                <form action="{{ route('role.destroy', $role->id) }}"
                                                      method="POST"
                                                      class="delete-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                            onclick="confirmHapusUser(this)"
                                                            class="btn btn-delete btn-sm" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>

                                            @endif

                                        @endcan

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-user-shield fa-3x mb-3 opacity-50"></i>
                                        <h6 class="mb-1">
                                            Belum ada data role
                                        </h6>
                                        <small>
                                            Silakan tambahkan role baru.
                                        </small>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($roles->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Menampilkan
                        <strong>{{ $roles->firstItem() }}</strong>
                        -
                        <strong>{{ $roles->lastItem() }}</strong>
                        dari
                        <strong>{{ $roles->total() }}</strong>
                        data
                    </div>
                    <div>
                        {{ $roles->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>


{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-form').forEach(function (form) {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({
                title: 'Hapus Role?',
                text: 'Role akan ditandai sebagai terhapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        });

    });

});
</script>


@endsection
