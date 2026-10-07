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
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Sesuaikan route dan passing parameter ID data yang akan diupdate -->
            <form method="POST" action="{{ route('geneqr.update', $qrcode->id_qrcode) }}" autocomplete="off" id="formEditUser">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis QR</label>
                        <!-- Asumsi Jenis QR tetap Pribadi, atau ambil dari database -->
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="{{ $qrcode->jenis_qr == 'PEGAWAI' ? 'Pribadi' : $qrcode->jenis_qr }}" readonly>
                        <input type="hidden" id="jenis_qr" name="jenis_qr" value="{{ old('jenis_qr', $qrcode->jenis_qr) }}">
                            
                        @error('jenis_qr')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="status_div">
                        <label class="form-label">Status</label>
                        <input type="hidden" id="status_input" name="status" value="{{ old('status', $qrcode->status) }}">
                        <input type="text" id="status_display" class="form-control" value="{{ old('status', $qrcode->status) }}" readonly>
                    </div>
                </div>

                <div class="mb-3" id="pegawai_div">
                    <label class="form-label">Nama Pegawai</label>
                    <select id="id_peg" name="id_peg" class="form-select @error('id_peg') is-invalid @enderror" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $item)
                            <option value="{{ $item->id_peg }}"
                                data-nik="{{ $item->nik }}"
                                data-status="{{ $item->status }}"
                                data-nama="{{ $item->nama }}"
                                {{ old('id_peg', $qrcode->id_peg) == $item->id_peg ? 'selected' : '' }}>
                                {{ $item->nama }} - {{ $item->nik }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_peg')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Kartu</label>
                    <input type="text" name="nama_kartu"
                        class="form-control @error('nama_kartu') is-invalid @enderror"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()"
                        value="{{ old('nama_kartu', $qrcode->nama_kartu) }}" autocomplete="off" maxlength="100" readonly required>

                    @error('nama_kartu')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Kartu</label>
                    <input type="text" name="nomor_kartu"
                        class="form-control @error('nomor_kartu') is-invalid @enderror"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()"
                        value="{{ old('nomor_kartu', $qrcode->nomor_kartu) }}" autocomplete="off" maxlength="30" readonly required>

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
                    <!-- Pastikan function JS confirmSaveUser mendukung form ini, atau ganti dengan submit biasa jika tidak diperlukan -->
                    <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                            <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Saat pegawai dipilih, otomatis mengisi Nama Kartu dan Nomor Kartu
    $('#id_peg').change(function () {
        let opt = $(this).find('option:selected');
        
        // Kosongkan input jika memilih default option "-- Pilih Pegawai --"
        if (!opt || !opt.val()) {
            $('input[name="nama_kartu"]').val('');
            $('input[name="nomor_kartu"]').val('');
            return;
        }

        let nama = opt.attr('data-nama') || '';
        let nik = opt.attr('data-nik') || '';
        let status = opt.attr('data-status') || '';

        $('input[name="nama_kartu"]').val(nama.toUpperCase());
        $('input[name="nomor_kartu"]').val(nik.toUpperCase());
        
        if (status) {
            $('#status_input').val(status);
            $('#status_display').val(status);
        }
    });
});
</script>
@endpush