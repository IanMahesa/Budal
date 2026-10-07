@extends('partials.all')

@section('title','Notifikasi Ijin')

@section('content')

<div class="card tabel-card">
<div class="card-body table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3 ms-3 me-3">
        <h4>Data Notifikasi Ijin</h4>
    </div>


    <div class="card shadow mb-3">
        <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                Daftar Notifikasi Ijin
            </h6>
            
            <button type="button"
                    id="btnReadAll"
                    class="btn btn-tambah">
                <i class="fas fa-tasks"></i>
                Read All
            </button>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="tabelUser"
                       class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox"
                                       id="checkAll"
                                       class="form-check-input">
                            </th>
                            <th class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Sub Bagian</th>
                            <th>Ijin</th>
                            <th class="text-center">Jenis</th>
                            <th class="text-center">Jam Keluar</th>
                            <th class="text-center">Jam Masuk</th>
                            <th class="text-center">Status</th>
                            <th>Pengawas</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($notif as $key => $item)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox"
                                           class="form-check-input check-notif"
                                           value="{{ $item->id_transaksi }}">
                                </td>
                                <td class="text-center">
                                    {{ $key + 1 }}
                                </td>
                                <td>
                                    {{ optional($item->tanggal_keluar)->format('d-m-Y') ?? '-' }}
                                </td>
                                <td>
                                    {{ optional($item->pegawai)->nama ?? '-' }}
                                </td>
                                <td>
                                    {{ optional($item->subbag)->sub_bag ?? '-' }}
                                </td>
                                <td>
                                    {{ optional($item->perijinan)->izin ?? '-' }}
                                </td>
                                <td class="text-center">
                                    {{ optional($item->perijinan)->jenis ?? '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($item->status == 'Keluar')
                                        <span class="badge bg-danger">
                                            Keluar
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Kembali
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ optional($item->user)->name ?? '-' }}
                                </td>
                                {{-- Aksi --}}
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-success btn-read"
                                            data-id="{{ $item->id_transaksi }}"
                                            title="Read">
                                        <i class="fas fa-check"></i>
                                        Read
                                    </button>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="12"
                                    class="text-center">
                                    Data Notifikasi ijin belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>

$(document).ready(function () {

    $('#checkAll').on('change', function () {
        $('.check-notif').prop(
            'checked',
            $(this).prop('checked')
        );
    });

    $(document).on('change', '.check-notif', function () {
        let total = $('.check-notif').length;
        let checked = $('.check-notif:checked').length;
        $('#checkAll').prop(
            'checked',
            total > 0 && total === checked
        );
    });

    $(document).on('click', '.btn-read', function () {
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('notifikasi.read') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function (response) {
                location.reload();
            },
            error: function (xhr) {
                alert('Notifikasi gagal dibaca.');
                console.log(xhr.responseText);
            }
        });
    });

    $('#btnReadAll').on('click', function () {
        let ids = [];
        $('.check-notif:checked').each(function () {
            ids.push($(this).val());
        });
        if (ids.length === 0) {
            alert('Silakan pilih notifikasi terlebih dahulu.');
            return;
        }

        $.ajax({
            url: "{{ route('notifikasi.readAll') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ids: ids
            },
            success: function (response) {
                location.reload();
            },
            error: function (xhr) {
                alert('Notifikasi gagal dibaca.');
                console.log(xhr.responseText);
            }
        });
    });
});

</script>

@endpush
