@extends('partials.all')

@section('title','Laporan Lembur')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header custom-header">
            <h5 class="mb-0">
                <i class="fa fa-chart-bar me-2"></i>
                Manajemen Lembur
            </h5>
    </div>
    
    <div class="card-body">
        <form method="GET"
            action="{{ route('lembur.index') }}">
            <div class="row g-3 align-items-end">
                   
                <div class="col-md-4">
                    <label for="tanggalLaporan" class="form-label">
                        Tanggal Laporan
                    </label>

                    <input type="date" class="form-control" id="tanggalLaporan"
                        name="tanggal" value="{{ $tanggal }}">
                    </div>

                    <div class="col-md-4">
                        <label for="jenisLaporan" class="form-label">
                            Jenis Lembur
                        </label>

                        <select class="form-select" id="jenisLaporan" name="jenis">
                            <option value=""
                                    {{ $jenis === '' ? 'selected' : '' }}>
                                Semua Jenis
                            </option>

                            <option value="TERENCANA"
                                    {{ $jenis === 'TERENCANA' ? 'selected' : '' }}>
                                Terencana
                            </option>

                            <option value="DADAKAN"
                                    {{ $jenis === 'DADAKAN' ? 'selected' : '' }}>
                                Dadakan
                            </option>

                        </select>
                    </div>

                    {{-- TOMBOL FILTER --}}
                    <div class="col-md-4">
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-scanqr">
                                <i class="fas fa-filter me-1"></i>
                                Tampilkan
                            </button>

                            <a href="{{ route('lembur.index') }}" class="btn btn-back">
                                <i class="fas fa-sync-alt me-1"></i>
                                Hari Ini
                            </a>

                        </div>
                    </div>

                </div>

            </form>

            <div class="table-divider my-3"></div>

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

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header custom-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>
                Daftar Kegiatan Lembur
            </h5>
            <button type="button" class="btn btn-download me-3" data-bs-toggle="modal" data-bs-target="#modalExport">
                <i class="fas fa-file-excel me-1"></i>
                Download
            </button>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover" id="tabelRekap" width="100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Jadwal Lembur</th>
                        <th>Jenis Lembur</th>
                        <th>Pegawai</th>
                        <th>Subbagian</th>
                        <th>Waktu Scan Aktual</th>
                        <th>Status</th>
                        <th>Durasi Aktual</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($lembur as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item->tanggal_lembur)->Format('d-m-Y') }}
                                @if ($item->jam_mulai || $item->jam_selesai)
                                    <br>
                                    <small class="text-muted">
                                        Mulai: {{ $item->jam_mulai ? substr((string) $item->jam_mulai, 0, 5) : '-' }}
                                        | Selesai: {{ $item->jam_selesai ? substr((string) $item->jam_selesai, 0, 5) : '-' }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->jenis_lembur == 'TERENCANA')
                                    <span class="badge bg-primary">
                                        Terencana
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        Dadakan
                                    </span>
                                @endif
                            </td>
                            <td>
                                @forelse ($pegawaiLembur->get($item->id_lembur, collect()) as $dataPegawai)
                                    <div>{{ $dataPegawai->nama }}</div>
                                @empty
                                    <span class="text-muted">Belum ada pegawai</span>
                                @endforelse
                            </td>
                            <td>{{ $item->sub_bag ?? '-' }}</td>
                            <td class="text-center">
                                @forelse ($pegawaiLembur->get($item->id_lembur, collect()) as $dataPegawai)
                                    <div>
                                        <small class="text-muted">
                                            Mulai:
                                            {{ $dataPegawai->jam_mulai ? \Carbon\Carbon::parse($dataPegawai->jam_mulai)->Format('d-m-Y | H:i') : '-' }}
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            Selesai:
                                            {{ $dataPegawai->jam_selesai ? \Carbon\Carbon::parse($dataPegawai->jam_selesai)->Format('d-m-Y | H:i') : '-' }}
                                        </small>
                                    </div>
                                @empty
                                    <span class="text-muted">Belum ada data scan</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                @if ($item->status == 'Terjadwal')
                                    <span class="badge bg-secondary">
                                        Terjadwal
                                    </span>
                                @elseif ($item->status == 'Berlangsung')
                                    <span class="badge bg-primary">
                                        Berlangsung
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @forelse ($pegawaiLembur->get($item->id_lembur, collect()) as $dataPegawai)
                                    <small class="text-muted">
                                        Mulai (jadwal - scan):
                                        @if ($dataPegawai->selisih_mulai_menit !== null)
                                            {{ $dataPegawai->selisih_mulai_menit > 0 ? '+' : '' }}{{ $dataPegawai->selisih_mulai_menit }} menit
                                        @else
                                            -
                                        @endif
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        Selesai (jadwal - scan):
                                        @if ($dataPegawai->selisih_selesai_menit !== null)
                                            {{ $dataPegawai->selisih_selesai_menit > 0 ? '+' : '' }}{{ $dataPegawai->selisih_selesai_menit }} menit
                                        @else
                                            -
                                        @endif
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        Durasi aktual - jadwal:
                                        @if ($dataPegawai->selisih_durasi_menit !== null)
                                            {{ $dataPegawai->selisih_durasi_menit > 0 ? '+' : '' }}{{ $dataPegawai->selisih_durasi_menit }} menit
                                        @else
                                            -
                                        @endif
                                    </small>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Belum ada data ijin keluar.
                                        </td>
                                    </tr>
                                @endforelse
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </table>
        </div>
    </div>
</div>

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
                        <option value="TERENCANA">Terencana</option>
                        <option value="DADAKAN">Dadakan</option>
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

    let url = "{{ route('lembur.export') }}";
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