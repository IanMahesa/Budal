@extends('partials.all')

@section('title','QR Code Lembur')

@section('content')

    <div class="card tabel-card">
        <div class="card-body table-responsive">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
                <h4 class="mb-0">Data Lembur</h4>
                <div class="d-flex gap-2">
                    
                    <a href="{{ route('genlembur.create') }}" class="btn btn-tambah">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                    

                    @can('geneqr-list')
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
                        <th class="text-center" width="200">Waktu Lembur</th>
                        <th class="text-center" width="100">Jenis Lembur</th>
                        <th class="text-center" width="250">Nama Pegawai</th>
                        <th class="text-center" width="120">Sub Bagian</th>
                        <th class="text-center" width="350">Keterangan</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lembur as $row)
                    <tr>
                        <td class="text-center"> 
                            <input type="checkbox" class="checkItem"
                            name="id_qrcode[]" value="{{ optional($row->qrcode)->id_qrcode }}"
                            {{ $row->qrcode ? '' : 'disabled' }}>
                        </td>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ optional($row->lembur)->tanggal_lembur
                                ? \Carbon\Carbon::parse($row->lembur->tanggal_lembur)->format('d-m-Y')
                                : '-' }}
                            @if($row->lembur && ($row->lembur->jam_mulai || $row->lembur->jam_selesai))
                                <br>
                                <small class="text-muted">
                                    Mulai: {{ $row->lembur->jam_mulai ? substr((string) $row->lembur->jam_mulai, 0, 5) : '-' }}
                                    | Selesai: {{ $row->lembur->jam_selesai ? substr((string) $row->lembur->jam_selesai, 0, 5) : '-' }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $row->lembur->jenis_lembur ?? '-' }}
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
                            <strong>{{ $row->lembur->keterangan ?? '-' }}</strong>
                        </td>
                        <td class="text-center">
                            @if ($row->qrcode)
                                <a href="{{ route('genlembur.show', $row->qrcode->id_qrcode) }}"
                                    class="btn btn-show btn-sm" title="Lihat QR Pegawai">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @else
                                <span class="text-muted">QR tidak tersedia</span>
                            @endif
                            <a href="{{ route('genlembur.edit', $row->id_detail_lembur) }}"
                                class="btn btn-edit btn-sm" title="Edit data lembur">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('genlembur.destroy', $row->id_detail_lembur) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete btn-sm"
                                    title="Hapus peserta lembur"
                                    onclick="return confirm('Hapus peserta ini dari data lembur? QR pegawai tidak akan dihapus.')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            Belum ada data lembur.
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
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
