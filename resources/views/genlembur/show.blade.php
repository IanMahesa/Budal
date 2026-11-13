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
                    <strong>{{ $qrcode->nama_kartu }}</strong>
                </div>
            </div>
            <div class="mt-3 ms-3">
                <a href="{{ route('genlembur.index') }}"
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

                        <tr>
                            <th>Tanggal Lembur</th>
                            <td>{{ optional($detailLembur->lembur->tanggal_lembur)->format('d-m-Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Lembur</th>
                            <td>{{ $detailLembur->lembur->jenis_lembur ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jam Mulai</th>
                            <td>{{ $detailLembur->lembur->jam_mulai ? substr((string) $detailLembur->lembur->jam_mulai, 0, 5) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jam Selesai</th>
                            <td>{{ $detailLembur->lembur->jam_selesai ? substr((string) $detailLembur->lembur->jam_selesai, 0, 5) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $detailLembur->lembur->keterangan ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Pegawai</th>
                            <td>
                                {{ optional($qrcode->pegawai)->nama ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Sub Bagian</th>
                            <td>
                                {{ optional($qrcode->subbag ?? optional($qrcode->pegawai)->subbag)->sub_bag ?? '-' }}
                            </td>
                        </tr>

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
                            <th>Jenis</th>
                            <td>
                                <span class="badge rounded-pill bg-primary px-3">
                                    <i class="fas fa-clock"></i> LEMBUR
                                </span>
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