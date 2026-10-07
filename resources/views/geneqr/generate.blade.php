@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@if(isset($qrcode) && $qrcode->isNotEmpty())
    @foreach($qrcode as $row)
        <div style="margin-bottom: 20px;">
            {!! QrCode::size(250)->generate($row->kode_qr) !!}
            <p>{{ $row->kode_qr }}</p>
        </div>
    @endforeach
@else
    <p>Tidak ada data QR yang dipilih.</p>
@endif