@extends('partials.all')

@section('title', 'Manajemen User')

@section('content')

<div class="card tabel-card">
        <div class="card-body table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
            <h4>Manajemen User</h4>
                @can('user-create')
                <a href="{{ route('user.create') }}" class="btn btn-tambah">
                    <i class="fas fa-plus"></i>Tambah
                </a>
                @endcan
</div>

<div class="card shadow mb-3">

    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            Daftar User
        </h6>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabelUser" class="table table-striped table-hover mb-0"
                   width="100%">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th width="18%">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $key => $user)
                        <tr>
                            <td class="text-center">
                                {{ $key + 1 }}
                            </td>
                            <td>
                                {{ $user->name }}
                            </td>
                            <td>
                                {{ $user->username }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    {{ $user->role->name ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @can('user-edit')
                                <a href="{{ route('user.edit', $user->id) }}"
                                   class="btn btn-edit btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                <form action="{{ route('user.destroy', $user->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    @can('user-delete')
                                    <button type="button"
                                            onclick="confirmHapusUser(this)"
                                            class="btn btn-delete btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                                </form>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">
                                Belum ada data user.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

@endsection

@section('scripts')

<script>
$(document).ready(function () {

    $('#datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 10,
        ordering: true,
        searching: true
    });

});
</script>

@endsection
