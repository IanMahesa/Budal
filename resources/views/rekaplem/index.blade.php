@extends('partials.all')

@section('title','Rekap Durasi Pegawai')

@section('content')

<div class="container-fluid">

    @if($akunBelumTerhubung)
        <div class="alert alert-warning" role="alert">
            Akun {{ auth()->user()->name }} belum terhubung dengan data pegawai.
            Hubungi administrator untuk memeriksa username akun dan NIK pegawai.
        </div>
    @endif

    {{-- Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header custom-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-chart-bar"></i>
                Filter Rekap
            </h5>
            <a href="{{ route('qrcode.index') }}" class="btn btn-back me-2">
                <i class="fas fa-long-arrow-alt-left"></i> Kembali
            </a>            
        </div>

        <div class="card-body">
            <form id="formFilter" method="GET" action="{{ url()->current() }}">

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
                            <option value="">-- Jenis Lembur --</option>
                            <option value="TERENCANA" {{ request('jenis') == 'TERENCANA' ? 'selected' : '' }}>Terencana</option>
                            <option value="DADAKAN" {{ request('jenis') == 'DADAKAN' ? 'selected' : '' }}>Dadakan</option>
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
                Rekap Durasi {{ $pegawai->nama ?? auth()->user()->name }}
            </h5>
        </div>

        <div class="card-body table-responsive">

            <form id="formRekap">
                @csrf

                <table class="table table-striped table-hover" id="tabelRekap" width="100%">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="10%" class="text-center">Tanggal</th>
                            <th width="10%" class="text-center">Jenis Lembur</th>
                            <th width="12%">Keterangan</th>
                            <th width="10%" class="text-center">Jam Mulai</th>
                            <th width="10%" class="text-center">Jam Selesai</th>
                            <th width="10%" class="text-center">Durasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($transaksi as $row)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="text-center">
                                    @if(optional($row->lembur)->tanggal_lembur)
                                        {{ \Carbon\Carbon::parse($row->lembur->tanggal_lembur)->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if (optional($row->lembur)->jenis_lembur == 'TERENCANA')
                                        <span class="badge bg-primary text-dark">Terencana</span>
                                    @elseif (optional($row->lembur)->jenis_lembur == 'DADAKAN')
                                        <span class="badge bg-info text-dark">Dadakan</span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ optional($row->lembur)->keterangan ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $row->jam_mulai ? \Carbon\Carbon::parse($row->jam_mulai)->format('H:i') : '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $row->jam_selesai ? \Carbon\Carbon::parse($row->jam_selesai)->format('H:i') : '-' }}
                                </td>

                                <td class="text-center">
                                    @if($row->total_menit !== null)
                                        {{ floor($row->total_menit / 60) }} jam
                                        {{ $row->total_menit % 60 }} menit
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Tidak ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(request('jenis') == 'TERENCANA' || request('jenis') == 'DADAKAN')
                        <tfoot>
                            @php
                                $totalMenit = $transaksi->sum('total_menit');
                                $totalJam = floor($totalMenit / 60);
                                $totalMenitSisa = $totalMenit % 60;
                            @endphp

                            <tr class="fw-bold">
                                <td colspan="6" class="text-end">
                                    Total Durasi {{ request('jenis') == 'TERENCANA' ? 'Terencana' : 'Dadakan' }}
                                </td>
                                <td class="text-center">
                                    {{ $totalJam }} jam {{ $totalMenitSisa }} menit
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

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
});
</script>
@endpush