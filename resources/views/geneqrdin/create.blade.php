@extends('partials.all')

@section('title','Tambah Data QR Code')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Tambah Data QR Code</h4>

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

            <form method="POST" action="{{ route('geneqrdin.store') }}" autocomplete="off" id="formSaveUser">
                @csrf

                <!-- Input Hidden untuk Data yang Dikirim Otomatis ke Database -->
                <input type="hidden" name="nama_kartu" id="nama_kartu">
                <input type="hidden" name="nomor_kartu" id="nomor_kartu">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis QR</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="Sub Bagian" readonly>
                        <input type="hidden" id="jenis_qr" name="jenis_qr" value="SUBBAG">
                            
                        @error('jenis_qr')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="status_div">
                        <label class="form-label">Status</label>
                        <input type="hidden" id="status_input" name="status" value="Aktif">
                        <input type="text" id="status_display" class="form-control" value="Aktif" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3" id="subbag_div">
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

                    <div class="col-md-6 mb-3" id="subbag_option_div">
                        <label class="form-label">Sub Bagian</label>
                        <select id="id_subag" name="id_subag" class="form-select" disabled>
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3" id="pegawai_subbag_div">
                    <label class="form-label">Nama Pegawai pada Sub Bagian</label>
                    <div class="d-flex align-items-start gap-2">
                        <div id="pegawai_search_rows" class="flex-grow-1"></div>
                        <datalist id="pegawai-options"></datalist>
                        <button type="button" id="add_pegawai_row" class="btn-tambah1" disabled aria-label="Tambah pegawai" title="Tambah pegawai">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div id="pegawai_search_error" class="text-danger small mt-1" role="alert"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="ket"
                        class="form-control @error('ket') is-invalid @enderror"
                        oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1)"
                        placeholder="Masukkan keterangan"
                        value="{{ old('ket') }}"
                        autocomplete="off"
                        maxlength="100"
                        required>

                    @error('ket')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div> 

                <div class="mb-3">
                    <label class="form-label" for="tanggal_dinas">Tanggal Dinas</label>
                    <input type="date" id="tanggal_dinas" name="tanggal_dinas"
                        class="form-control @error('tanggal_dinas') is-invalid @enderror"
                        value="{{ old('tanggal_dinas', now()->format('Y-m-d')) }}"
                        required>
                    @error('tanggal_dinas')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                  
                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('geneqrdin.index') }}" class="btn btn-back">
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
// ID QR Code berikutnya dari server
const nextIdQrcode = {{ $nextIdQrcode }};
let pegawaiSubbagOptions = [];
let pegawaiSearchRowCount = 0;

function addPegawaiSearchRow() {
    pegawaiSearchRowCount++;
    const row = $('<div>', { class: 'input-group mb-2 pegawai-search-row' });
    const input = $('<input>', {
        type: 'search',
        class: 'form-control pegawai-search',
        list: 'pegawai-options',
        placeholder: pegawaiSubbagOptions.length
            ? 'Cari nama atau NIK pegawai'
            : 'Pilih Sub Bagian terlebih dahulu',
        autocomplete: 'off',
        required: true,
        disabled: pegawaiSubbagOptions.length === 0,
        'aria-label': 'Cari pegawai pada sub bagian'
    });
    const selectedId = $('<input>', {
        type: 'hidden',
        name: 'id_peg[]',
        class: 'pegawai-id'
    });

    row.append(input, selectedId);

    if ($('#pegawai_search_rows .pegawai-search-row').length > 0) {
        const removeButton = $('<button>', {
            type: 'button',
            class: 'btn btn-outline-danger remove-pegawai-row',
            'aria-label': 'Hapus pegawai',
            title: 'Hapus pegawai'
        }).html('<i class="fas fa-minus"></i>');
        row.append(removeButton);
    }

    $('#pegawai_search_rows').append(row);
    updatePegawaiSearchRows();
}

function updatePegawaiSearchRows() {
    $('#pegawai_search_rows .pegawai-search-row').each(function () {
        const input = $(this).find('.pegawai-search');
        const selectedId = $(this).find('.pegawai-id').val();
        const isSelected = Boolean(selectedId);
        input.prop('disabled', pegawaiSubbagOptions.length === 0);

        if (isSelected) {
            input.prop('required', false);
        } else {
            input.prop('required', true);
        }
    });
}

function resetPegawaiSearchRows() {
    pegawaiSearchRowCount = 0;
    $('#pegawai_search_rows').empty();
    $('#pegawai-options').empty();
    $('#add_pegawai_row').prop('disabled', true);
    $('#pegawai_search_error').text('');
    addPegawaiSearchRow();
}

function loadSubBagian(idBag) {
    if (!idBag) {
        $('#id_subag').html('<option value="">-- Pilih Sub Bagian --</option>');
        $('#id_subag').prop('disabled', true);
        $('#nama_kartu').val('');
        $('#nomor_kartu').val('');
        return;
    }

    $('#id_subag').html('<option value="">Loading...</option>');
    $('#id_subag').prop('disabled', true);

    $.ajax({
        url: '/get-subag/' + idBag,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            let options = '<option value="">-- Pilih Sub Bagian --</option>';
            data.forEach(function (row) {
                options += '<option value="' + row.id_subag + '" '
                    + 'data-kode_subag="'+ (row.kode_subag || '') +'" '
                    + 'data-kode_bag="'+ (row.kode_bag || '') +'" '
                    + 'data-sub_bag="'+ (row.sub_bag || '') +'" '
                    + '>' + row.sub_bag + '</option>';
            });
            $('#id_subag').html(options);
            $('#id_subag').prop('disabled', false);
        }
    });
}

function loadPegawaiBySubag(idSubag) {
    pegawaiSubbagOptions = [];
    resetPegawaiSearchRows();

    if (!idSubag) return;

    $('#pegawai_search_rows .pegawai-search').prop('placeholder', 'Memuat pegawai...');

    $.ajax({
        url: '/get-pegawai/' + idSubag,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            pegawaiSubbagOptions = data.map(function (row) {
                return {
                    id: String(row.id_peg),
                    label: row.nama + ' - ' + row.nik
                };
            });

            const datalist = $('#pegawai-options').empty();
            pegawaiSubbagOptions.forEach(function (pegawai) {
                datalist.append($('<option>', { value: pegawai.label }));
            });

            $('#pegawai_search_rows .pegawai-search')
                .prop('disabled', pegawaiSubbagOptions.length === 0)
                .prop('placeholder', pegawaiSubbagOptions.length
                    ? 'Cari nama atau NIK pegawai'
                    : 'Tidak ada pegawai aktif pada sub bagian ini');
            $('#add_pegawai_row').prop('disabled', pegawaiSubbagOptions.length === 0);
            updatePegawaiSearchRows();
        },
        error: function () {
            $('#pegawai_search_rows .pegawai-search')
                .prop('disabled', true)
                .prop('placeholder', 'Gagal memuat pegawai. Pilih kembali sub bagian.');
            $('#pegawai_search_error').text('Daftar pegawai tidak dapat dimuat.');
        }
    });
}

$('#pegawai_search_rows').on('input', '.pegawai-search', function () {
    const input = $(this);
    const row = input.closest('.pegawai-search-row');
    const selectedId = row.find('.pegawai-id');
    const query = input.val().trim().toLocaleLowerCase();
    const match = pegawaiSubbagOptions.find(function (pegawai) {
        return pegawai.label.toLocaleLowerCase() === query;
    });
    const duplicate = match && $('#pegawai_search_rows .pegawai-id').not(selectedId).filter(function () {
        return this.value === match.id;
    }).length > 0;

    selectedId.val(match && !duplicate ? match.id : '');
    $('#pegawai_search_error').text(duplicate ? 'Pegawai tersebut sudah dipilih.' : '');
    input.toggleClass('is-invalid', Boolean(query && (!match || duplicate)));
    updatePegawaiSearchRows();
});

$('#add_pegawai_row').on('click', function () {
    addPegawaiSearchRow();
});

$('#pegawai_search_rows').on('click', '.remove-pegawai-row', function () {
    $(this).closest('.pegawai-search-row').remove();
    updatePegawaiSearchRows();
});

// Mengisi otomatis nama_kartu dan nomor_kartu (hidden) saat Sub Bagian dipilih
$('#id_subag').change(function () {
    let opt = $(this).find('option:selected');
    if (!opt || !opt.val()) {
        $('#nama_kartu').val('');
        $('#nomor_kartu').val('');
        return;
    }
    let kodeBag = opt.attr('data-kode_bag') || '';
    let kodeSub = opt.attr('data-kode_subag') || '';
    let subBag = opt.attr('data-sub_bag') || '';

    if (kodeBag) {
        $('#nama_kartu').val((kodeBag + '-' + subBag).toUpperCase());
        $('#nomor_kartu').val((kodeBag + '-' + kodeSub + '-' + nextIdQrcode).toUpperCase());
    }
});

$(document).ready(function () {
    resetPegawaiSearchRows();

    $('#id_bag').on('change', function () {
        loadSubBagian($(this).val());
    });

    $('#id_subag').on('change', function () {
        loadPegawaiBySubag($(this).val());
    });

    if ($('#id_subag').val()) {
        $('#id_subag').trigger('change');
    }
});
</script>
@endpush