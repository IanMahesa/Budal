@extends('partials.all')

@section('title','Rekap Durasi Pegawai')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header custom-header">
            <h5 class="mb-0">
                <i class="fa fa-chart-bar"></i>
                Rekap Durasi Pegawai
            </h5>
        </div>

        <div class="card-body">
            <form id="formFilter">
                @csrf

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold">
                            Periode
                        </label>
                        <br>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="periode" value="harian" checked>
                            <label class="form-check-label">
                                Harian
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="periode" value="bulanan">
                            <label class="form-check-label">
                                Bulanan
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="periode" value="tahunan">
                            <label class="form-check-label">
                                Tahunan
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="periode" value="range">
                            <label class="form-check-label">
                                Rentang Tanggal
                            </label>
                        </div>
                    </div>
                </div>

                <div class="table-divider mb-2"></div>

                <div class="row">
                    <div class="col-md-3" id="tanggal_div">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-2 d-none" id="bulan_div">
                        <label>Bulan</label>
                        <select name="bulan" id="bulan" class="form-control">
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}" {{ date('n')==$i?'selected':'' }}>
                                    {{ DateTime::createFromFormat('!m',$i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2 d-none" id="tahun_div">
                        <label>Tahun</label>
                        <select name="tahun" id="tahun" class="form-control">
                            @for($i=date('Y');$i>=2024;$i--)
                                <option value="{{ $i }}">
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3 d-none" id="awal_div">
                        <label>Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control">
                    </div>

                    <div class="col-md-3 d-none" id="akhir_div">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control">
                    </div>
                </div>
                <div class="table-divider mt-3 mb-2"></div>

                <div class="row">
                    <div class="col-md-4">
                        <label>Bagian</label>
                        <select name="id_bag" id="id_bag" class="form-control">
                            <option value="">
                                Semua Bagian
                            </option>
                            @foreach($bagian as $b)
                                <option value="{{ $b->id_bag }}">
                                    {{ $b->bag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Sub Bagian</label>
                        <select name="id_subag" id="id_subag" class="form-control">
                            <option value="">
                                Semua Sub Bagian
                            </option>
                            @foreach($subbag as $s)
                                <option value="{{ $s->id_subag }}">
                                    {{ $s->sub_bag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Pegawai</label>
                        <select
                            name="id_peg" id="id_peg" class="form-control">
                            <option value="">
                                Semua Pegawai
                            </option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id_peg }}">
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-divider mt-3 mb-3"></div>

                <div class="text-center">
                    <button type="button" id="btnCari" class="btn btn-scanqr">
                        <i class="fa fa-search"></i>
                        Tampilkan
                    </button>

                    <button
                        type="button" id="btnReset" class="btn btn-back">
                        <i class="fa fa-sync"></i>
                        Reset
                    </button>

                    @can('rekap-manage')
                    <a href="#" id="btnExcel" class="btn btn-download">
                        <i class="fa fa-file-excel"></i>
                        Export
                    </a>
                    @endcan

                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header custom-header">
            <h6 class="mb-0">
                Hasil Rekap
            </h6>
        </div>

        <div class="card-body table-responsive">
            <table id="tabelRekap" class="table table-striped table-hover mb-0" width="100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="7%" class="text-center">NIK</th>
                        <th width="10%">Nama</th>
                        <th width="12%">Bagian</th>
                        <th width="15%">Sub Bagian</th>
                        <th width="10%" class="text-center">Jml Ijin Pribadi</th>
                        <th width="8%" class="text-center">Durasi</th>
                        <th width="10%" class="text-center">Jml Ijin Dinas</th>
                        <th width="8%" class="text-center">Durasi</th>
                        <th width="10%" class="text-center">Total Durasi</th>
                    </tr>
                </thead>

                <tbody>

                </tbody>

                 <tfoot class="table-secondary">
                    <tr>
                        <th colspan="5" class="text-end">
                            TOTAL
                        </th>
                        <th id="tfootPribadi">0</th>
                        <th id="tfootDurasiPribadi">0</th>
                        <th id="tfootDinas">0</th>
                        <th id="tfootDurasiDinas">0</th>
                        <th id="tfootTotal">0</th>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

$(function () {

    // TAMPILKAN FILTER SESUAI PERIODE //
    function tampilFilter(){
        let periode = $('input[name=periode]:checked').val();

        $('#tanggal_div').addClass('d-none');
        $('#bulan_div').addClass('d-none');
        $('#tahun_div').addClass('d-none');
        $('#awal_div').addClass('d-none');
        $('#akhir_div').addClass('d-none');

        if(periode=='harian'){
            $('#tanggal_div').removeClass('d-none');
        }

        if(periode=='bulanan'){
            $('#bulan_div').removeClass('d-none');
            $('#tahun_div').removeClass('d-none');
        }

        if(periode=='tahunan'){
            $('#tahun_div').removeClass('d-none');
        }

        if(periode=='range'){
            $('#awal_div').removeClass('d-none');
            $('#akhir_div').removeClass('d-none');
        }
    }

    tampilFilter();
    $('input[name=periode]').change(function(){
        tampilFilter();
    });

    function updateSubBagOptions(selectedBagian = '') {
        let subBagSelect = $('#id_subag');
        let currentValue = subBagSelect.val();

        subBagSelect.empty();
        subBagSelect.append('<option value="">Semua Sub Bagian</option>');
        subBagSelect.prop('disabled', true);

        let url = "{{ route('subbag.byBagian') }}";
        if (selectedBagian) {
            url += '/' + selectedBagian;
        }

        $.getJSON(url, function(data){
            data.forEach(function(item){
                subBagSelect.append(new Option(item.sub_bag, item.id_subag));
            });

            if (currentValue && data.some(function(item){ return item.id_subag == currentValue; })) {
                subBagSelect.val(currentValue);
            } else {
                subBagSelect.val('');
            }

            subBagSelect.prop('disabled', false);
            updatePegawaiOptions(subBagSelect.val());
        });
    }

    function updatePegawaiOptions(selectedSubag = '') {
        let pegawaiSelect = $('#id_peg');
        let currentValue = pegawaiSelect.val();

        pegawaiSelect.empty();
        pegawaiSelect.append('<option value="">Semua Pegawai</option>');
        pegawaiSelect.prop('disabled', true);

        let params = {};
        if (selectedSubag) {
            params.id_subag = selectedSubag;
        } else if ($('#id_bag').val()) {
            params.id_bag = $('#id_bag').val();
        }

        $.getJSON('{{ route("rekap.pegawai") }}', params, function(data){
            data.forEach(function(item){
                pegawaiSelect.append(new Option(item.nama, item.id_peg));
            });

            if (currentValue && data.some(function(item){ return item.id_peg == currentValue; })) {
                pegawaiSelect.val(currentValue);
            } else {
                pegawaiSelect.val('');
            }

            pegawaiSelect.prop('disabled', false);
        });
    }

    updateSubBagOptions();
    $('#id_bag').change(function(){
        updateSubBagOptions($(this).val());
    });

    $('#id_subag').change(function(){
        updatePegawaiOptions($(this).val());
    });

    // DATATABLE //
    let csrfToken = $('input[name=_token]').val() || "{{ csrf_token() }}";
    let filterApplied = true;
    let tableElement = $('#tabelRekap');

    let table = $.fn.DataTable.isDataTable(tableElement[0])
        ? tableElement.DataTable()
        : tableElement.DataTable({

        processing:true,
        paging:true,
        pageLength:10,
        lengthMenu:[
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Semua"]
        ],
        searching:false,
        ordering:false,
        info:true,
        language:{
            info:"Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty:"Tidak ada data",
            emptyTable:"Tidak ada data",
            zeroRecords:"Data tidak ditemukan",
            lengthMenu:"Tampilkan _MENU_ data",
            paginate:{
                first:"Awal",
                last:"Akhir",
                next:"Berikutnya",
                previous:"Sebelumnya"
            }
        },

        ajax:{
            url:"{{ route('rekap.data') }}",
            type:"POST",
            dataType:"json",
            cache:false,
            headers:{
                'X-CSRF-TOKEN': csrfToken
            },
            data:function(d){
                d._token = csrfToken;
                d.filter_applied = filterApplied;
                d.periode = $('input[name=periode]:checked').val();
                d.tanggal = $('#tanggal').val();
                d.bulan = $('#bulan').val();
                d.tahun = $('#tahun').val();
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
                d.id_bag = $('#id_bag').val();
                d.id_subag = $('#id_subag').val();
                d.id_peg = $('#id_peg').val();
            },
            dataSrc:''
        },

        error:function(xhr){
            console.error('Rekap AJAX error:', xhr.responseText);
            tableElement.find('tbody').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data rekap. Silakan cek log server.</td></tr>');
        },

        columns:[
            {
                data:null,
                render:function(data,type,row,meta){
                    return meta.row+1;
                }
            },

            {data:'nik'},
            {data:'nama'},
            {data:'bag'},
            {data:'sub_bag'},
            {data:'jml_pribadi'},
            {
                data:'durasi_pribadi',
                render:function(data){
                    return (data ?? 0)+' Menit';
                }
            },
            {data:'jml_dinas'},
            {
                data:'durasi_dinas',
                render:function(data){
                    return (data ?? 0)+' Menit';
                }
            },
            {
                data:'total_durasi',
                render:function(data){
                    return '<b>'+(data ?? 0)+' Menit</b>';
                }
            }
        ],

        footerCallback:function(row, data, start, end, display){
            let pegawai = data.length;
            let jp=0;
            let dp=0;
            let jd=0;
            let dd=0;
            let total=0;

            data.forEach(function(item){
                jp+=parseInt(item.jml_pribadi??0);
                dp+=parseInt(item.durasi_pribadi??0);

                jd+=parseInt(item.jml_dinas??0);
                dd+=parseInt(item.durasi_dinas??0);

                total+=parseInt(item.total_durasi??0);
            });

            $('#tfootPribadi').text(jp);
            $('#tfootDurasiPribadi').text(dp);
            $('#tfootDinas').text(jd);
            $('#tfootDurasiDinas').text(dd);
            $('#tfootTotal').text(total);
        }
    });

    // TOMBOL TAMPILKAN //
    $('#btnCari').click(function(){
        filterApplied = true;
        table.ajax.reload();
    });

    // RESET FILTER //
    $('#btnReset').click(function(e){
        e.preventDefault();
        filterApplied = true;
        $('#formFilter')[0].reset();
        tampilFilter();
        updateSubBagOptions();
        updatePegawaiOptions();
        table.ajax.reload();
    });

    // EXPORT EXCEL //
    $('#btnExcel').click(function(e){
        e.preventDefault();
        let url="{{ route('rekap.excel') }}?";
        url += "filter_applied="+(filterApplied ? 1 : 0);
        url += "&periode="+$('input[name=periode]:checked').val();
        url += "&tanggal="+$('#tanggal').val();
        url += "&bulan="+$('#bulan').val();
        url += "&tahun="+$('#tahun').val();
        url += "&tanggal_awal="+$('#tanggal_awal').val();
        url += "&tanggal_akhir="+$('#tanggal_akhir').val();
        url += "&id_bag="+$('#id_bag').val();
        url += "&id_subag="+$('#id_subag').val();
        url += "&id_peg="+$('#id_peg').val();
        window.location=url;
    });

});
</script>

@endpush