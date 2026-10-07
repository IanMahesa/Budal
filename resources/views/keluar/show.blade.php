@extends('partials.all')

@section('title','Detail Ijin Keluar')

@section('content')

<div class="container-fluid">
    <div class="text-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-qrcode text-primary"></i>
            Detail Ijin Keluar
        </h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <strong>Foto Pegawai</strong>
                </div>
                <div class="card-body text-center">
                    @if($ijinKeluar->foto_pegawai)
                        <img src="{{ asset('storage/' . $ijinKeluar->foto_pegawai) }}"
                        alt="Foto Pegawai"
                        class="img-thumbnail"
                        style="width: 490px; height: 490px; object-fit: cover;">
                    @else
                        <span class="text-muted">Tidak ada foto</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 ms-3">
                <a href="{{ route('keluar.index') }}"
                class="btn btn-back">
                    <i class="fas fa-long-arrow-alt-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <strong>Informasi Ijin Keluar</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nama Pegawai</th>
                            <td>{{ optional($ijinKeluar->pegawai)->nama }}</td>
                        </tr>

                        <tr>
                            <th>NIK</th>
                            <td>{{ optional($ijinKeluar->pegawai)->nik }}</td>
                        </tr>

                        <tr>
                            <th>Bagian</th>
                            <td>{{ optional($ijinKeluar->bagian)->bag }}</td>
                        </tr>

                        <tr>
                            <th>Sub Bagian</th>
                            <td>{{ optional($ijinKeluar->subbag)->sub_bag }}</td>
                        </tr>

                        <tr>
                            <th>Jenis Ijin</th>
                            <td>{{ optional($ijinKeluar->perijinan)->izin }}</td>
                        </tr>

                        <tr>
                            <th>Tanggal Keluar</th>
                            <td>{{ optional($ijinKeluar->tanggal_keluar)->format('d-m-Y') }}</td>
                        </tr>

                        <tr>
                            <th>Jam Keluar</th>
                            <td>
                                {{ $ijinKeluar->jam_keluar
                                    ? \Carbon\Carbon::parse($ijinKeluar->jam_keluar)->format('H:i:s')
                                    : '-'
                                }}
                            </td>
                        </tr>

                        <tr>
                            <th>Tanggal Masuk</th>
                            <td>
                                {{ $ijinKeluar->tanggal_masuk ? \Carbon\Carbon::parse($ijinKeluar->tanggal_masuk)->format('d-m-Y') : '-' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Jam Masuk</th>
                            <td>
                                {{ $ijinKeluar->jam_masuk
                                    ? \Carbon\Carbon::parse($ijinKeluar->jam_masuk)->format('H:i:s')
                                    : '-'
                                }}
                            </td>
                        </tr>

                        <tr>
                            <th>Durasi</th>
                            <td>
                                @if($ijinKeluar->durasi_menit)
                                    @php
                                        $jam = floor($ijinKeluar->durasi_menit / 60);
                                        $menit = $ijinKeluar->durasi_menit % 60;
                                    @endphp
                                    {{ $jam }} Jam {{ $menit }} Menit
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                @if($ijinKeluar->status=='Keluar')
                                    <span class="badge bg-danger">
                                        Keluar
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Keterangan</th>
                            <td>
                                {{ $ijinKeluar->keterangan ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Diawasi Oleh</th>
                            <td>{{ optional($ijinKeluar->user)->name }}</td>
                        </tr>

                    </table> 
                </div> 
            </div> 
        </div>                      
    </div>
</div>

@endsection