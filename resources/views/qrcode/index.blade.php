@extends('partials.all')

@section('title', 'QR Code Pengguna')

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
        width: 5.5cm;
        height: 9cm;

        min-width: 5.5cm;
        min-height: 9cm;

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
    }

    .qr svg {
        width: 150px;
        height: 150px;
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
        width: 60px;
        font-weight: bold;
        padding-left: 15px;
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

    .btn-dinas {
        background: #0A5EB0;
        color: #fff;
    }

    .btn-dinas:hover {
        background: #084b8d;
        color: #fff;
    }

    .btn-pribadi {
        background: #198754;
        color: #fff;
    }

    .btn-pribadi:hover {
        background: #146c43;
        color: #fff;
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

<div class="card">

    <div class="header">
        <h4>PERUMDA AIR MINUM</h4>
        <p>KOTA MAGELANG</p>
    </div>

    <div class="judul">
        {{ strtoupper($user->name) }}
    </div>

    <div class="qr">
        {!! QrCode::size(220)->generate((string) $user->id) !!}
    </div>

    <table>
        <tr>
            <td class="judulkolom">ID User</td>
            <td class="titik">:</td>
            <td>{{ $user->id }}</td>
        </tr>

        <tr>
            <td class="judulkolom">Nama</td>
            <td class="titik">:</td>
            <td>{{ $user->name }}</td>
        </tr>

        <tr>
            <td class="judulkolom">Username</td>
            <td class="titik">:</td>
            <td>{{ $user->username }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak : {{ now()->format('d-m-Y H:i:s') }}
    </div>

</div>

<div class="qr-buttons">

    <a href="{{ route('ijin.create', ['jenis' => 'DINAS']) }}"
       class="btn-dinas">
        DINAS
    </a>

    <a href="{{ route('ijin.create', ['jenis' => 'PRIBADI']) }}"
       class="btn-pribadi">
        PRIBADI
    </a>

</div>

</div>

@endsection
