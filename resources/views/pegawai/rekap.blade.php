@extends('partials.all')

@section('title','Rekap Durasi Pegawai')

@section('content')

<div class="container-fluid">

    {{-- Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header custom-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-chart-bar"></i>
                Rekap Durasi Pegawai
            </h5>
            <a href="{{ route('pegawai.index') }}" class="btn btn-back me-2">
                <i class="fas fa-long-arrow-alt-left"></i> Kembali
            </a>            
        </div>

        <div class="card-body">
            <form id="formFilter" method="GET" action="{{ url()->current() }}">
                @csrf
                <input type="hidden" name="id" value="{{ request()->route('id') }}">

                <div class="row mb-3">

                    <div class="col-md-2">
                        <select name="bulan" class="form-select">
                            <option value="">-- Bulan --</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m',$i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="tahun" class="form-select">
                            @for($t=date('Y');$t>=2024;$t--)
                                <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                    </div>

                     <div class="col-md-2">
                        <select name="jenis" class="form-select">
                            <option value="">-- Semua Ijin --</option>
                            <option value="PRIBADI" {{ request('jenis') == 'PRIBADI' ? 'selected' : '' }}>Ijin Pribadi</option>
                            <option value="DINAS" {{ request('jenis') == 'DINAS' ? 'selected' : '' }}>Ijin Dinas</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-filter me-2">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                        
                            <button type="button" id="btnReset" class="btn btn-reset me-2">
                                <i class="fa fa-sync"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow mb-4">

        <div class="card-header custom-header d-flex justify-content-between align-items-center">        
            <h5 class="mb-0">
                <i class="fa fa-table"></i>
                Data Rekap Durasi Pegawai
            </h5>
            @can('pegawai-manage')
            <a href="{{ route('pegawai.rekap.export', [], false) }}" id="btnExcel" class="btn btn-download me-2">
                <i class="fa fa-file-excel"></i>
                Export
            </a>
            @endcan
        </div>

        <div class="card-body table-responsive">

            <form id="formRekap">
                @csrf

                <table class="table table-striped table-hover" id="tabelRekap" width="100%">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="10%" class="text-center">Tanggal</th>
                            <th width="15%">Nama</th>
                            <th width="18%">Sub Bagian</th>
                            <th width="10%" class="text-center">Jenis Ijin</th>
                            <th width="12%">Ijin</th>
                            <th width="10%" class="text-center">Jam Keluar</th>
                            <th width="10%" class="text-center">Jam Masuk</th>
                            <th width="10%" class="text-center">Durasi</th>
                            <th width="10%" class="text-center">Foto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($transaksi as $row)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="text-center">
                                    @if($row->tanggal_keluar)
                                        {{ \Carbon\Carbon::parse($row->tanggal_keluar)->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ optional($row->pegawai)->nama }}
                                </td>

                                <td>
                                    {{ optional(optional($row->pegawai)->subbag)->sub_bag }}
                                </td>

                                <td class="text-center">
                                    {{ optional($row->perijinan)->jenis }}
                                </td>

                                <td>
                                    {{ optional($row->perijinan)->izin }}
                                </td>

                                <td class="text-center">
                                    {{ $row->jam_keluar ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $row->jam_masuk ?? '-' }}
                                </td>

                                <td class="text-center">
                                    @if($row->durasi_menit)
                                        {{ floor($row->durasi_menit / 60) }} jam
                                        {{ $row->durasi_menit % 60 }} menit
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($row->foto_pegawai)
                                        <img src="{{ asset('storage/' . $row->foto_pegawai) }}"
                                            alt="Foto {{ optional($row->pegawai)->nama }}"
                                            class="foto-pegawai"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalFoto"
                                            data-foto="{{ asset('storage/' . $row->foto_pegawai) }}"
                                            data-nama="{{ optional($row->pegawai)->nama }}"
                                            style="
                                                width: 45px;
                                                height: 45px;
                                                object-fit: cover;
                                                border-radius: 50%;
                                                border: 2px solid #ddd;
                                                cursor: pointer;
                                            ">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    Tidak ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                        @if(request('jenis') == 'PRIBADI' || request('jenis') == 'DINAS')
                            @php
                                $totalMenit = $transaksi->sum('durasi_menit');
                                $totalJam = floor($totalMenit / 60);
                                $totalMenitSisa = $totalMenit % 60;
                            @endphp

                            <tr class="fw-bold">
                                <td colspan="8" class="text-end">
                                    Total Durasi {{ request('jenis') == 'PRIBADI' ? 'Ijin Pribadi' : 'Ijin Dinas' }}
                                </td>
                                <td class="text-center">
                                    {{ $totalJam }} jam {{ $totalMenitSisa }} menit
                                </td>
                            </tr>
                        @endif
                        
                </table>

                    <div class="modal fade" id="modalFoto" tabindex="-1" aria-labelledby="modalFotoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">

                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="modalFotoLabel">
                                        Foto Pegawai
                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close">
                                    </button>
                                </div>

                                <div class="modal-body text-center">
                                    <img id="fotoModal"
                                        src=""
                                        alt="Foto Pegawai"
                                        style="
                                            max-width: 100%;
                                            max-height: 70vh;
                                            object-fit: contain;
                                            border-radius: 8px;
                                        ">
                                </div>

                            </div>
                        </div>
                    </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
$(function () {
    $('#formFilter').on('submit', function (e) {
        e.preventDefault();

        const params = $(this).serialize();
        const url = '{{ url()->current() }}' + (params ? '?' + params : '');

        window.location.href = url;
    });

    $('#btnReset').on('click', function () {
        $('#formFilter')[0].reset();
        window.location.href = '{{ url()->current() }}';
    });

    $('#btnExcel').on('click', function (e) {
        e.preventDefault();

        const params = $('#formFilter').serialize();
        const currentId = '{{ request()->route('id') }}';
        let url = '{{ route('pegawai.rekap.export', ['id' => '__ID__'], false) }}';
        url = url.replace('__ID__', currentId);
        url += (params ? (url.includes('?') ? '&' : '?') + params : '');

        window.location.href = url;
    });
});

document.addEventListener('DOMContentLoaded', function () {

    const fotoModal = document.getElementById('fotoModal');
    const modalFotoLabel = document.getElementById('modalFotoLabel');

    document.querySelectorAll('.foto-pegawai').forEach(function (foto) {

        foto.addEventListener('click', function () {

            const src = this.getAttribute('data-foto');
            const nama = this.getAttribute('data-nama');

            fotoModal.src = src;
            modalFotoLabel.textContent = 'Foto ' + nama;
        });

    });

});
</script>

@endpush
