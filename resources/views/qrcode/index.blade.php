@extends('partials.all')

@section('title', 'QR Code')

@section('content')

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f3f3;
    }

    .qr-card-wrapper {
        width: 100%;
        min-height: 100vh;

        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;

        padding: 20px 10px;
        box-sizing: border-box;
    }

    .card {
        width: 7cm;
        height: 10.4cm;

        min-width: 7cm;
        min-height: 10.4cm;

        border: 2px solid #000;
        border-radius: 8px;
        background: #fff;

        padding: 12px;
        box-sizing: border-box;

        page-break-inside: avoid;
        position: relative;

        flex-shrink: 0;
    }

    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 4px;
        margin-bottom: 4px;
    }

    .header h4 {
        margin: 0;
        font-size: 12px;
        font-weight: bold;
    }

    .header p {
        margin: 2px 0;
        font-size: 8px;
    }

    .judul {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 5px 0;
    }

    .qr {
        text-align: center;
        margin: 5px 0;
        padding-bottom: 4px;
    }

    .qr svg {
        width: 200px;
        height: 200px;
        max-width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
    }

    table td {
        padding: 2px;
        vertical-align: top;
    }

    .judulkolom {
        width: 80px;
        font-weight: bold;
        padding-left: 17px;
        font-size: 10px;
    }

    .isikolom {
        width: 80px;        
        font-size: 10px;
    }

    .titik {
        width: 10px;
    }

    .footer {
        position: absolute;
        left: 10px;
        right: 10px;
        bottom: 10px;

        border-top: 2px solid #999;
        padding-top: 6px;

        text-align: center;
        font-size: 8px;
        color: #555;
    }

    .qr-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;

        margin-top: 15px;
    }

    .qr-buttons a {
        min-width: 90px;
        padding: 8px 15px;

        border-radius: 6px;
        text-decoration: none;

        font-size: 13px;
        font-weight: bold;

        text-align: center;

        transition: all 0.2s ease;
    }    

    @media (max-width: 400px) {
        .qr-card-wrapper {
            padding-left: 5px;
            padding-right: 5px;
        }

        .qr-buttons {
            gap: 8px;
        }

        .qr-buttons a {
            min-width: 85px;
            padding: 8px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="qr-card-wrapper">

@if($qrcode)
<div class="card">

    <div class="header">
        <h4>PERUMDA AIR MINUM</h4>
        <p>KOTA MAGELANG</p>
    </div>

    <div class="judul">
        {{ $jenisQr === 'PEGAWAI' ? 'PRIBADI' : 'DINAS' }}
    </div>

    <div class="qr">
        {!! QrCode::size(200)->generate($qrcode->nomor_kartu) !!}
    </div>

    <table>
        <tr>
            <td class="judulkolom">Nama</td>
            <td class="titik">:</td>
            <td class="isikolom">{{ $user->name }}</td>
        </tr>
        
        <tr>
            <td class="judulkolom">Sub Bagian</td>
            <td class="titik">:</td>
            <td class="isikolom">{{ $qrcode->subbag->sub_bag ?? $pegawai->subbag->sub_bag ?? '-' }}</td>
        </tr>

        <tr>
            <td class="judulkolom">{{ $jenisQr === 'PEGAWAI' ? 'NIK' : 'Nomor Kartu' }}</td>
            <td class="titik">:</td>
            <td class="isikolom">{{ $jenisQr === 'PEGAWAI' ? $pegawai->nik : $qrcode->nomor_kartu }}</td>
        </tr>

    </table>

    <div class="footer">
        Dicetak : {{ now()->format('d-m-Y H:i:s') }}
    </div>

</div>
@else
    <div class="alert alert-info text-center" role="status">
        @if(!$pegawai)
            Data pegawai untuk akun ini tidak ditemukan.
        @elseif($jenisQr === 'PEGAWAI')
            QR Code pribadi untuk akun ini belum tersedia.
        @else
            QR Code dinas aktif untuk akun ini belum tersedia.
        @endif
    </div>
@endif

<div class="qr-buttons">

    <a href="{{ route('qrcode.index', ['jenis_qr' => 'PEGAWAI']) }}"
       class="btn-pribadi {{ $jenisQr === 'PEGAWAI' ? 'active' : '' }}"
       aria-current="{{ $jenisQr === 'PEGAWAI' ? 'page' : 'false' }}">
        PRIBADI
    </a>
    
    <a href="{{ route('qrcode.index', ['jenis_qr' => 'SUBBAG']) }}"
       class="btn-dinas {{ $jenisQr === 'SUBBAG' ? 'active' : '' }}"
       aria-current="{{ $jenisQr === 'SUBBAG' ? 'page' : 'false' }}">
        DINAS
    </a>

</div>

</div>

@endsection
