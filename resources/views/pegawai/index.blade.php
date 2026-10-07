@extends('partials.all')

@section('title','Data Pegawai')

@section('content')
<div class="card tabel-card">
        <div class="card-body table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
            <h4>Data Pegawai</h4>
            <div class="d-flex align-items-center gap-2">
                @can('pegawai-manage')
                <button type="button" class="btn btn-download me-2" data-bs-toggle="modal" data-bs-target="#modalImportPegawai">
                    <i class="fas fa-file-import"></i> Import
                </button>
                @endcan
                @can('pegawai-create')
                <a href="{{ route('pegawai.create') }}" class="btn btn-tambah">
                    <i class="fas fa-plus"></i>Tambah
                </a>
                @endcan
</div>
</div>

<div class="card shadow mb-3">

    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            Daftar Pegawai
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

        @if ($errors->has('file'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->get('file') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

<div class="table-responsive">
    <table id="tabelUser" class="table table-striped table-hover mb-0">
        <thead>
            <tr>
                <th width="60">No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Sub Bagian</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai as $key => $user)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $user->nik }}</td>
                    <td>{{ $user->nama }}</td>
                    <td class="text-center">{{ $user->jabatan ?? '-' }}</td>
                    <td>
                        {{ optional($user->subbag)->sub_bag ?? '-' }}
                    </td>

                    <td class="text-center">
                        <a href="{{ route('pegawai.rekap', $user->id_peg) }}"
                            class="btn btn-recap btn-sm"
                            title="Rekap">
                            <i class="fas fa-file-alt"></i>
                        </a>
                        @can('pegawai-edit')
                        <a href="{{ route('pegawai.edit',$user->id_peg) }}"
                             class="btn btn-edit btn-sm">
                             <i class="fas fa-user-edit"></i>
                        </a>
                        @endcan
                            <form action="{{ route('pegawai.destroy',$user->id_peg) }}"
                                method="POST"
                                class="d-inline form-hapus-user">
                                @csrf
                                @method('DELETE')
                                @can('pegawai-delete')
                                <button type="button"
                                        onclick="confirmHapusUser(this)"
                                        class="btn btn-delete btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @endcan
                            </form>
                    </td>
                </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Data tidak ada</td>
                    </tr>
                    @endforelse
        </tbody>
    </table>
    
    </div>
    </div>

</div>

<div class="modal fade" id="modalImportPegawai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="fileImportPegawai" class="form-label">File Excel atau CSV</label>
                    <input type="file" name="file" id="fileImportPegawai" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <small class="text-muted">Header: nik, nama, jenis_kelamin, jabatan, sub_bagian, status</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-back" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-download">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection