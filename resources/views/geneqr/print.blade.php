<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code</title>

    <style>
    @page{
        size:A4 landscape;
        margin:10mm;
    }

    body{
        font-family:Arial, Helvetica, sans-serif;
        background:#f3f3f3;
    }

    .container-print{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-start;
        gap:10px;
    }

    .card{
        width:5.5cm;
        height:9cm;
        border:2px solid #000;
        border-radius:8px;
        background:#fff;
        padding:12px;
        box-sizing:border-box;
        page-break-inside:avoid;
        position:relative;
    }

    .header{
        text-align:center;
        border-bottom:2px solid #000;
        padding-bottom:8px;
        margin-bottom:8px;
    }

    .header h4{
        margin:0;
        font-size:12px;
    }

    .header p{
        margin:2px 0;
        font-size:8px;
    }

    .judul{
        text-align:center;
        font-size:12px;
        font-weight:bold;
        margin:10px 0;
    }

    .qr{
        text-align:center;
        margin:10px 0;
    }

    .qr svg{
        width:150px;
        height:150px;
    }

    table{
        width:100%;
        border-collapse:collapse;
        font-size:8px;
    }

    table td{
        padding:2px;
        vertical-align:top;
    }

    .judulkolom{
        width:60px;
        font-weight:bold;
        padding-left:15px;
    }

    .titik{
        width:10px;
    }

    .footer{
        position:absolute;
        left:10px;
        right:10px;
        bottom:10px;
        border-top:2px solid #999;
        padding-top:6px;
        text-align:center;
        font-size:8px;
        color:#555;
    }

    .no-print{
        text-align:center;
        margin-top:20px;
    }

    @media print{

        body{
            background:#fff;
        }

        .no-print{
            display:none;
        }

    }
</style>

</head>
<body>

<div class="container-print">

@foreach($qrcode as $row)

<div class="card">

    <div class="header">
        <h4>PERUMDA AIR MINUM</h4>
        <p>KOTA MAGELANG</p>
    </div>

    <div class="judul">        
            {{ strtoupper($row->nama_kartu) }}     
    </div>

    <div class="qr">
        {!! QrCode::size(220)->generate($row->nomor_kartu) !!}
    </div>

    <table>
        <tr>
            <td class="judulkolom">Nomor Kartu</td>
            <td class="titik">:</td>
            <td>{{ $row->nomor_kartu }}</td>
        </tr>
        <tr>
            <td class="judulkolom">Jenis Kartu</td>
            <td>:</td>
            <td>
                @if($row->jenis_qr == 'SUBBAG')
                    SUB BAGIAN
                @else
                    {{ $row->jenis_qr }}
                @endif
            </td>
        </tr>
        @if($row->jenis_qr=="PEGAWAI")
        <tr>
            <td class="judulkolom">Nama</td>
            <td>:</td>
            <td>{{ optional($row->pegawai)->nama }}</td>
        </tr>
        <tr>
            <td class="judulkolom">NIK</td>
            <td>:</td>
            <td>{{ optional($row->pegawai)->nik }}</td>
        </tr>
        @else
        <tr>
            <td class="judulkolom">Bagian</td>
            <td>:</td>
            <td>{{ optional(optional($row->subbag)->bagian)->bag }}</td>
        </tr>
        <tr>
            <td class="judulkolom">Sub Bagian</td>
            <td>:</td>
            <td>{{ optional($row->subbag)->sub_bag }}</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        Dicetak :
        {{ now()->format('d-m-Y H:i:s') }}
    </div>

</div>

@endforeach

</div>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-success">
        🖨 Cetak
    </button>
    <button onclick="history.back()" class="btn btn-secondary">
        Kembali
    </button>
</div>

<script>

window.onload = function(){
    window.print();
};

</script>

</body>
</html>