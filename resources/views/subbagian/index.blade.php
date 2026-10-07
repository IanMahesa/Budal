@extends('partials.all')

@section('title','SubBagian')

@section('content')

<style>
    .search-box {
        margin-bottom: 15px;
        display: flex;
        justify-content: flex-end;
    }
    
    .search-box input {
        width: 300px !important;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .table-head-color > thead > tr > th{
        background-color: #343a40 !important;
        color: #fff !important;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border-color: #495057 !important;
    }

</style>

<div class="container">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

   <div class="card">
        <div class="card-header custom-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Master Sub Bagian</h4>
        </div>

        <div class="card-body">
            <div class="accordion" id="accordionBagian">
                @foreach($bagian as $b)
                <div class="accordion-item">
                    <h2 class="accordion-header d-flex align-items-center justify-content-between" id="heading{{ $b->id_bag }}">
                        <button class="accordion-button collapsed flex-grow-1"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $b->id_bag }}"
                                aria-expanded="false">
                            <strong>{{ $b->bag }}</strong>
                            <span class="badge bg-primary ms-3">
                                {{ $b->kode_bag }}
                            </span>
                        </button>
                        @can('subag-create')
                        <a href="{{ route('subag.create', ['id_bag' => $b->id_bag]) }}"
                           class="btn-tambah1">
                            <i class="fas fa-plus"></i>
                        </a>
                        @endcan
                    </h2>

                    <div id="collapse{{ $b->id_bag }}"
                         class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="search-box">
                                <input type="text" 
                                       class="form-control search-input" 
                                       placeholder="Cari berdasarkan kode atau nama..."
                                       data-table="tabel{{ $b->id_bag }}">
                            </div>
                            <table id="tabel{{ $b->id_bag }}" class="table table-striped table-hover mb-0 table-head-color">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Sub Bagian</th>
                                        <th width="180">Kode</th>
                                        <th width="150">Jumlah Pegawai</th>
                                        <th width="180">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($b->subag as $s)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $s->sub_bag }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">
                                                {{ $s->kode_subag }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                {{ $s->pegawai->count() }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            @can('subag-edit')
                                            <a href="{{ route('subag.edit', $s->id_subag) }}"
                                                class="btn btn-edit btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            <form action="{{ route('subag.destroy', $s->id_subag) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                @can('subag-delete')
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
                                        <td colspan="5" class="text-center">
                                            Belum ada Sub Bagian
                                        </td>
                                    </tr>

                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
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
                if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
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