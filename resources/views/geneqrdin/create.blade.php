@extends('partials.all')

@section('title','Tambah Data QR Code')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Buat QR Code Dinas</h4>
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

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0">Data QR Dinas</h5>
                    <span class="text-primary">Terpilih: <strong id="jumlah_pegawai">0</strong></span>
                </div>
                <div class="input-group mb-3">
                    <input type="search" id="cari_pegawai" class="form-control"
                        list="pegawai-options" placeholder="Cari nama pegawai untuk ditambahkan"
                        autocomplete="off" aria-label="Cari nama pegawai untuk ditambahkan" disabled>
                    <datalist id="pegawai-options"></datalist>
                    <button type="button" class="btn btn-tambah" id="tambah_pegawai"
                        aria-label="Tambah pegawai" title="Tambah pegawai" disabled>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="pegawai_add_error" class="text-danger small mb-3" role="alert"></div>
                <div id="pegawai_load_error" class="text-danger small mb-3" role="alert"></div>
                <div class="accordion mb-3" id="accordionPegawai"></div>
                <p id="pegawai_tidak_ditemukan" class="text-center text-muted">
                    Belum ada pegawai ditambahkan. Cari nama lalu tekan tombol +.
                </p>
                <div id="pegawai_kosong" class="text-center text-muted d-none mb-3">
                    Tidak ada pegawai aktif pada pilihan bagian/sub bagian ini.
                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('geneqrdin.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-save" onclick="confirmSaveDinas(event)">
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
const nextIdQrcode = {{ $nextIdQrcode }};
const dinasJabatan = @json($jabatan);
const initialBagianId = @json((string) old('id_bag', $selectedBagianId ?? ''));
const initialSubbagId = @json((string) old('id_subag', $selectedSubbagId ?? ''));
const initialDinasEntries = @json(array_values((array) old('entries', [])));
const todayDate = @json(now()->format('Y-m-d'));
let pegawaiSubbagOptions = [];
let pendingDinasEntries = initialDinasEntries;

function setEmployeeEnabled(item, enabled) {
    item.find('.detail-pegawai')
        .prop('disabled', !enabled)
        .prop('required', enabled);
}

function updateEmployeeCount() {
    const count = $('.pegawai-checkbox:checked').length;
    $('#jumlah_pegawai').text(count);
    $('#pegawai_tidak_ditemukan').toggleClass('d-none', count > 0);
}

function confirmSaveDinas(event) {
    event.preventDefault();
    const selectedEmployees = $('#accordionPegawai .pegawai-checkbox:checked');

    if (!selectedEmployees.length) {
        $('#pegawai_add_error').text('Pilih minimal satu pegawai dan lengkapi data dinasnya.');
        return;
    }

    $('#pegawai_add_error').text('');
    let invalidField = selectedEmployees.closest('.pegawai-item')
        .find('.detail-pegawai[required]')
        .filter(function () {
            return !this.checkValidity();
        })
        .first()
        .get(0);

    if (invalidField) {
        const item = $(invalidField).closest('.pegawai-item');
        item.removeClass('d-none');
        bootstrap.Collapse.getOrCreateInstance(item.find('.accordion-collapse').get(0)).show();
        setTimeout(function () {
            invalidField.reportValidity();
        }, 350);
        return;
    }

    let invalidTimeField = null;
    selectedEmployees.each(function () {
        const item = $(this).closest('.pegawai-item');
        const waktuKeluar = item.find('[name$="[waktu_keluar]"]').val();
        const waktuMasuk = item.find('[name$="[waktu_masuk]"]').val();
        if (waktuMasuk <= waktuKeluar) {
            invalidTimeField = item.find('[name$="[waktu_masuk]"]').get(0);
            return false;
        }
    });

    if (invalidTimeField) {
        const item = $(invalidTimeField).closest('.pegawai-item');
        invalidTimeField.setCustomValidity('Batas akhir absen masuk harus setelah waktu keluar.');
        bootstrap.Collapse.getOrCreateInstance(item.find('.accordion-collapse').get(0)).show();
        setTimeout(function () {
            invalidTimeField.reportValidity();
            invalidTimeField.setCustomValidity('');
        }, 350);
        return;
    }

    confirmSaveUser(event);
}

function createEmployeeItem(pegawai, entry) {
    const isSelected = Boolean(entry);
    const headingId = 'pegawaiHeading' + pegawai.id;
    const detailId = 'pegawaiDetail' + pegawai.id;
    const item = $('<div>', {
        class: 'accordion-item pegawai-item' + (isSelected ? '' : ' d-none')
    });
    const heading = $('<h2>', {
        class: 'accordion-header',
        id: headingId
    });
    const button = $('<div>', {
        class: 'accordion-button' + (isSelected ? '' : ' collapsed') + ' d-flex gap-3',
        role: 'button',
        'data-bs-toggle': 'collapse',
        'data-bs-target': '#' + detailId,
        'aria-expanded': isSelected ? 'true' : 'false',
        'aria-controls': detailId
    });
    const checkbox = $('<input>', {
        type: 'checkbox',
        name: 'entries[' + pegawai.id + '][id_peg]',
        class: 'form-check-input pegawai-checkbox',
        value: pegawai.id,
        checked: isSelected,
        'aria-label': 'Pilih ' + pegawai.label
    });
    button.append(checkbox);
    button.append($('<span>', { class: 'flex-grow-1', text: pegawai.label }));
    heading.append(button);

    const collapse = $('<div>', {
        id: detailId,
        class: 'accordion-collapse collapse',
        'aria-labelledby': headingId,
        'data-bs-parent': '#accordionPegawai'
    });
    const body = $('<div>', { class: 'accordion-body' });
    const row = $('<div>', { class: 'row' });
    const fields = [
        { name: 'tanggal_dinas', label: 'Tanggal Dinas', type: 'date', value: (entry && entry.tanggal_dinas) || todayDate, column: 'col-md-4' },
        { name: 'waktu_keluar', label: 'Waktu Mulai Absen Keluar', type: 'time', value: (entry && entry.waktu_keluar) || '', column: 'col-md-4' },
        { name: 'waktu_masuk', label: 'Batas Akhir Absen Masuk', type: 'time', value: (entry && entry.waktu_masuk) || '', column: 'col-md-4' }
    ];

    fields.forEach(function (field) {
        const fieldId = field.name + '_' + pegawai.id;
        const group = $('<div>', { class: field.column + ' mb-3' });
        group.append($('<label>', {
            class: 'form-label',
            for: fieldId,
            text: field.label + ' *'
        }));
        group.append($('<input>', {
            type: field.type,
            id: fieldId,
            name: 'entries[' + pegawai.id + '][' + field.name + ']',
            class: 'form-control detail-pegawai',
            value: field.value,
            disabled: !isSelected,
            required: isSelected
        }));
        row.append(group);
    });

    const ketGroup = $('<div>', { class: 'col-md-12 mb-3' });
    ketGroup.append($('<label>', {
        class: 'form-label',
        for: 'ket_' + pegawai.id,
        text: 'Keterangan *'
    }));
    ketGroup.append($('<textarea>', {
        id: 'ket_' + pegawai.id,
        name: 'entries[' + pegawai.id + '][ket]',
        rows: 2,
        maxlength: 100,
        class: 'form-control detail-pegawai',
        disabled: !isSelected,
        required: isSelected,
        text: (entry && entry.ket) || ''
    }));
    row.append(ketGroup);

    body.append(row);
    collapse.append(body);
    item.append(heading, collapse);
    $('#accordionPegawai').append(item);

    if (isSelected) {
        bootstrap.Collapse.getOrCreateInstance(collapse.get(0), { toggle: false }).show();
    }
}

function renderEmployees(entries, showNoEmployees = false) {
    const entriesByPegawai = {};
    (entries || []).forEach(function (entry) {
        if (entry.id_peg) {
            entriesByPegawai[String(entry.id_peg)] = entry;
        }
    });

    $('#accordionPegawai').empty();
    pegawaiSubbagOptions.forEach(function (pegawai) {
        createEmployeeItem(pegawai, entriesByPegawai[pegawai.id] || null);
    });
    $('#pegawai_kosong').toggleClass('d-none', !showNoEmployees || pegawaiSubbagOptions.length > 0);
    updateEmployeeCount();
    if (showNoEmployees && pegawaiSubbagOptions.length === 0) {
        $('#pegawai_tidak_ditemukan').addClass('d-none');
    }
}

function loadSubBagian(idBag, selectedSubag) {
    pegawaiSubbagOptions = [];
    $('#pegawai-options').empty();
    $('#cari_pegawai, #tambah_pegawai').prop('disabled', true);
    $('#pegawai_add_error, #pegawai_load_error').text('');
    $('#pegawai_kosong, #pegawai_tidak_ditemukan').addClass('d-none');
    renderEmployees([]);

    if (!idBag) {
        $('#id_subag').html('<option value="">-- Pilih Sub Bagian --</option>');
        $('#id_subag').prop('disabled', true);
        $('#id_subag_value').val('');
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
        },
        error: function () {
            $('#id_subag').html('<option value="">Gagal memuat sub bagian</option>');
            $('#id_subag').prop('disabled', true);
            $('#pegawai_load_error').text('Gagal memuat sub bagian. Silakan coba pilih bagian kembali.');
        }
    });
}

function loadPegawaiByScope(idBag, idSubag) {
    pegawaiSubbagOptions = [];
    $('#pegawai-options').empty();
    $('#cari_pegawai, #tambah_pegawai').prop('disabled', true);
    $('#pegawai_load_error').text('');
    $('#pegawai_kosong, #pegawai_tidak_ditemukan').addClass('d-none');
    renderEmployees([]);

    if (!idBag || (['Manajer', 'A.Manajer'].includes(dinasJabatan) && !idSubag)) {
        renderEmployees([]);
        return;
    }

    const entriesToRestore = pendingDinasEntries;
    pendingDinasEntries = [];
    $('#cari_pegawai')
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
                    label: row.nama + ' (#' + row.id_peg + ')'
                };
            });

            const datalist = $('#pegawai-options').empty();
            pegawaiSubbagOptions.forEach(function (pegawai) {
                datalist.append($('<option>', { value: pegawai.label }));
            });

            $('#cari_pegawai')
                .prop('disabled', pegawaiSubbagOptions.length === 0)
                .prop('placeholder', 'Cari nama pegawai untuk ditambahkan');
            $('#tambah_pegawai').prop('disabled', pegawaiSubbagOptions.length === 0);
            renderEmployees(entriesToRestore, true);
        },
        error: function () {
            renderEmployees([]);
            $('#pegawai_load_error').text('Gagal memuat pegawai. Periksa kembali bagian dan sub bagian.');
        }
    });
}

function addSearchedEmployee() {
    const label = $('#cari_pegawai').val().trim().toLocaleLowerCase();
    const pegawai = pegawaiSubbagOptions.find(function (item) {
        return item.label.toLocaleLowerCase() === label;
    });

    if (!pegawai) {
        $('#pegawai_add_error').text('Pilih pegawai yang sesuai dari daftar pencarian.');
        return;
    }

    const item = $('#accordionPegawai .pegawai-item').filter(function () {
        return $(this).find('.pegawai-checkbox').val() === pegawai.id;
    }).first();
    const checkbox = item.find('.pegawai-checkbox');
    $('#pegawai_add_error').text('');

    if (!checkbox.prop('checked')) {
        checkbox.prop('checked', true).trigger('change');
    } else {
        item.removeClass('d-none');
        bootstrap.Collapse.getOrCreateInstance(item.find('.accordion-collapse').get(0)).show();
        updateEmployeeCount();
    }

    $('#cari_pegawai').val('').trigger('focus');
}

$('#tambah_pegawai').on('click', addSearchedEmployee);
$('#cari_pegawai').on('keydown', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addSearchedEmployee();
    }
}).on('input', function () {
    $('#pegawai_add_error').text('');
});

$('#accordionPegawai').on('change', '.pegawai-checkbox', function () {
    const item = $(this).closest('.pegawai-item');
    const selected = this.checked;
    setEmployeeEnabled(item, selected);
    if (selected) {
        item.removeClass('d-none');
        bootstrap.Collapse.getOrCreateInstance(item.find('.accordion-collapse').get(0)).show();
    } else {
        bootstrap.Collapse.getOrCreateInstance(item.find('.accordion-collapse').get(0)).hide();
        item.addClass('d-none');
    }
    updateEmployeeCount();
});

$('#accordionPegawai').on('click', '.pegawai-checkbox', function (event) {
    event.stopPropagation();
});

$('#accordionPegawai').on('shown.bs.collapse hidden.bs.collapse', '.accordion-collapse', function () {
    $(this).prev('.accordion-header').find('.accordion-button')
        .toggleClass('collapsed', !$(this).hasClass('show'))
        .attr('aria-expanded', $(this).hasClass('show'));
});

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
    $('#id_bag_value').val($('#id_bag').val() || '');

    const subbagRequired = ['Manajer', 'A.Manajer'].includes(dinasJabatan);
    $('#id_subag').prop('required', subbagRequired);
    $('#subbag_required').prop('hidden', !subbagRequired);

    $('#id_bag').on('change', function () {
        pendingDinasEntries = [];
        $('#cari_pegawai').val('');
        $('#pegawai_add_error').text('');
        $('#id_bag_value').val($(this).val() || '');
        loadSubBagian($(this).val(), '');
    });

    $('#id_subag').on('change', function () {
        pendingDinasEntries = [];
        $('#cari_pegawai').val('');
        $('#pegawai_add_error').text('');
        $('#id_subag_value').val($(this).val() || '');
        loadPegawaiByScope($('#id_bag').val(), $(this).val());
    });

    if (initialBagianId) {
        loadSubBagian(initialBagianId, initialSubbagId);
    }
});
</script>
@endpush