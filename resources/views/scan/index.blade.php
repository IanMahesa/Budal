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
</div>

{{-- Modal Pegawai --}}
<div class="modal fade" id="modalPegawai" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    Informasi Izin Pegawai
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

    <div class="card border-0 shadow-sm mb-4 info-pegawai-card">
        <div class="card-body">

            <table class="table table-borderless mb-0">
                <tr>
                    <td width="35%"><strong>Nama</strong></td>
                    <td width="5%">:</td>
                    <td id="namaPegawai">-</td>
                </tr>

                <tr>
                    <td><strong>NIK</strong></td>
                    <td>:</td>
                    <td id="infoNik">-</td>
                </tr>

                <tr>
                    <td><strong>Sub Bagian</strong></td>
                    <td>:</td>
                    <td id="infoSubBag">-</td>
                </tr>

                <tr>
                    <td><strong>Jenis Izin</strong></td>
                    <td>:</td>
                    <td>
                        <span id="infoJenisIjin" class="badge bg-success px-3 py-2">
                            -
                        </span>
                    </td>
                </tr>
            </table>

        </div>
    </div>

    <div class="pilih-izin-card">
        <h5 class="text-center mb-3" >
            <i class="fas fa-list-check me-2"></i>
            Pilih Jenis Izin
        </h5>
        <div class="table-divider mb-3"></div> 
        <div class="row g-3" id="btnIjin"></div>
    </div>

    <div class="mt-4">
        <label for="kameraPegawai" class="form-label fw-semibold text-center w-100">
            <i class="fas fa-camera me-2"></i>Foto Pegawai
        </label>
        <video id="kameraPegawai" class="w-100 rounded d-none" autoplay muted playsinline></video>
        <canvas id="canvasFotoPegawai" class="d-none"></canvas>
        <div id="statusKamera" class="small text-muted">Menyiapkan kamera...</div>
    </div>
</div>
        </div>
    </div>
</div>
</div>
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

    .info-pegawai-card {
        background: #e8f5e9 !important;
        border: 1px solid #c8e6c9 !important;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
    }

    .info-pegawai-card .card-body {
        background: transparent !important;
        padding: 7px 14px;
    }

    .info-pegawai-card .table {
        background: transparent !important;
        margin-bottom: 0;
    }

    .info-pegawai-card .table td {
        background: transparent !important;
        border: none;
        padding: 3px 5px;
        line-height: 1.1;
        color: #334155;
    }

    .info-pegawai-card .table td strong {
        color: #1e293b;
    }

    /* Bagian pilih jenis izin */
    .pilih-izin-card {
        background: #e8f1ff;
        border: 1px solid #c7dcff;
        border-radius: 12px;
        padding: 20px;
    }

    .pilih-izin-card h5 {
        color: #1e40af;
        font-weight: 600;
    }

    #kameraPegawai {
        max-height: 280px;
        object-fit: cover;
        background: #111827;
    }

    .btn-izin {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        width: 100%;
        height: 48px;

        background-color: #0A5EB0;
        color: #fff;

        border: none;
        border-radius: 8px;

        font-size: 18px;
        font-weight: 600;

        cursor: pointer;

        box-shadow: 0 4px 0 #071B4D;

        transition:
            background-color .2s ease,
            transform .2s ease,
            box-shadow .2s ease;
    }

    .btn-izin:hover {
        background-color: #084F96;
        color: #fff;

        transform: translateY(-2px);

        box-shadow: 0 7px 0 #071B4D;
    }

    .btn-izin:active {
        background-color: #063F7A;
        color: #fff;

        transform: translateY(3px);

        box-shadow: 0 2px 0 #071B4D;
    }

    .btn-izin:focus {
        outline: none;
        color: #fff;

        box-shadow:
            0 4px 0 #071B4D,
            0 0 0 3px rgba(10, 94, 176, 0.25);
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
let currentQrCode = null;
let modalClosedBySubmit = false;
let photoStream = null;

function stopPhotoCamera() {
    if (photoStream) {
        photoStream.getTracks().forEach(function (track) {
            track.stop();
        });
        photoStream = null;
    }

    const video = $('#kameraPegawai').get(0);
    video.srcObject = null;
    $('#kameraPegawai').addClass('d-none');
}

async function startPhotoCamera() {
    const video = $('#kameraPegawai').get(0);

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        $('#statusKamera').text('Kamera tidak didukung oleh browser ini.');
        return;
    }

    try {
        photoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' },
            audio: false
        });
        video.srcObject = photoStream;
        $('#kameraPegawai').removeClass('d-none');
        $('#statusKamera').text('Kamera siap. Tekan jenis izin untuk mengambil foto.');
    } catch (error) {
        console.error('Kamera foto gagal dibuka:', error);
        $('#statusKamera').text('Kamera tidak dapat diakses. Izin kamera diperlukan.');
    }
}

function capturePhoto() {
    const video = $('#kameraPegawai').get(0);
    const canvas = $('#canvasFotoPegawai').get(0);

    if (!photoStream || !video.videoWidth || !video.videoHeight) {
        return null;
    }

    const maxWidth = 800;
    const scale = Math.min(1, maxWidth / video.videoWidth);
    canvas.width = Math.round(video.videoWidth * scale);
    canvas.height = Math.round(video.videoHeight * scale);
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL('image/jpeg', 0.8);
}

async function stopScanner() {
    if (!scanner) {
        return;
    }

    try {
        await scanner.stop();
        await scanner.clear();
    } catch (error) {
        console.error('Scanner gagal dihentikan:', error);
    }
    scanner = null;
}

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

async function showChoiceModal(data = {}) {
    modalClosedBySubmit = false;
    const nama = data.nama_pegawai || 'Pilih jenis izin yang akan dipakai';
    const jenisQr = String(data.jenis_qr || '').toUpperCase();
    const jenisIjin = String(
        data.jenis_izin || (jenisQr === 'SUBBAG' ? 'DINAS' : 'PRIBADI')
    ).toUpperCase();
    const options = data.perijinan_options || [];

    $('#namaPegawai').text(nama);
    $('#infoNik').text(data.nik || '-');
    $('#infoSubBag').text(data.nama_subbag || '-');
    $('#infoJenisIjin').text(jenisIjin);

    let buttonsHtml = '';

    if (options.length > 0) {
        buttonsHtml = options.map(function (item) {
            return `
                <div class="col-lg-3 col-md-3 col-sm-4 col-6">
                    <button type="button" class="btn btn-izin mb-2" data-izin-id="${item.id_ijin}" data-jenis="${item.jenis}">
                        <i class="fas fa-hand-point-right me-2"></i> ${item.izin}
                    </button>
                </div>
            `;
        }).join('');
    } else if (jenisQr === 'PEGAWAI' || jenisIjin === 'PRIBADI') {
        buttonsHtml = `
            <div class="col-lg-3 col-md-3 col-sm-4 col-6">
                <button type="button" class="btn btn-izin mb-2" data-jenis="PRIBADI">
                    <i class="fas fa-hand-point-right me-2"></i> Ijin Pribadi
                </button>
            </div>
        `;
    } else if (jenisQr === 'SUBBAG' || jenisIjin === 'DINAS') {
        buttonsHtml = `
            <div class="col-lg-3 col-md-3 col-sm-4 col-6">
                <button type="button" class="btn btn-izin mb-2" data-jenis="DINAS">
                    <i class="fas fa-hand-point-right me-2"></i> Perjalanan Dinas
                </button>
            </div>
        `;
    }

    $('#btnIjin').html(buttonsHtml);

    const modal = new bootstrap.Modal(document.getElementById('modalPegawai'));
    modal.show();
    await stopScanner();
    await startPhotoCamera();
}

function submitChoice(jenis, idIjin = null) {
    if (!currentQrCode) {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'QR belum tersedia.' });
        return;
    }

    const fotoPegawai = capturePhoto();

    if (!fotoPegawai) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto belum tersedia',
            text: 'Pastikan kamera aktif sebelum memilih jenis izin.'
        });
        return;
    }

    $.ajax({
        url: '{{ route("scan.simpan") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            kode_qr: currentQrCode,
            jenis_izin: jenis,
            id_ijin: idIjin,
            foto_pegawai: fotoPegawai
        },
        success: function (res) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPegawai'));
            if (modal) {
                modalClosedBySubmit = true;
                modal.hide();
            }

            $('#statusScan').text('Pilihan izin tersimpan');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: res.message || 'Pilihan izin berhasil diproses.'
            }).then(() => {
                startScanner();
            });
        },
        error: function (xhr) {
            var message = 'Terjadi kesalahan saat menyimpan pilihan.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            }).then(() => {
                resumeScanner();
            });
        }
    });
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
            currentQrCode = decodedText;
            $('#statusScan').text('QR Terdeteksi...');

            $.ajax({
                url: '{{ route("scan.proses") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kode_qr: decodedText
                },
                success: function (res) {
                    if (res.success) {
                        if (res.data && res.data.status === 'Kembali') {
                            $('#statusScan').text('Transaksi kembali berhasil dicatat');
                            Swal.fire({
                                icon: 'info',
                                title: 'Selesai',
                                text: res.message
                            }).then(() => {
                                resumeScanner();
                            });
                            return;
                        }

                        $('#statusScan').text('Pilih jenis izin');
                        showChoiceModal(res.data || {});
                    } else {
                        $('#statusScan').text('QR tidak dikenali');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message
                        }).then(() => {
                            resumeScanner();
                        });
                    }
                },
                error: function (xhr) {
                    var message = 'Terjadi kesalahan saat memproses QR Code.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    $('#statusScan').text('Gagal memproses QR');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: message
                    }).then(() => {
                        resumeScanner();
                    });
                }
            });
        },
        function () {
            // ignore scan errors
        }
    ).catch(function (err) {
        console.error(err);
        $('#statusScan').text('Kamera tidak dapat diakses');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    $('#modalPegawai').on('hidden.bs.modal', function () {
        stopPhotoCamera();
        if (!modalClosedBySubmit) {
            $('#statusScan').text('Menunggu QR Code...');
            startScanner();
        }

        modalClosedBySubmit = false;
    });

    $(document).on('click', '#btnIjin button[data-izin-id]', function () {
        const idIjin = $(this).data('izin-id');
        const jenis = $(this).data('jenis');

        if (idIjin) {
            submitChoice(jenis, idIjin);
        }
    });

    $(document).on('click', '#btnIjin button[data-jenis]:not([data-izin-id])', function () {
        submitChoice($(this).data('jenis'));
    });

    startScanner();
});
</script>
@endpush