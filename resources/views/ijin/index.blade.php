@extends('partials.all')

@section('title', 'Ijin Pribadi')

@section('content')
<div class="card tabel-card">
    <div class="card-body table-responsive">
        <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
            <h4>Master Ijin Pribadi</h4>
            @can('perijinan-create')
            <a href="{{ route('ijin.create') }}" class="btn btn-tambah">
                <i class="fas fa-plus"></i> Tambah
            </a>
            @endcan
        </div>

        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    Daftar Ijin Pribadi
                </h6>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="tabelUser" class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Jenis Ijin</th>
                                <th width="150">Kode</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($perijinan as $i)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $i->izin }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $i->kode }}</span></td>
                                    <td class="text-center">
                                        @can('perijinan-edit')
                                        <a href="{{ route('ijin.edit', $i->id_ijin) }}" class="btn btn-edit btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        <form action="{{ route('ijin.destroy', $i->id_ijin) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            @can('perijinan-delete')
                                            <button type="button" onclick="confirmHapusUser(this)" class="btn btn-delete btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">Belum ada perijinan pribadi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection