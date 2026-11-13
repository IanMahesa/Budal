@extends('partials.all')

@section('title','Scan QR')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header custom-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode"></i>
                        SCAN QR CODE
                    </h4>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="scan_mode" class="form-label">Mode Scan</label>
                        <select id="scan_mode" class="form-select">
                            <option value="ijin">Izin Keluar / Kembali</option>
                            <option value="lembur">Absensi Lembur</option>
                        </select>
                    </div>
                    <div id="reader"></div>
                    <div class="text-center mt-3">
                        <span class="badge bg-success px-4 py-2" id="statusScan">
                            Menunggu QR Code...
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center align-items-center mt-3">
    <a href="{{ route('keluar.index') }}" class="btn btn-laporan">
        <i class="fas fa-file-alt"></i> Riwayat Ijin
    </a>
    <a href="{{ route('lembur.index') }}" class="btn btn-laporan">
        <i class="fas fa-file-alt"></i> Riwayat Lembur
    </a>
</div>

@endsection

@push('styles')
<style>
    #reader {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    #reader > * {
        max-width: 100%;
    }

    #reader video,
    #reader canvas,
    #reader img {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
    }

    #reader__scan_region {
        width: 100% !important;
        min-height: 0 !important;
    }

    @media (max-width: 576px) {
        .container.py-4 {
            padding-right: .75rem !important;
            padding-left: .75rem !important;
        }

        #reader {
            min-width: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let scanner;

function resumeScanner() {
    if (!scanner) {
        return;
    }

    try {
        scanner.resume();
    } catch (error) {
        console.error('Scanner gagal dilanjutkan:', error);
    }
}

function startScanner() {
    if (typeof Html5Qrcode === 'undefined') {
        $('#statusScan').text('Library scanner belum siap');
        return;
    }

    scanner = new Html5Qrcode('reader');

    scanner.start(
        { facingMode: 'environment' },
        {
            fps: 10,
            qrbox: 250
        },
        function (decodedText) {
            scanner.pause();
            $('#statusScan').text('QR Terdeteksi...');
            processScan(decodedText);
        },
        function () {
            // ignore scan errors
        }
    ).catch(function (err) {
        console.error(err);
        $('#statusScan').text('Kamera tidak dapat diakses');
    });
}

function processScan(kodeQr, idLembur) {
    const mode = $('#scan_mode').val();

    $.ajax({
        url: '{{ route("scan.proses") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            kode_qr: kodeQr,
            mode: mode,
            id_lembur: idLembur || ''
        },
        success: function (res) {
            const status = res.data && res.data.status;
            const isKembali = status === 'Kembali';
            const statusLabel = mode === 'lembur'
                ? (status === 'Selesai' ? 'Lembur selesai' : 'Mulai lembur tercatat')
                : (isKembali ? 'Transaksi kembali berhasil dicatat' : 'Transaksi keluar berhasil dicatat');
            $('#statusScan').text(statusLabel);
            Swal.fire({
                icon: mode === 'ijin' && isKembali ? 'info' : 'success',
                title: status === 'Selesai' || isKembali ? 'Selesai' : 'Berhasil',
                text: res.message
            }).then(function () {
                resumeScanner();
            });
        },
        error: function (xhr) {
            const response = xhr.responseJSON || {};
            if (response.requires_selection && Array.isArray(response.choices)) {
                const choices = {};
                response.choices.forEach(function (item) {
                    choices[item.id] = item.label;
                });

                Swal.fire({
                    icon: 'question',
                    title: 'Pilih kegiatan lembur',
                    text: response.message,
                    input: 'select',
                    inputOptions: choices,
                    inputPlaceholder: 'Pilih kegiatan',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    inputValidator: function (value) {
                        return value ? undefined : 'Silakan pilih kegiatan lembur.';
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        processScan(kodeQr, result.value);
                    } else {
                        resumeScanner();
                    }
                });
                return;
            }

            const message = response.message || 'Terjadi kesalahan saat memproses QR Code.';
            $('#statusScan').text('Gagal memproses QR');
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            }).then(function () {
                resumeScanner();
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    $('#scan_mode').on('change', function () {
        $('#statusScan').text($(this).val() === 'lembur'
            ? 'Scan QR pegawai; kegiatan lembur dicocokkan otomatis.'
            : 'Menunggu QR Code...');
    });

    startScanner();
});
</script>
@endpush