@extends('partials.all')

@section('title','Tambah Perijinan')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah Perijinan</h4>

            <div class="table-divider"></div>

            <form method="POST" action="{{ route('ijin.store') }}" autocomplete="off" id="formSaveUser">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Jenis</label>
                        <div class="form-control" style="background-color: #e9ecef;">
                            {{ $jenis }}
                        </div>
                            <input type="hidden" id="jenis" name="jenis" value="{{ $jenis }}">

                        @error('jenis')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Nama Ijin <span class="text-danger">*</span>
                    </label>
                        <input type="text" name="izin" class="form-control @error('izin') is-invalid @enderror"
                            value="{{ old('izin') }}" maxlength="100" placeholder="Contoh : Cuti Tahunan" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

                        @error('izin')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Kode <span class="text-danger">*</span>
                    </label>
                        <input type="text" name="kode" class="form-control text-uppercase @error('kode') is-invalid @enderror"
                            value="{{ old('kode') }}" maxlength="5" style="text-transform:uppercase" 
                            oninput="this.value = this.value.toUpperCase()" placeholder="CT" required>

                        @error('kode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-3" id="subbag_div" style="display:none">
                        <label class="form-label">Bagian</label>
                        <select id="id_bag" class="form-select">
                            <option value="">-- Pilih Bagian --</option>

                            @foreach($bagian as $row)
                                <option value="{{ $row->id_bag }}">
                                    {{ $row->bag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3" id="subbag_option_div" style="display:none">
                        <label class="form-label">Sub Bagian</label>
                        <select name="id_subag" id="id_subag" class="form-select" required
                                data-selected-value="{{ old('id_subag') }}"
                                {{ old('id_bag') ? '' : 'disabled' }}>
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                    </div> 

                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
    
                    <a href="{{ route('ijin.index') }}" class="btn btn-back">
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

function tampilkanForm() {

    let jenis = $('#jenis').val();

    if (jenis === 'DINAS') {
        $('#subbag_div').show();
        $('#subbag_option_div').show();
        $('#id_subag').prop('required', true);
    } else {
        $('#subbag_div').hide();
        $('#subbag_option_div').hide();
        $('#id_bag').val('');
        $('#id_subag').empty()
            .append('<option value="">-- Pilih Sub Bagian --</option>');
        $('#id_subag').prop('required', false);
    }
}

function loadSubBagian(id_bag) {

    $('#id_subag').prop('disabled', true);
    $('#id_subag').html('<option value="">Loading...</option>');

    $.ajax({
        url: "{{ route('subbag.byBagian') }}",
        type: "GET",
        data: {
            id_bag: id_bag
        },
        success: function(data) {
            let option = '<option value="">-- Pilih Sub Bagian --</option>';
            $.each(data, function(i, item) {
                option += '<option value="' + item.id_subag + '">' + item.sub_bag + '</option>';
            });
            $('#id_subag').html(option);
            $('#id_subag').prop('disabled', false);
        },
        error: function() {
            $('#id_subag')
                .html('<option value="">Sub Bagian gagal dimuat</option>')
                .prop('disabled', true);
        }
    });
}

    $(document).ready(function () {
        tampilkanForm();
        $('#jenis').on('change', function () {
            tampilkanForm();
        });

        $('#id_bag').on('change', function () {
            let id_bag = $(this).val();
            if (id_bag != '') {
                loadSubBagian(id_bag);
            } else {
                $('#id_subag')
                    .html('<option value="">-- Pilih Sub Bagian --</option>')
                    .prop('disabled', true);
            }
        });
});

</script>
@endpush