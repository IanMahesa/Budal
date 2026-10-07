@extends('partials.all')

@section('title', 'Master Perijinan')

@section('content')

<style>  
    /* BADGE JENIS */
    .jenis-badge {
        background: #fff;
        color: #1976d2;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        margin-left: 10px;
    }

    /* SEARCH */
    .search-box {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 15px;
    }

    .search-box input {
        width: 300px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    /* TABLE */
    .table-head-color > thead > tr > th {
        background-color: #343a40 !important;
        color: #fff !important;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border-color: #495057 !important;
    }

    .table td {
        vertical-align: middle;
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .permission-card {
        height: 100%;
        border: 1px solid #e3e7ed;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(33, 37, 41, 0.08);
    }

    .permission-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        background: linear-gradient(90deg, #4215a8, #405be8);
        color: #fff;
    }

    .permission-card-header h5 {
        margin: 0;
    }

    .permission-card-body {
        padding: 18px;
    }

    .permission-card .jenis-badge {
        background: #fff;
    }

    .custom-header {
        background: linear-gradient(90deg, #4215a8, #405be8);
        color: #fff;
    }

    @media (max-width: 991.98px) {
        .permission-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0">
        <div class="card-header custom-header">
            <h4 class="mb-0">Master Perijinan</h4>
        </div>

        <div class="card-body">
            @php
                $dataPribadi = $perijinan->where('jenis', 'PRIBADI');
                $dataDinas = $perijinan->where('jenis', 'DINAS');
            @endphp

            <div class="permission-grid">
                <div class="permission-card">
                    <div class="permission-card-header">
                        <h5>PRIBADI <span class="jenis-badge">PRIBADI</span></h5>
                        @can('ijin-create')
                        <a href="{{ route('ijin.create', ['jenis' => 'PRIBADI']) }}"
                            class="btn-tambah1"
                            title="Tambah Perijinan Pribadi">
                            <i class="fas fa-plus"></i>
                        </a>
                        @endcan
                    </div>
                    <div class="permission-card-body">
                        <div class="search-box">
                            <input type="text" class="form-control search-input"
                                placeholder="Cari berdasarkan kode atau nama..."
                                data-table="tabelPribadi">
                        </div>
                        <div class="table-responsive">
                            <table id="tabelPribadi" class="table table-striped table-hover mb-0 table-head-color">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Jenis Ijin</th>
                                        <th width="150">Kode</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataPribadi as $i)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $i->izin }}</td>
                                            <td class="text-center"><span class="badge bg-primary">{{ $i->kode }}</span></td>
                                            <td class="text-center">
                                                @can('ijin-edit')
                                                <a href="{{ route('ijin.edit', $i->id_ijin) }}" class="btn btn-edit btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                <form action="{{ route('ijin.destroy', $i->id_ijin) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    @can('ijin-delete')
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

                <div class="permission-card">
                    <div class="permission-card-header">
                        <h5>DINAS <span class="jenis-badge">DINAS</span></h5>
                        @can('ijin-create')
                        <a href="{{ route('ijin.create', ['jenis' => 'DINAS']) }}"
                            class="btn-tambah1"
                            title="Tambah Perijinan Dinas">
                            <i class="fas fa-plus"></i>
                        </a>
                        @endcan
                    </div>
                    <div class="permission-card-body">
                        <div class="search-box">
                            <input type="text" class="form-control search-input"
                                placeholder="Cari berdasarkan kode atau nama..."
                                data-table="tabelDinas">
                        </div>
                        <div class="table-responsive">
                            <table id="tabelDinas" class="table table-striped table-hover mb-0 table-head-color">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Ijin</th>
                                        <th width="150">Kode</th>
                                        <th width="200">Sub Bagian</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataDinas as $i)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $i->izin }}</td>
                                            <td class="text-center"><span class="badge bg-primary">{{ $i->kode }}</span></td>
                                            <td>
                                                @if($i->subbag)
                                                    <span class="badge bg-success">{{ $i->subbag->sub_bag }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @can('ijin-edit')
                                                <a href="{{ route('ijin.edit', $i->id_ijin) }}" class="btn btn-edit btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                <form action="{{ route('ijin.destroy', $i->id_ijin) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    @can('ijin-delete')
                                                    <button type="button" onclick="confirmHapusUser(this)" class="btn btn-delete btn-sm">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    @endcan
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">Belum ada perijinan dinas</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tableId = this.getAttribute('data-table');
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;
            for (let i = 0; i < cells.length - 1; i++) {
                if (
                    cells[i].textContent
                        .toLowerCase()
                        .includes(searchTerm)
                ) {
                    match = true;
                    break;
                }
            }
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>

@endsection