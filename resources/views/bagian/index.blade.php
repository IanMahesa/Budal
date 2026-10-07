@extends('partials.all')

@section('title','Bagian')

@section('content')
<div class="card tabel-card">
        <div class="card-body table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
            <h4>Data Bagian</h4>
                @can('bagian-create')
                <a href="{{ route('bagian.create') }}" class="btn btn-tambah">
                    <i class="fas fa-plus"></i>Tambah
                </a>
                @endcan
</div>

<div class="card shadow mb-3">

    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            Daftar Bagian
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
    <table id="tabelUser" class="table table-striped table-hover mb-0">
        <thead>
            <tr>
                <th width="60">No</th>
                <th>Nama Bagian</th>
                <th>Kode Bagian</th>
                <th width="180">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bagian as $key => $item)
                <tr>
                    <td class="text-center">{{ $key+1 }}</td>
                    <td>{{ $item->bag }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary">
                            {{ $item->kode_bag }}
                        </span>
                    </td>                    
                    
                    <td class="text-center">
                        @can('bagian-edit')
                        <a href="{{ route('bagian.edit',$item->id_bag) }}"
                             class="btn btn-edit btn-sm" title="Edit">
                             <i class="fas fa-edit"></i>
                        </a>
                    @endcan
                            <form action="{{ route('bagian.destroy',$item->id_bag) }}"
                                method="POST"
                                class="d-inline form-hapus-user">
                                @csrf
                                @method('DELETE')
                                @can('bagian-delete')
                                <button type="button"
                                        onclick="confirmHapusUser(this)"
                                        class="btn btn-delete btn-sm" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @endcan
                            </form>
                    </td>
                </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Data bagian belum tersedia.</td>
                    </tr>
                    @endforelse
        </tbody>
    </table>

    </div>
    </div>

</div>
@endsection