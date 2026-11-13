@extends('partials.all')

@section('title','Edit Data QR Code')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Edit Data QR Code</h4>

            <div class="table-divider mb-4"></div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as$error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('geneqr.update', $qrcode->id_qrcode) }}" autocomplete="off" id="formSaveUser">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis QR</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="Pribadi" readonly>
                        <input type="hidden" id="jenis_qr" name="jenis_qr" value="PEGAWAI">
                            
                        @error('jenis_qr')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="status_div">
                        <label class="form-label">Status</label>
                        <input type="hidden" id="status_input" name="status" value="{{ old('status', $qrcode->status ?? 'Aktif') }}">
                        <input type="text" id="status_display" class="form-control" value="{{ old('status', $qrcode->status ?? 'Aktif') }}" readonly>
                    </div>
                </div>

                <!-- Custom Autocomplete Nama Pegawai -->
                <div class="mb-3 position-relative" id="pegawai_div">
                    <label class="form-label">Nama Pegawai</label>
                    
                    <!-- Hidden input untuk menyimpan ID Pegawai yang dikirim ke server, pre-filled dari data edit -->
                    <input type="hidden" id="id_peg" name="id_peg" value="{{ old('id_peg', $qrcode->id_peg ?? '') }}" required>
                    
                    <!-- Input teks untuk pencarian -->
                    <input type="text" id="search_pegawai" 
                           class="form-control @error('id_peg') is-invalid @enderror" 
                           placeholder="Cari nama atau NIK pegawai" 
                           autocomplete="off">

                    <!-- Kotak dropdown hasil pencarian -->
                    <div id="pegawai_dropdown" class="shadow rounded-3 p-1 mt-1" style="display: none; max-height: 220px; overflow-y: auto; background-color: #1e1e1e; color: #fff; position: absolute; width: 100%; z-index: 1050;">
                    </div>

                    @error('id_peg')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Kartu</label>
                    <input type="text" name="nama_kartu"
                        class="form-control @error('nama_kartu') is-invalid @enderror"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()"
                        value="{{ old('nama_kartu', $qrcode->nama_kartu ?? '') }}" autocomplete="off" maxlength="100" readonly required>

                    @error('nama_kartu')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Kartu (NIK)</label>
                    <input type="text" name="nomor_kartu"
                        class="form-control @error('nomor_kartu') is-invalid @enderror"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()"
                        value="{{ old('nomor_kartu', $qrcode->nomor_kartu ?? '') }}" autocomplete="off" maxlength="30" readonly required>

                    @error('nomor_kartu')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>                

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('geneqr.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                            <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $pegawaiData = $pegawai->map(function ($item) {
        return [
            'id' => (string) $item->id_peg,
            'nama' => $item->nama,
            'nik' => $item->nik,
            'status' => $item->status ?? '',
        ];
    })->values();
@endphp

@push('scripts')
<script>
// Data pegawai dari backend Laravel
const pegawaiList = @json($pegawaiData);

$(document).ready(function () {
    const $searchInput =$('#search_pegawai');
    const $dropdown =$('#pegawai_dropdown');
    const $hiddenId =$('#id_peg');

    // Jika ada nilai awal (baik dari database saat edit atau old input saat error validasi)
    if ($hiddenId.val()) {
        let selected = pegawaiList.find(p => p.id == $hiddenId.val());
        if (selected) {
            $searchInput.val(selected.nama + ' - ' + selected.nik);
            fillData(selected);
        }
    }

    // Event saat mengetik di input pencarian
    $searchInput.on('input', function () {
        let keyword = $(this).val().toLowerCase().trim();$hiddenId.val(''); // Reset ID jika user mengetik ulang pencarian

        // Jika input kosong, kosongkan juga kartu & status
        if (!keyword) {
            $dropdown.hide();
            clearCardData();
            return;
        }

        // Filter data berdasarkan nama atau NIK
        let filtered = pegawaiList.filter(p => 
            String(p.nama || '').toLowerCase().includes(keyword) ||
            String(p.nik || '').toLowerCase().includes(keyword)
        );

        if (filtered.length > 0) {
            $dropdown.empty();
            filtered.forEach(p => {
                $('<div>', {
                    class: 'pegawai-item px-3 py-2 rounded',
                    text: `${p.nama} - ${p.nik}`,
                    css: { cursor: 'pointer', transition: 'background 0.2s' }
                }).data('pegawai', p).appendTo($dropdown);
            });
            $dropdown.show();
        } else {
            $dropdown.html('<div class="px-3 py-2 text-muted">Pegawai tidak ditemukan</div>').show();
        }
    });

    // Event ketika salah satu pilihan pada dropdown diklik
    $(document).on('click', '.pegawai-item', function () {
        let pegawai = $(this).data('pegawai');
        if (!pegawai) return;

        let id = pegawai.id;
        let nama = pegawai.nama;
        let nik = pegawai.nik;
        let status = pegawai.status;

        // Set nilai ke input & hidden input
        $hiddenId.val(id);
        $searchInput.val(nama + ' - ' + nik);$dropdown.hide();

        // Isi otomatis Nama Kartu, Nomor Kartu, dan Status
        fillData({ nama, nik, status });
    });

    // Efek hover pada item dropdown
    $(document).on('mouseenter', '.pegawai-item', function () {
        $(this).css('background-color', 'rgba(255, 255, 255, 0.1)'); }).on('mouseleave', '.pegawai-item', function () {$(this).css('background-color', 'transparent');
    });

    // Sembunyikan dropdown jika klik di luar area
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#pegawai_div').length) {
            $dropdown.hide();
        }
    });

    function fillData(data) {
        $('input[name="nama_kartu"]').val(String(data.nama || '').toUpperCase());
        $('input[name="nomor_kartu"]').val(String(data.nik || '').toUpperCase());
        
        if (data.status) {
            $('#status_input').val(data.status);
            $('#status_display').val(data.status);
        }
    }

    function clearCardData() {
        $('input[name="nama_kartu"]').val('');
        $('input[name="nomor_kartu"]').val('');
    }
});
</script>
@endpush