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
                <h4 class="mb-0">Data QR Code Pribadi</h4>

                <div class="d-flex gap-2">
                    @can('geneqr-create')
                    <a href="{{ route('geneqr.create') }}" class="btn btn-tambah">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                    @endcan

                    @can('geneqr-manage')
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
                        <th class="text-center" width="50"> <input type="checkbox" id="checkAll"> </th>
                        <th class="text-center" width="50">No</th>
                        <th>Sub Bagian</th>
                        <th>Nama Pegawai</th>
                        <th>Nama Kartu</th>
                        <th>Nomor Kartu</th>
                        <th width="160">Aksi</th>
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
                        <td>                            
                            <span class="badge bg-success">
                                    {{ $row->pegawai->subbag->sub_bag ?? '-' }}
                            </span>                       
                        </td>
                         <td>
                            @php
                                $namaPegawai = '-';

                                if ($row->jenis_qr === 'PEGAWAI' && $row->pegawai) {
                                    $namaPegawai = $row->pegawai->nama;
                                } elseif ($row->jenis_qr === 'SUBBAG' && $row->subbag) {
                                    // Prefer the specific pegawai assigned to this QR (id_peg).
                                    if ($row->id_peg && $row->pegawai) {
                                        $namaPegawai = $row->pegawai->nama;
                                    } else {
                                        $namaPegawai = '-';
                                    }
                                }
                            @endphp

                            {{ $namaPegawai }}
                        </td>
                        <td>{{ $row->nama_kartu }}</td>
                        <td>
                            <strong>{{ $row->nomor_kartu }}</strong>
                        </td>
                       

                        <td class="text-center">
                            <a href="{{ route('geneqr.show',$row->id_qrcode) }}"
                                class="btn btn-show btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>

                            @can('geneqr-edit')
                            <a href="{{ route('geneqr.edit',$row->id_qrcode) }}"
                               class="btn btn-edit btn-sm">
                               <i class="fas fa-edit"></i>
                            </a>
                            @endcan

                            
                            <form action="{{ route('geneqr.destroy',$row->id_qrcode) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                @can('geneqr-delete')
                                <button type="button" class="btn btn-delete btn-sm" onclick="confirmHapusUser(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">
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
