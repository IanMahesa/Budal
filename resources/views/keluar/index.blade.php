@extends('partials.all')

@section('title','Data Ijin Keluar')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">

        {{-- HEADER --}}
        <div class="card-header custom-header">
            <h5 class="mb-0">
                <i class="fa fa-chart-bar me-2"></i>
                Data Ijin Keluar Pegawai
            </h5>
        </div>

        {{-- FILTER --}}
        <div class="card-body">

            <form method="GET"
                  action="{{ route('keluar.index') }}">

                <div class="row g-3 align-items-end">

                    {{-- TANGGAL --}}
                    <div class="col-md-4">
                        <label for="tanggalLaporan" class="form-label">
                            Tanggal Laporan
                        </label>

                        <input type="date"
                               class="form-control"
                               id="tanggalLaporan"
                               name="tanggal"
                               value="{{ $tanggal }}">
                    </div>

                    {{-- JENIS --}}
                    <div class="col-md-4">
                        <label for="jenisLaporan" class="form-label">
                            Jenis Ijin
                        </label>

                        <select class="form-select"
                                id="jenisLaporan"
                                name="jenis">

                            <option value=""
                                {{ $jenis === '' ? 'selected' : '' }}>
                                Semua Jenis
                            </option>

                            <option value="PRIBADI"
                                {{ $jenis === 'PRIBADI' ? 'selected' : '' }}>
                                Pribadi
                            </option>

                            <option value="DINAS"
                                {{ $jenis === 'DINAS' ? 'selected' : '' }}>
                                Kedinasan
                            </option>

                        </select>
                    </div>

                    {{-- TOMBOL FILTER --}}
                    <div class="col-md-4">
                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-scanqr">
                                <i class="fas fa-filter me-1"></i>
                                Tampilkan
                            </button>

                            <a href="{{ route('keluar.index') }}"
                               class="btn btn-back">
                                <i class="fas fa-sync-alt me-1"></i>
                                Hari Ini
                            </a>

                        </div>
                    </div>

                </div>

            </form>

            {{-- DIVIDER --}}
            <div class="table-divider my-3"></div>

            {{-- ACTION BUTTON --}}
            <div class="d-flex justify-content-center gap-2">

                <a href="{{ route('scan.index') }}"
                   class="btn btn-scanqr">
                    <i class="fas fa-qrcode me-1"></i>
                    Scan QR
                </a>

            </div>

        </div>

    </div>
</div>

{{-- CARD 2 : TABEL DATA --}}
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header custom-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>
                Data Ijin Keluar Pegawai
            </h5>
            @can('keluar-manage')
             <button type="button"
                        class="btn btn-download me-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExport">
                    <i class="fas fa-file-excel me-1"></i>
                    Download
                </button>
            @endcan
        </div>

        {{-- TABLE --}}
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover" id="tabelRekap" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Tanggal</th>
                        <th>Pegawai</th>
                        <th>Sub Bagian</th>
                        <th>Ijin</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">Jam Keluar</th>
                        <th class="text-center">Jam Masuk</th>
                        <th class="text-center">Status</th>
                        <th>Pengawas</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transaksi as $row)
                        <tr>
                            {{-- NO --}}
                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>
                            {{-- TANGGAL --}}
                            <td>
                                {{ optional($row->tanggal_keluar)->format('d-m-Y') ?? '-' }}
                            </td>
                            {{-- PEGAWAI --}}
                            <td>
                                {{ optional($row->pegawai)->nama ?? '-' }}
                            </td>
                            {{-- SUB BAGIAN --}}
                            <td>
                                {{ optional($row->subbag)->sub_bag ?? '-' }}
                            </td>
                            {{-- IJIN --}}
                            <td>
                                {{ optional($row->perijinan)->izin ?? '-' }}
                            </td>
                            {{-- JENIS --}}
                            <td class="text-center">
                                @if(optional($row->perijinan)->jenis == 'PRIBADI')
                                    <span class="badge bg-warning text-dark">
                                        Pribadi
                                    </span>
                                @elseif(optional($row->perijinan)->jenis == 'DINAS')
                                    <span class="badge bg-primary">
                                        Kedinasan
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- JAM KELUAR --}}
                            <td class="text-center text-nowrap">
                                {{ $row->jam_keluar
                                    ? \Carbon\Carbon::parse($row->jam_keluar)->format('H:i:s')
                                    : '-' }}
                            </td>
                            {{-- JAM MASUK --}}
                            <td class="text-center text-nowrap">
                                {{ $row->jam_masuk
                                    ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i:s')
                                    : '-' }}
                            </td>
                            {{-- STATUS --}}
                            <td class="text-center">
                                @if($row->status == 'Keluar')
                                    <span class="badge bg-danger">
                                        Keluar
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                            {{-- PENGAWAS --}}
                            <td>
                                {{ optional($row->user)->name ?? '-' }}
                            </td>
                            {{-- AKSI --}}
                            <td class="text-center action-cell">
                                <a href="{{ route('keluar.show', $row->id_transaksi) }}"
                                   class="btn btn-show btn-sm"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="11"
                                class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-1"></i>
                                Belum ada data ijin keluar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- Modal Export -->
<div class="modal fade" id="modalExport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    Export Data Ijin Keluar
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">
                        Pilih Jenis Ijin
                    </label>

                    <select class="form-select" id="jenisExport">
                        <option value="">Semua Data</option>
                        <option value="PRIBADI">Pribadi</option>
                        <option value="DINAS">Kedinasan</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tanggalMulai" class="form-label">
                            Tanggal Mulai
                        </label>
                        <input type="date" class="form-control" id="tanggalMulai">
                    </div>

                    <div class="col-md-6">
                        <label for="tanggalAkhir" class="form-label">
                            Tanggal Akhir
                        </label>
                        <input type="date" class="form-control" id="tanggalAkhir">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-back me-2"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <button class="btn btn-download me-2"
                        id="btnDownloadExport">
                    <i class="fas fa-download"></i>
                    Download
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

$('#tanggalLaporan, #jenisLaporan').on('change', function(){
    $(this).closest('form').trigger('submit');
});

$('#btnDownloadExport').click(function(){

    let jenis = $('#jenisExport').val();

    let url = "{{ route('keluar.export') }}";
    let params = new URLSearchParams();

    if(jenis != ''){
        params.set('jenis', jenis);
    }

    let tanggalMulai = $('#tanggalMulai').val();
    let tanggalAkhir = $('#tanggalAkhir').val();

    if(tanggalMulai != ''){
        params.set('tanggal_mulai', tanggalMulai);
    }

    if(tanggalAkhir != ''){
        params.set('tanggal_akhir', tanggalAkhir);
    }

    if (tanggalMulai && tanggalAkhir && tanggalAkhir < tanggalMulai) {
        Swal.fire({
            icon: 'warning',
            title: 'Rentang tanggal tidak valid',
            text: 'Tanggal akhir harus sama atau setelah tanggal mulai.'
        });
        return;
    }

    if (params.toString() !== '') {
        url += '?' + params.toString();
    }

    window.location.href = url;

});

</script>
@endpush