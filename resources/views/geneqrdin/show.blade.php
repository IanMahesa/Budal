@extends('partials.all')

@section('title', 'Detail QR Code')

@section('content')

<div class="container-fluid">
    <div class="text-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-qrcode text-primary"></i>
            Detail QR Code
        </h3>        
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <strong>QR Code</strong>
                </div>
                <div class="card-body text-center">
                    {!! QrCode::size(292)->generate($qrcode->nomor_kartu) !!}
                    <hr>
                    <strong>{{ $qrcode->nomor_kartu }}</strong>
                </div>
            </div>
            <div class="mt-3 ms-3">
                <a href="{{ route('geneqrdin.index') }}"
                class="btn btn-back">
                    <i class="fas fa-long-arrow-alt-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <strong>Informasi QR Code</strong>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Jenis Kartu</th>
                            <td>
                                @if($qrcode->jenis_qr=='PEGAWAI')
                                    <span class="badge bg-info">
                                        PEGAWAI
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        SUB BAGIAN
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Nama Kartu</th>
                            <td>{{ $qrcode->nama_kartu }}</td>
                        </tr>

                        <tr>
                            <th>Nomor Kartu</th>
                            <td><code>{{ $qrcode->nomor_kartu }}</code></td>
                        </tr>

                        @if($qrcode->jenis_qr=='PEGAWAI')
                        <tr>
                            <th>Pegawai</th>
                            <td>
                                {{ optional($qrcode->pegawai)->nama ?? '-' }}
                            </td>
                        </tr>
                        @else
                        <tr>
                            <th>Sub Bagian</th>
                            <td>
                                {{ optional($qrcode->subbag)->sub_bag ?? '-' }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <th>Status</th>
                            <td>
                                @if($qrcode->status=='Aktif')
                                    <span class="badge bg-success">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Ijin</th>
                            <td>
                                @if($qrcode->jenis_qr == 'PEGAWAI')
                                    <span class="badge rounded-pill bg-success px-3">
                                        <i class="fas fa-user"></i> PRIBADI
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-primary px-3">
                                        <i class="fas fa-briefcase"></i> DINAS
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Tanggal Generate</th>
                            <td>
                                {{ optional($qrcode->tanggal_generate)->format('d-m-Y H:i:s') }}
                            </td>
                        </tr>

                        <tr>
                            <th>Terakhir Diubah</th>
                            <td>
                                {{ optional($qrcode->updated_at)->format('d-m-Y H:i:s') }}
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection