@extends('partials.all')

@section('title','Data QR Code')

@section('content')
<div class="container">

    <div class="card tabel-card">
        <div class="card-body table-responsive">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
                <h4 class="mb-0">Data QR Code Dinas</h4>

                <div class="d-flex gap-2">
                    
                    <a href="{{ route('geneqrdin.create') }}" class="btn btn-tambah">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                    

                    @can('geneqrdin-manage')
                    <button type="button" id="btnCetak" class="btn btn-cetak">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    @endcan
                </div>
            </div>

            <div class="table-divider mb-3"></div>

            <table id="tabelUser" class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="20"> <input type="checkbox" id="checkAll"> </th>
                        <th class="text-center" width="20">No</th>
                        <th class="text-center">Tanggal Dinas</th>
                        <th class="text-center">Tgl Generate</th>
                        <th class="text-center">Nama Pegawai</th>
                        <th class="text-center">Sub Bagian</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qrcode as $key => $row)
                    <tr>
                        <td class="text-center"> 
                            <input type="checkbox" class="checkItem"
                            name="id_qrcode[]" value="{{ $row->id_qrcode }}">
                        </td>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ $row->tanggal_dinas
                                ? $row->tanggal_dinas->format('d-m-Y')
                                : '-'
                            }}
                        </td>
                        <td class="text-center">
                            {{ $row->tanggal_generate
                                ? \Carbon\Carbon::parse($row->tanggal_generate)->format('d-m-Y')
                                : '-'
                            }}
                        </td>
                        <td>
                            {{ $row->pegawai->nama ?? '-' }}
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                {{ $row->pegawai->subbag->sub_bag ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $row->ket ?? '-' }}</strong>
                        </td>
                       
                        <td class="text-center">
                            <a href="{{ route('geneqrdin.show',$row->id_qrcode) }}"
                                class="btn btn-show btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                           
                            <a href="{{ route('geneqrdin.edit',$row->id_qrcode) }}"
                               class="btn btn-edit btn-sm">
                               <i class="fas fa-edit"></i>
                            </a>                           
                            
                            <form action="{{ route('geneqrdin.destroy',$row->id_qrcode) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')                                
                                <button type="button" class="btn btn-delete btn-sm" onclick="confirmHapusUser(this)">
                                    <i class="fas fa-trash"></i>
                                </button>                                
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            Belum ada data QR Code.
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const checkAll = document.getElementById('checkAll');
const printButton = document.getElementById('btnCetak');

if (checkAll) {
    checkAll.addEventListener('change', function () {

        let checkbox = document.querySelectorAll('.checkItem');

        checkbox.forEach(function(item){
            item.checked = this.checked;
        }, this);

    });
}

function submitSelection(actionUrl) {

    let checked = document.querySelectorAll('.checkItem:checked');

    if (checked.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silakan pilih minimal satu QR Code yang akan dicetak.'
        });
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    checked.forEach(function(item){
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_qrcode[]';
        input.value = item.value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

if (printButton) {
    printButton.addEventListener('click', function(){
        submitSelection("{{ route('qrcode.print') }}");
    });
}
</script>

@endsection
