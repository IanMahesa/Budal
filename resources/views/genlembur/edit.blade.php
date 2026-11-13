@extends('partials.all')

@section('title', 'Edit QR Lembur')

@section('content')
<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Edit Data Lembur</h4>
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

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('genlembur.update', $detailLembur->id_detail_lembur) }}" autocomplete="off">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pegawai</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="{{ $detailLembur->pegawai->nama ?? '-' }}" readonly>
                        <input type="hidden" name="id_pegawai" value="{{ $detailLembur->pegawai->id_pegawai ?? '' }}">   
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bagian / Sub Bagian</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;"
                            value="{{ optional(optional(optional($detailLembur->pegawai)->subbag)->bagian)->bag ?? '-' }} / {{ optional(optional($detailLembur->pegawai)->subbag)->sub_bag ?? '-' }}"
                            readonly>
                        <input type="hidden" name="id_subbag" value="{{ optional($detailLembur->pegawai)->id_subbag ?? '' }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="jenis_lembur" class="form-label">Jenis Lembur <span class="text-danger">*</span></label>
                        <select id="jenis_lembur" name="jenis_lembur"
                            class="form-select @error('jenis_lembur') is-invalid @enderror" required>
                            <option value="TERENCANA" {{ old('jenis_lembur', $detailLembur->lembur->jenis_lembur) === 'TERENCANA' ? 'selected' : '' }}>Terencana</option>
                            <option value="DADAKAN" {{ old('jenis_lembur', $detailLembur->lembur->jenis_lembur) === 'DADAKAN' ? 'selected' : '' }}>Dadakan</option>
                        </select>
                        @error('jenis_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_lembur" class="form-label">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_lembur" name="tanggal_lembur"
                            class="form-control @error('tanggal_lembur') is-invalid @enderror"
                            value="{{ old('tanggal_lembur', optional($detailLembur->lembur->tanggal_lembur)->format('Y-m-d')) }}" required>
                        @error('tanggal_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status Kehadiran</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="{{ $detailLembur->status_kehadiran }}" readonly>
                        <input type="hidden" name="status_kehadiran" value="{{ $detailLembur->status_kehadiran }}">
                    </div>
                </div>

                <div class="row" id="jadwal_lembur">
                    <div class="col-md-6 mb-3">
                        <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger required-schedule">*</span></label>
                        <input type="time" id="jam_mulai" name="jam_mulai"
                            class="form-control @error('jam_mulai') is-invalid @enderror"
                            value="{{ old('jam_mulai', $detailLembur->lembur->jam_mulai ? substr((string) $detailLembur->lembur->jam_mulai, 0, 5) : '') }}">
                        @error('jam_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger required-schedule">*</span></label>
                        <input type="time" id="jam_selesai" name="jam_selesai"
                            class="form-control @error('jam_selesai') is-invalid @enderror"
                            value="{{ old('jam_selesai', $detailLembur->lembur->jam_selesai ? substr((string) $detailLembur->lembur->jam_selesai, 0, 5) : '') }}">
                        @error('jam_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" maxlength="2000"
                        class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $detailLembur->lembur->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    Pengubahan ini hanya memperbarui jadwal dan keterangan lembur. Jam aktual hasil scan QR tidak berubah.
                </div>

                <div class="table-divider mt-4"></div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="{{ route('genlembur.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function toggleSchedule() {
        const scheduled = $('#jenis_lembur').val() === 'TERENCANA';
        $('#jadwal_lembur').toggleClass('d-none', !scheduled);
        $('#jam_mulai, #jam_selesai').prop('required', scheduled);
        $('.required-schedule').toggleClass('d-none', !scheduled);
    }

    $('#jenis_lembur').on('change', toggleSchedule);
    toggleSchedule();
});
</script>
@endpush