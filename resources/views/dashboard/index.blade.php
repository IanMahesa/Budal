@extends('partials.all')

@section('title','Dashboard')

@section('content')

<section class="p-2 rounded mb-3 ms-md-5"
    style="background-color: transparent; margin-top: -10px;">
    <h1 class="fw-semibold text-center text-md-start welcome-title text-3d">
        Selamat Datang, {{ Auth::user()->name }}
    </h1>
</section>

<div class="table-divider mb-3"></div>  

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt text-primary"></i>
            Dashboard Monitoring Izin Keluar Pegawai
        </h3>

        <span class="badge bg-primary fs-6">
            {{ date('d F Y') }}
        </span>
    </div>  

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Pegawai
                            </div>
                            <div class="h2 font-weight-bold text-gray-800">
                                {{ $pegawai ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-3x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ijin Pribadi
                            </div>
                            <div class="h2 font-weight-bold text-gray-800">
                                {{ $pribadi ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-3x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ijin Dinas
                            </div>
                            <div class="h2 font-weight-bold text-gray-800">
                                {{ $dinas ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-3x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Sedang Keluar
                            </div>
                            <div class="h2 font-weight-bold text-gray-800">
                                {{ $keluar ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-out-alt fa-3x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
    
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    Grafik Ijin Keluar Bulanan
                </div>

                <div class="card-body">
                    <div style="height:350px">
                        <canvas id="chartBulanan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    Jenis Ijin
                </div>

                <div class="card-body">
                    <div style="height:350px">
                        <canvas id="chartPie"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    // PIE CHART // 
    new Chart(document.getElementById('chartPie'), {
        type: 'pie',
        data: {
            labels: ['Ijin Pribadi', 'Ijin Dinas'],
            datasets: [{
                data: [
                    {{ $pribadi ?? 0 }},
                    {{ $dinas ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107'
                ]
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }

    });

    // BAR CHART //
    new Chart(document.getElementById('chartBulanan'), {
        type: 'bar',
        data: {
            labels: [
                'Jan','Feb','Mar','Apr','Mei','Jun',
                'Jul','Ags','Sep','Okt','Nov','Des'
            ],

            datasets: [
                {
                    label: 'Ijin Pribadi',
                    data: @json($grafikPribadi),
                    backgroundColor: '#28a745',
                    borderRadius: 6
                },

                {
                    label: 'Ijin Dinas',
                    data: @json($grafikDinas),
                    backgroundColor: '#ffc107',
                    borderRadius: 6
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});

</script>

@endpush