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

            <form method="POST" action="{{ route('geneqrdin.update', $qrcode->id_qrcode) }}" autocomplete="off" id="formUpdateUser">
                @csrf
                @method('PUT')

                <!-- Input Hidden untuk Data yang Dikirim Otomatis ke Database -->
                <input type="hidden" name="nama_kartu" id="nama_kartu" value="{{ old('nama_kartu', $qrcode->nama_kartu) }}">
                <input type="hidden" name="nomor_kartu" id="nomor_kartu" value="{{ old('nomor_kartu', $qrcode->nomor_kartu) }}">

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
                        <input type="hidden" id="status_input" name="status" value="{{ old('status', $qrcode->status ?? 'Aktif') }}">
                        <input type="text" id="status_display" class="form-control" value="{{ old('status', $qrcode->status ?? 'Aktif') }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3" id="subbag_div">
                        <label class="form-label">Bagian</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="{{ $qrcode->subbag->bagian->bag ?? '' }}" readonly>
                        <input type="hidden" id="id_bag" name="id_bag" value="{{ old('id_bag', $selectedBagianId ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3" id="subbag_option_div">
                        <label class="form-label">Sub Bagian</label>
                        <input type="text" class="form-control" style="background-color: #e9ecef;" value="{{ $qrcode->subbag->sub_bag ?? '' }}" readonly>
                        <input type="hidden" id="id_subag" name="id_subag" value="{{ old('id_subag', $selectedSubbagId ?? '') }}">
                    </div>
                </div>

                <div class="mb-3" id="pegawai_subbag_div">
                    <label class="form-label">Nama Pegawai pada Sub Bagian</label>
                    <div class="d-flex align-items-start gap-2">
                        <div id="pegawai_search_rows" class="flex-grow-1"></div>
                        <datalist id="pegawai-options"></datalist>
                        <button type="button" id="add_pegawai_row" class="btn-tambah1" aria-label="Tambah pegawai" title="Tambah pegawai">
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
                        value="{{ old('ket', $qrcode->ket) }}"
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
                        value="{{ old('tanggal_dinas', $qrcode->tanggal_dinas ? $qrcode->tanggal_dinas->format('Y-m-d') : '') }}"
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
                        <i class="fas fa-save"></i> Perbarui
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
let pegawaiSubbagOptions = [];
let pegawaiSearchRowCount = 0;

function addPegawaiSearchRow(selectedIdVal = '', selectedTextVal = '') {
    pegawaiSearchRowCount++;
    const row = $('<div>', { class: 'input-group mb-2 pegawai-search-row' });
    const input = $('<input>', {
        type: 'search',
        class: 'form-control pegawai-search',
        list: 'pegawai-options',
        placeholder: pegawaiSubbagOptions.length
            ? 'Cari nama atau NIK pegawai'
            : 'Memuat data pegawai...',
        autocomplete: 'off',
        required: true,
        value: selectedTextVal,
        'aria-label': 'Cari pegawai pada sub bagian'
    });
    const selectedId = $('<input>', {
        type: 'hidden',
        name: 'id_peg[]',
        class: 'pegawai-id',
        value: selectedIdVal
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
}

function loadPegawaiBySubag(idSubag, preselectedPegawai = []) {
    pegawaiSubbagOptions = [];
    resetPegawaiSearchRows();

    if (!idSubag) return;

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

            // Jika form edit memiliki data pegawai sebelumnya, render row berdasarkan data tersebut
            if (preselectedPegawai && preselectedPegawai.length > 0) {
                preselectedPegawai.forEach(function (item) {
                    let found = pegawaiSubbagOptions.find(p => p.id === String(item.id_peg));
                    addPegawaiSearchRow(item.id_peg, found ? found.label : (item.nama_label || ''));
                });
            } else {
                addPegawaiSearchRow();
            }

            updatePegawaiSearchRows();
        },
        error: function () {
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

$(document).ready(function () {
    let initialSubagId = $('#id_subag').val();
    let initialPegawai = @json(old('id_peg_objects', $selectedPegawaiList ?? []));

    if (initialSubagId) {
        loadPegawaiBySubag(initialSubagId, initialPegawai);
    } else {
        addPegawaiSearchRow();
    }
});
</script>
@endpush