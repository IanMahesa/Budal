@extends('partials.all')

@section('title','Tambah Pegawai')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah Pegawai</h4>

            <div class="table-divider"></div>

            @if ($errors->any())
                <div class="alert alert-danger mx-3 mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('pegawai.store') }}" autocomplete="off" id="formSaveUser">
                @csrf
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label>NIK</label>
                        <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" autocomplete="off" 
                            pattern="[0-9]{5,8}" minlength="5" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>
                                Laki-Laki
                            </option>
                            <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>
                                Perempuan
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-8 mb-3">
                        <label>Jabatan <span class="text-danger">*</span></label>
                        <select name="jabatan" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Direktur" {{ old('jabatan')=='Direktur' ? 'selected' : '' }}>
                                Direktur
                            </option>
                            <option value="Manajer" {{ old('jabatan')=='Manajer' ? 'selected' : '' }}>
                                Manajer
                            </option>
                            <option value="A.Manajer" {{ old('jabatan')=='A.Manajer' ? 'selected' : '' }}>
                                Asisten Manajer
                            </option>
                            <option value="Staff" {{ old('jabatan')=='Staff' ? 'selected' : '' }}>
                                Staff
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Aktif"
                                {{ old('status','Aktif')=='Aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="Tidak Aktif"
                                {{ old('status')=='Tidak Aktif' ? 'selected' : '' }}>
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label>Bagian</label>
                        <select name="id_bag" id="id_bag" class="form-select" required>
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagian as $item)
                                <option value="{{ $item->id_bag }}" {{ old('id_bag') == $item->id_bag ? 'selected' : '' }}>
                                    {{ $item->bag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Sub Bagian</label>
                        <select name="id_subag"
                                id="id_subag"
                                class="form-select"
                                required
                                data-selected-value="{{ old('id_subag') }}"
                                {{ old('id_bag') ? '' : 'disabled' }}>
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                    </div> 

                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
    
                    <a href="{{ route('pegawai.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                        <i class="fas fa-save"></i> Save
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bagianSelect = document.getElementById('id_bag');
    const subBagSelect = document.getElementById('id_subag');

    function loadSubBagian(idBag) {
        if (!idBag) {
            subBagSelect.innerHTML = '<option value="">-- Pilih Sub Bagian --</option>';
            subBagSelect.disabled = true;
            return;
        }

        subBagSelect.innerHTML = '<option value="">Loading...</option>';
        subBagSelect.disabled = true;

        fetch('/pegawai/subbagian/' + idBag)
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                let html = '<option value="">-- Pilih Sub Bagian --</option>';
                data.forEach(function (row) {
                    html += '<option value="' + row.id_subag + '">' + row.sub_bag + '</option>';
                });

                subBagSelect.innerHTML = html;
                subBagSelect.disabled = false;

                const selectedSubBag = subBagSelect.getAttribute('data-selected-value');
                if (selectedSubBag) {
                    subBagSelect.value = selectedSubBag;
                }
            });
    }

    bagianSelect.addEventListener('change', function () {
        loadSubBagian(this.value);
    });

    if (bagianSelect.value) {
        loadSubBagian(bagianSelect.value);
    }
});
</script>

@endpush