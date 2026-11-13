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

                <input type="hidden" name="nama_kartu" id="nama_kartu">
                <input type="hidden" name="nomor_kartu" id="nomor_kartu">

                <div class="row">
                    <div class="col-md-6 mb-3" id="subbag_div">
                        <label class="form-label">Jenis Lembur <span class="text-danger">*</span></label>
                        <select id="id_bag" class="form-select"
                            {{ in_array($jabatan, ['Manajer', 'A.Manajer'], true) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagian as $row)
                                <option value="{{ $row->id_bag }}"
                                    {{ old('id_bag', $selectedBagianId) == $row->id_bag ? 'selected' : '' }}>
                                    {{ $row->bag }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="id_bag" id="id_bag_value"
                            value="{{ old('id_bag', $selectedBagianId) }}">
                    </div>
                    <div class="col-md-6 mb-3" id="subbag_div">
                        <label class="form-label">Bagian <span class="text-danger">*</span></label>
                        <select id="id_bag" class="form-select"
                            {{ in_array($jabatan, ['Manajer', 'A.Manajer'], true) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagian as $row)
                                <option value="{{ $row->id_bag }}"
                                    {{ old('id_bag', $selectedBagianId) == $row->id_bag ? 'selected' : '' }}>
                                    {{ $row->bag }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="id_bag" id="id_bag_value"
                            value="{{ old('id_bag', $selectedBagianId) }}">
                    </div>

                    <div class="col-md-6 mb-3" id="subbag_option_div">
                        <label class="form-label">Sub Bagian <span id="subbag_required" class="text-danger">*</span></label>
                        <select id="id_subag" class="form-select"
                            {{ $jabatan === 'A.Manajer' ? 'disabled' : '' }}
                            {{ old('id_bag', $selectedBagianId) ? '' : 'disabled' }}>
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                        <input type="hidden" name="id_subag" id="id_subag_value"
                            value="{{ old('id_subag', $selectedSubbagId) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Data QR Dinas</h5>
                    <button type="button" id="add_dinas_entry" class="btn-tambah1" disabled
                        data-bs-toggle="modal" data-bs-target="#dinasEntryModal"
                        aria-label="Tambah data pegawai" title="Tambah data pegawai">
                        <i class="fas fa-plus"></i> Tambah Data
                    </button>
                </div>
                <div class="table-responsive mb-3">
                    <table id="tabelUser" class="table table-striped table-hover mb-0 table-head-color">
                        <thead>
                            <tr>
                                <th class="text-center" width="200">Nama</th>
                                <th class="text-center" width="70">Tanggal</th>
                                <th class="text-center">Keluar</th>
                                <th class="text-center">Masuk</th>
                                <th class="text-center" width="300">Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dinas_entries">
                            <tr id="empty_dinas_entries">
                                <td colspan="6" class="text-center text-muted">Belum ada data. Klik Tambah Data untuk mengisi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <datalist id="pegawai-options"></datalist>

                <div class="modal fade" id="dinasEntryModal" tabindex="-1" aria-labelledby="dinasEntryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="dinasEntryModalLabel">Tambah Data QR Dinas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" for="modal_pegawai_search">Nama</label>
                                    <input type="search" id="modal_pegawai_search" class="form-control"
                                        list="pegawai-options" placeholder="Cari nama atau NIK pegawai"
                                        autocomplete="off" required disabled>
                                    <input type="hidden" id="modal_id_peg">
                                    <div id="modal_pegawai_error" class="text-danger small mt-1" role="alert"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="modal_tanggal_dinas">Tanggal Dinas</label>
                                        <input type="date" id="modal_tanggal_dinas" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="modal_waktu_keluar">Waktu Mulai Absen Keluar</label>
                                        <input type="time" id="modal_waktu_keluar" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="modal_waktu_masuk">Batas Akhir Absen Masuk</label>
                                        <input type="time" id="modal_waktu_masuk" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="modal_ket">Keterangan</label>
                                    <textarea id="modal_ket" class="form-control" placeholder="Masukkan keterangan"
                                        maxlength="100" rows="3" autocomplete="off" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-back" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-save" id="save_dinas_entry"><i class="fas fa-save"></i> Save</button>
                                
                            </div>
                        </div>
                    </div>
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
const dinasJabatan = @json($jabatan);
const initialBagianId = @json((string) old('id_bag', $selectedBagianId ?? ''));
const initialSubbagId = @json((string) old('id_subag', $selectedSubbagId ?? ''));
const initialDinasEntries = @json(array_values((array) old('entries', [])));
const todayDate = @json(now()->format('Y-m-d'));
let pegawaiSubbagOptions = [];
let dinasEntryCount = 0;
let pendingDinasEntries = initialDinasEntries;

function addDinasEntry(entry, label) {
    const index = dinasEntryCount++;
    const row = $('<tr>');
    row.append($('<td>').text(label));
    row.append($('<td>').text(entry.tanggal_dinas));
    row.append($('<td>').text(entry.waktu_keluar));
    row.append($('<td>').text(entry.waktu_masuk));
    row.append($('<td>').text(entry.ket));

    const actions = $('<td>');
    actions.append($('<button>', {
        type: 'button',
        class: 'btn btn-delete btn-sm remove-dinas-entry',
        'aria-label': 'Hapus data ' + label
    }).html('<i class="fas fa-trash"></i>'));
    ['id_peg', 'tanggal_dinas', 'waktu_keluar', 'waktu_masuk', 'ket'].forEach(function (field) {
        actions.append($('<input>', {
            type: 'hidden',
            name: 'entries[' + index + '][' + field + ']',
            value: entry[field]
        }));
    });
    row.append(actions);
    $('#dinas_entries').append(row);
    updateDinasEntryList();
}

function resetDinasEntries(entries) {
    const emptyRow = $('<tr>', { id: 'empty_dinas_entries' });
    emptyRow.append($('<td>', {
        colspan: 6,
        class: 'text-center text-muted',
        text: 'Belum ada data. Klik Tambah Data untuk mengisi.'
    }));
    $('#dinas_entries').empty().append(emptyRow);
    dinasEntryCount = 0;
    (entries || []).forEach(function (entry) {
        const pegawai = pegawaiSubbagOptions.find(function (item) {
            return item.id === String(entry.id_peg);
        });
        if (pegawai) addDinasEntry(entry, pegawai.label);
    });
}

function updateDinasEntryList() {
    $('#empty_dinas_entries').toggle($('#dinas_entries tr:not(#empty_dinas_entries)').length === 0);
    $('#add_dinas_entry').prop('disabled', pegawaiSubbagOptions.length === 0);
}

function resetDinasEntryModal() {
    $('#modal_pegawai_search').val('').removeClass('is-invalid');
    $('#modal_id_peg').val('');
    $('#modal_tanggal_dinas').val(todayDate);
    $('#modal_waktu_keluar').val('');
    $('#modal_waktu_masuk').val('');
    $('#modal_ket').val('');
    $('#modal_pegawai_error').text('');
}

function saveDinasEntry() {
    const idPegawai = $('#modal_id_peg').val();
    const pegawai = pegawaiSubbagOptions.find(function (item) {
        return item.id === String(idPegawai);
    });
    const entry = {
        id_peg: idPegawai,
        tanggal_dinas: $('#modal_tanggal_dinas').val(),
        waktu_keluar: $('#modal_waktu_keluar').val(),
        waktu_masuk: $('#modal_waktu_masuk').val(),
        ket: $('#modal_ket').val().trim()
    };

    if (!pegawai) {
        $('#modal_pegawai_search').addClass('is-invalid');
        $('#modal_pegawai_error').text('Pilih pegawai dari daftar yang tersedia.');
        return;
    }

    if ($('#dinas_entries input[name$="[id_peg]"]').filter(function () {
        return this.value === idPegawai;
    }).length) {
        $('#modal_pegawai_search').addClass('is-invalid');
        $('#modal_pegawai_error').text('Pegawai tersebut sudah ditambahkan.');
        return;
    }

    if (!entry.tanggal_dinas || !entry.waktu_keluar || !entry.waktu_masuk || !entry.ket) {
        const invalidField = $('#dinasEntryModal .modal-body :input[required]').filter(function () {
            return !this.checkValidity();
        }).first().get(0);
        if (invalidField) {
            invalidField.reportValidity();
        }
        return;
    }

    if (entry.waktu_masuk <= entry.waktu_keluar) {
        $('#modal_waktu_masuk').get(0).setCustomValidity('Batas akhir absen masuk harus setelah waktu keluar.');
        $('#modal_waktu_masuk').get(0).reportValidity();
        $('#modal_waktu_masuk').get(0).setCustomValidity('');
        return;
    }

    addDinasEntry(entry, pegawai.label);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('dinasEntryModal')).hide();
}

function loadSubBagian(idBag, selectedSubag) {
    if (!idBag) {
        $('#id_subag').html('<option value="">-- Pilih Sub Bagian --</option>');
        $('#id_subag').prop('disabled', true);
        $('#id_subag_value').val('');
        pegawaiSubbagOptions = [];
        $('#pegawai-options').empty();
        $('#add_dinas_entry').prop('disabled', true);
        $('#modal_pegawai_search').prop('disabled', true);
        resetDinasEntries();
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
            if (selectedSubag) {
                    $('#id_subag').val(String(selectedSubag));
            }
            $('#id_subag_value').val($('#id_subag').val() || '');
            $('#id_subag').prop('disabled', dinasJabatan === 'A.Manajer');
            loadPegawaiByScope(idBag, $('#id_subag').val());
        }
    });
}

function loadPegawaiByScope(idBag, idSubag) {
    pegawaiSubbagOptions = [];
    $('#pegawai-options').empty();
    $('#add_dinas_entry').prop('disabled', true);

    if (!idBag || (['Manajer', 'A.Manajer'].includes(dinasJabatan) && !idSubag)) {
        resetDinasEntries([]);
        return;
    }

    const entriesToRestore = pendingDinasEntries;
    pendingDinasEntries = [];
    $('#modal_pegawai_search')
        .prop('disabled', true)
        .prop('placeholder', 'Memuat pegawai...');

    $.ajax({
        url: '{{ route('qrcode.dinas.get-pegawai') }}',
        type: 'GET',
        dataType: 'json',
        data: {
            id_bag: idBag,
            id_subag: idSubag || ''
        },
        success: function (data) {
            pegawaiSubbagOptions = data.map(function (row) {
                return {
                    id: String(row.id_peg),
                    label: row.nama 
                };
            });

            const datalist = $('#pegawai-options').empty();
            pegawaiSubbagOptions.forEach(function (pegawai) {
                datalist.append($('<option>', { value: pegawai.label }));
            });

            $('#modal_pegawai_search')
                .prop('disabled', pegawaiSubbagOptions.length === 0)
                .prop('placeholder', pegawaiSubbagOptions.length
                    ? 'Cari nama atau NIK pegawai'
                    : 'Tidak ada pegawai aktif pada pilihan bagian/sub bagian ini');
            resetDinasEntries(entriesToRestore);
            updateDinasEntryList();
        },
        error: function () {
            $('#modal_pegawai_search')
                .prop('disabled', true)
                .prop('placeholder', 'Gagal memuat pegawai. Periksa kembali bagian dan sub bagian.');
        }
    });
}

$('#modal_pegawai_search').on('input', function () {
    const input = $(this);
    const query = input.val().trim().toLocaleLowerCase();
    const match = pegawaiSubbagOptions.find(function (pegawai) {
        return pegawai.label.toLocaleLowerCase() === query;
    });
    const duplicate = match && $('#dinas_entries input[name$="[id_peg]"]').filter(function () {
        return this.value === match.id;
    }).length > 0;

    $('#modal_id_peg').val(match && !duplicate ? match.id : '');
    $('#modal_pegawai_error').text(duplicate ? 'Pegawai tersebut sudah ditambahkan.' : '');
    input.toggleClass('is-invalid', Boolean(query && (!match || duplicate)));
});

$('#add_dinas_entry').on('click', function () {
    resetDinasEntryModal();
});

$('#dinas_entries').on('click', '.remove-dinas-entry', function () {
    $(this).closest('tr').remove();
    updateDinasEntryList();
});

$('#save_dinas_entry').on('click', function () {
    saveDinasEntry();
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
    resetDinasEntries([]);
    $('#id_bag_value').val($('#id_bag').val() || '');
    updateDinasEntryList();

    const subbagRequired = ['Manajer', 'A.Manajer'].includes(dinasJabatan);
    $('#id_subag').prop('required', subbagRequired);
    $('#subbag_required').prop('hidden', !subbagRequired);

    $('#id_bag').on('change', function () {
        pendingDinasEntries = [];
        resetDinasEntryModal();
        resetDinasEntries([]);
        $('#id_bag_value').val($(this).val() || '');
        loadSubBagian($(this).val(), '');
    });

    $('#id_subag').on('change', function () {
        pendingDinasEntries = [];
        resetDinasEntryModal();
        resetDinasEntries([]);
        $('#id_subag_value').val($(this).val() || '');
        loadPegawaiByScope($('#id_bag').val(), $(this).val());
    });

    if (initialBagianId) {
        loadSubBagian(initialBagianId, initialSubbagId);
    }
});
</script>
@endpush