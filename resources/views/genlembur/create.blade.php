@extends('partials.all')

@section('title', 'Buat QR Lembur')

@section('content')
<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Buat QR Code Lembur</h4>
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

            <form method="POST" action="{{ route('genlembur.store') }}" autocomplete="off">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="jenis_lembur" class="form-label">Jenis Lembur <span class="text-danger">*</span></label>
                        <select id="jenis_lembur" name="jenis_lembur"
                            class="form-select @error('jenis_lembur') is-invalid @enderror" required>
                            <option value="TERENCANA" {{ old('jenis_lembur', 'TERENCANA') === 'TERENCANA' ? 'selected' : '' }}>Terencana</option>
                            <option value="DADAKAN" {{ old('jenis_lembur') === 'DADAKAN' ? 'selected' : '' }}>Dadakan</option>
                        </select>
                        @error('jenis_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="col-md-4 mb-3">
                        <label for="id_bag" class="form-label">Bagian <span class="text-danger">*</span></label>
                        <select id="id_bag" class="form-select" disabled>
                            @forelse ($bagian as $row)
                                <option value="{{ $row->id_bag }}" selected>{{ $row->bag }}</option>
                            @empty
                                <option value="">Bagian belum tersedia</option>
                            @endforelse
                        </select>
                        @if ($bagian->isNotEmpty())
                            <input type="hidden" name="id_bag" value="{{ $bagian->first()->id_bag }}">
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_subag" class="form-label">Sub Bagian</label>
                        <select id="id_subag" class="form-select" disabled>
                            @if ($hasSubbag && $subag->isNotEmpty())
                                @foreach ($subag as $row)
                                    <option value="{{ $row->id_subag }}" data-bag="{{ $row->id_bag }}" selected>
                                        {{ $row->sub_bag }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" selected>Semua sub bagian pada bagian ini</option>
                            @endif
                        </select>
                        @if ($hasSubbag && $subag->isNotEmpty())
                            <input type="hidden" name="id_subag" value="{{ $subag->first()->id_subag }}">
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0">Lembur Pegawai</h5>
                    <span class="text-primary">Terpilih: <strong id="jumlah_pegawai">0</strong></span>
                </div>

                <div class="input-group mb-3">
                    <input type="search" id="cari_pegawai" class="form-control"
                        list="daftar_nama_pegawai" placeholder="Cari nama pegawai untuk ditambahkan"
                        autocomplete="off" aria-label="Cari nama pegawai untuk ditambahkan">
                    <datalist id="daftar_nama_pegawai">
                        @foreach ($pegawai as $row)
                            <option value="{{ $row->nama }} (#{{ $row->id_peg }})"></option>
                        @endforeach
                    </datalist>
                    <button type="button" class="btn btn-tambah" id="tambah_pegawai"
                        aria-label="Tambah pegawai" title="Tambah pegawai">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="pegawai_add_error" class="text-danger small mb-3" role="alert"></div>

                <div class="accordion mb-3" id="accordionPegawai">
                    @forelse ($pegawai as $row)
                        @php
                            $oldEntry = old('entries.' . $row->id_peg, []);
                            $isSelected = in_array((string) $row->id_peg, array_map('strval', (array) old('id_peg', [])), true);
                        @endphp
                        <div class="accordion-item pegawai-item {{ $isSelected ? '' : 'd-none' }}"
                            data-bag="{{ $row->id_bag }}" data-subag="{{ $row->id_subag }}"
                            data-name="{{ mb_strtolower($row->nama) }}"
                            data-label="{{ $row->nama }} (#{{ $row->id_peg }})">
                            <h2 class="accordion-header" id="pegawaiHeading{{ $row->id_peg }}">
                                <div class="accordion-button collapsed d-flex gap-3" role="button"
                                    data-bs-toggle="collapse" data-bs-target="#pegawaiDetail{{ $row->id_peg }}"
                                    aria-expanded="false" aria-controls="pegawaiDetail{{ $row->id_peg }}">
                                    <input type="checkbox" name="id_peg[]" class="form-check-input pegawai-checkbox"
                                        value="{{ $row->id_peg }}" aria-label="Pilih {{ $row->nama }}"
                                        {{ $isSelected ? 'checked' : '' }} {{ $row->id_qrcode ? '' : 'disabled' }}>
                                    <span class="flex-grow-1">{{ $row->nama }}</span>
                                    @if ($row->id_qrcode)
                                        <span class="badge bg-success">QR Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">QR belum tersedia</span>
                                    @endif
                                </div>
                            </h2>
                            <div id="pegawaiDetail{{ $row->id_peg }}" class="accordion-collapse collapse"
                                aria-labelledby="pegawaiHeading{{ $row->id_peg }}" data-bs-parent="#accordionPegawai">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tanggal Lembur <span class="text-danger">*</span></label>
                                            <input type="date" name="entries[{{ $row->id_peg }}][tanggal_lembur]"
                                                class="form-control detail-pegawai"
                                                value="{{ $oldEntry['tanggal_lembur'] ?? now()->format('Y-m-d') }}"
                                                {{ $isSelected ? '' : 'disabled' }}>
                                            @error('entries.' . $row->id_peg . '.tanggal_lembur')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Jam Mulai <span class="text-danger required-schedule">*</span></label>
                                            <input type="time" name="entries[{{ $row->id_peg }}][jam_mulai]"
                                                class="form-control detail-pegawai jam-mulai"
                                                value="{{ $oldEntry['jam_mulai'] ?? '' }}"
                                                {{ $isSelected ? '' : 'disabled' }}>
                                            @error('entries.' . $row->id_peg . '.jam_mulai')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Jam Selesai <span class="text-danger required-schedule">*</span></label>
                                            <input type="time" name="entries[{{ $row->id_peg }}][jam_selesai]"
                                                class="form-control detail-pegawai jam-selesai"
                                                value="{{ $oldEntry['jam_selesai'] ?? '' }}"
                                                {{ $isSelected ? '' : 'disabled' }}>
                                            @error('entries.' . $row->id_peg . '.jam_selesai')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Keterangan</label>
                                            <textarea name="entries[{{ $row->id_peg }}][keterangan]" rows="2"
                                                maxlength="2000" class="form-control detail-pegawai"
                                                {{ $isSelected ? '' : 'disabled' }}>{{ $oldEntry['keterangan'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted p-3">Belum ada pegawai aktif.</div>
                    @endforelse
                </div>
                <p id="pegawai_tidak_ditemukan" class="text-center text-muted {{ $pegawai->isEmpty() ? '' : 'd-none' }}">
                    Belum ada pegawai ditambahkan. Cari nama lalu tekan tombol +.
                </p>
                @error('id_peg')
                    <div class="text-danger small mb-3">{{ $message }}</div>
                @enderror
                @error('id_peg.*')
                    <div class="text-danger small mb-3">{{ $message }}</div>
                @enderror

                <div class="alert alert-info">
                    Pilih pegawai dan isi detail lembur pada accordion masing-masing. QR peserta menggunakan QR Pegawai yang sudah aktif.
                </div>

                <div class="table-divider mt-4"></div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="{{ route('genlembur.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-qrcode"></i> Simpan
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
    function updateCount() {
        $('#jumlah_pegawai').text($('.pegawai-checkbox:checked').length);
    }

    function setEmployeeEnabled(item, enabled) {
        item.find('.detail-pegawai').prop('disabled', !enabled);
        item.find('input[type="date"]').prop('required', enabled);
        item.find('.jam-mulai, .jam-selesai')
            .prop('required', enabled && $('#jenis_lembur').val() === 'TERENCANA');
    }

    function updateEmployeeCount() {
        updateCount();
        $('#pegawai_tidak_ditemukan').toggleClass('d-none', $('.pegawai-checkbox:checked').length > 0);
    }

    function addSearchedEmployee() {
        const label = $('#cari_pegawai').val().trim();
        const employee = $('.pegawai-item').filter(function () {
            return $(this).attr('data-label') === label;
        }).first();

        if (!employee.length) {
            $('#pegawai_add_error').text('Pilih pegawai yang sesuai dari daftar pencarian.');
            return;
        }

        const checkbox = employee.find('.pegawai-checkbox');
        if (checkbox.prop('disabled')) {
            $('#pegawai_add_error').text('Pegawai ini belum memiliki QR Code aktif.');
            return;
        }

        $('#pegawai_add_error').text('');
        employee.removeClass('d-none');
        if (!checkbox.prop('checked')) {
            checkbox.prop('checked', true);
            setEmployeeEnabled(employee, true);
        }

        bootstrap.Collapse.getOrCreateInstance(
            employee.find('.accordion-collapse').get(0)
        ).show();

        $('#cari_pegawai').val('').trigger('focus');
        updateEmployeeCount();
    }

    function toggleSchedule() {
        const scheduled = $('#jenis_lembur').val() === 'TERENCANA';
        $('.pegawai-item').each(function () {
            const row = $(this);
            const enabled = row.find('.pegawai-checkbox').is(':checked');
            row.find('.jam-mulai, .jam-selesai').prop('required', enabled && scheduled);
        });
        $('.required-schedule').toggleClass('d-none', !scheduled);
    }

    $('#jenis_lembur').on('change', toggleSchedule);
    $('#accordionPegawai').on('change', '.pegawai-checkbox', function () {
        const row = $(this).closest('.pegawai-item');
        setEmployeeEnabled(row, this.checked);
        if (this.checked) {
            row.removeClass('d-none');
            bootstrap.Collapse.getOrCreateInstance(row.find('.accordion-collapse').get(0)).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(row.find('.accordion-collapse').get(0)).hide();
            row.addClass('d-none');
        }
        updateEmployeeCount();
    });
    $('#accordionPegawai').on('click', '.pegawai-checkbox', function (event) {
        event.stopPropagation();
    });
    $('#accordionPegawai').on('shown.bs.collapse hidden.bs.collapse', '.accordion-collapse', function () {
        $(this).prev('.accordion-header').find('.accordion-button')
            .toggleClass('collapsed', !$(this).hasClass('show'));
    });
    $('#tambah_pegawai').on('click', addSearchedEmployee);
    $('#cari_pegawai').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addSearchedEmployee();
        }
    }).on('input', function () {
        $('#pegawai_add_error').text('');
    });
    $('.pegawai-item').each(function () {
        const row = $(this);
        const selected = row.find('.pegawai-checkbox').is(':checked');
        setEmployeeEnabled(row, selected);
        if (selected) {
            bootstrap.Collapse.getOrCreateInstance(row.find('.accordion-collapse').get(0)).show();
        }
    });

    toggleSchedule();
    updateEmployeeCount();
});
</script>
@endpush
