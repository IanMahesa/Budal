@extends('partials.all')

@section('title','Edit Perijinan')

@section('content')
<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Edit Perijinan</h4>
            <div class="table-divider"></div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ijin.update', $perijinan->id_ijin) }}" autocomplete="off" id="formSaveUser">
                @csrf
                @method('PUT')
                <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <div class="form-control" style="background-color: #e9ecef;">
                            {{ old('jenis', $perijinan->jenis) }}
                        </div>

                        <input type="hidden" id="jenis" name="jenis" value="{{ old('jenis', $perijinan->jenis) }}">

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
                            value="{{ old('izin',$perijinan->izin) }}" maxlength="100" placeholder="Contoh : Istirahat" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

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
                            value="{{ old('kode',$perijinan->kode) }}" maxlength="5" style="text-transform:uppercase" 
                            oninput="this.value = this.value.toUpperCase()" placeholder="CT" required>

                        @error('kode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-3" id="subbag_div" style="{{ old('jenis',$perijinan->jenis)=='DINAS' ? '' : 'display:none' }}">
                        <label class="form-label">Bagian</label>
                        <select id="id_bag" class="form-select">
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagian as $row)
                                <option value="{{ $row->id_bag }}"
                                    {{ old('id_bag', optional($perijinan->subbag)->id_bag) == $row->id_bag ? 'selected':'' }}>
                                    {{ $row->bag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 mb-3" id="subbag_option_div" style="{{ old('jenis',$perijinan->jenis)=='DINAS' ? '' : 'display:none' }}">
                        <label class="form-label">Sub Bagian</label>
                        <select id="id_subag" name="id_subag" class="form-select"
                            {{ old('id_bag', optional($perijinan->subbag)->id_bag) ? '' : 'disabled' }}>
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

function tampilkanForm(){

    let jenis = $('#jenis').val();

    if(jenis=="DINAS"){

        $('#subbag_div').show();
        $('#subbag_option_div').show();

        $('#id_subag').prop('required',true);

    }else{

        $('#subbag_div').hide();
        $('#subbag_option_div').hide();

        $('#id_bag').val('');
        $('#id_subag').html('<option value="">-- Pilih Sub Bagian --</option>');
        $('#id_subag').prop('required',false);

    }

}

function loadSubBagian(id_bag){

    $('#id_subag').prop('disabled', true);
    $('#id_subag').html('<option value="">Loading...</option>');

    $.ajax({

        url:"{{ route('subbag.byBagian') }}",
        type:"GET",
        data:{id_bag:id_bag},

        success:function(data){

            let option='<option value="">-- Pilih Sub Bagian --</option>';

            let selected = $('#id_subag').data('selected') || '';

            $.each(data,function(i,item){

                let pilih=(selected == item.id_subag.toString())?'selected':'';

                option+='<option value="'+item.id_subag+'" '+pilih+'>'+item.sub_bag+'</option>';

            });

            $('#id_subag').html(option);
            $('#id_subag').prop('disabled', false);

        },
        error:function(){
            $('#id_subag')
                .html('<option value="">Sub Bagian gagal dimuat</option>')
                .prop('disabled', true);
        }

    });

}

$(function(){

    $('#id_subag').data('selected', "{{ old('id_subag', $perijinan->id_subag ?? '') }}");

    tampilkanForm();

    $('#jenis').change(function(){
        tampilkanForm();
    });

    $('#id_bag').change(function(){

        $('#id_subag').data('selected', '');

        if($(this).val()!=''){
            loadSubBagian($(this).val());
        } else {
            $('#id_subag')
                .html('<option value="">-- Pilih Sub Bagian --</option>')
                .prop('disabled', true);
        }

    });

    if ($('#jenis').val() === 'DINAS' && $('#id_bag').val() !== '') {
        loadSubBagian($('#id_bag').val());
    }

});

</script>
@endpush
