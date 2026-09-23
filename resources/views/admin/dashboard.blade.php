@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/css/pages/app-logistics-dashboard.css') }}" />

    <style>
        body {
            font-family: "Public Sans", sans-serif !important;
        }

        .layout-navbar-fixed body:not(.modal-open) .layout-content-navbar .layout-navbar,
        .layout-menu-fixed body:not(.modal-open) .layout-content-navbar .layout-navbar,
        .layout-menu-fixed-offcanvas body:not(.modal-open) .layout-content-navbar .layout-navbar {
            z-index: 1043;
        }

        .layout-navbar-fixed body:not(.modal-open) .layout-content-navbar .layout-menu,
        .layout-menu-fixed body:not(.modal-open) .layout-content-navbar .layout-menu,
        .layout-menu-fixed-offcanvas body:not(.modal-open) .layout-content-navbar .layout-menu {
            z-index: 1043;
        }

        i {
            margin: 0 5px 0 5px;
        }

        textarea {
            height: 100px;
        }
    </style>
@endsection

@section('content')
    @php
        $bookingsThisMonth = \App\Models\Booking::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();
        $totalBookings = \App\Models\Booking::count();
        $cancelledBookings = \App\Models\Booking::where('status', 'cancelled')->count();

        $totalClients = \App\Models\Client::count();
        $clientsThisMonth = \App\Models\Client::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        $totalAdmins = \App\Models\Admin::count();

        $totalVisitors = \App\Models\Visitor::count();
        $visitorsThisMonth = \App\Models\Visitor::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        $totalPackages = \App\Models\Package::count();
        $packagesThisMonth = \App\Models\Package::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        // $totalDestinations = \App\Models\Destination::count();
        $totalInquiries = \App\Models\Inquiry::count();

        $totalRevenue = class_exists(\App\Models\Payment::class)
            ? \App\Models\Payment::where('status', 'paid')->sum('amount')
            : 0;

        $platformProfit = $totalRevenue * 0.2;

        $walletCount = class_exists(\App\Models\Payment::class)
            ? \App\Models\Payment::where('payment_type', 'wallet')->count()
            : 0;

        $cashCount = class_exists(\App\Models\Payment::class)
            ? \App\Models\Payment::where('payment_type', 'cash')->count()
            : 0;

        $cardCount = class_exists(\App\Models\Payment::class)
            ? \App\Models\Payment::where('payment_type', 'card')->count()
            : 0;

        $paymentTotal = max($walletCount + $cashCount + $cardCount, 1);

        $walletPercent = round(($walletCount / $paymentTotal) * 100, 2);
        $cashPercent = round(($cashCount / $paymentTotal) * 100, 2);
        $cardPercent = round(($cardCount / $paymentTotal) * 100, 2);

        $topClients = \App\Models\Client::withCount('bookings')->orderByDesc('bookings_count')->take(5)->get();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-xl-12 mb-4 col-lg-5 col-12">
                {{ greeting() }} {{ auth()->guard('admin')->user()->name }} 😍
            </div>

            <div class="row mb-4 g-4">
                <div class="col-lg-8">
                    <div class="row">

                        {{-- Bookings this month --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="ti ti-calendar-event ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $bookingsThisMonth }}</h4>
                                    </div>
                                    <p class="mb-1">Bookings This Month</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Bookings --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-calendar-stats ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalBookings }}</h4>
                                    </div>
                                    <p class="mb-1">Total Bookings</p>
                                </div>
                            </div>
                        </div>

                        {{-- Cancelled Bookings --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-danger">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-danger">
                                                <i class="ti ti-calendar-x ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $cancelledBookings }}</h4>
                                    </div>
                                    <p class="mb-1">Cancelled Bookings</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Clients --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-secondary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-secondary">
                                                <i class="ti ti-users ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalClients }}</h4>
                                    </div>
                                    <p class="mb-1">Total Clients</p>
                                </div>
                            </div>
                        </div>

                        {{-- Clients This Month --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-dark">
                                                <i class="ti ti-user-plus ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $clientsThisMonth }}</h4>
                                    </div>
                                    <p class="mb-1">Clients This Month</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Admins --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="ti ti-user-cog ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalAdmins }}</h4>
                                    </div>
                                    <p class="mb-1">Total Staff</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Visits --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="ti ti-eye ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalVisitors }}</h4>
                                    </div>
                                    <p class="mb-1">Total Visits</p>
                                </div>
                            </div>
                        </div>

                        {{-- Visits This Month --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-eye-check ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $visitorsThisMonth }}</h4>
                                    </div>
                                    <p class="mb-1">Visits This Month</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Packages --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="ti ti-plane-departure ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalPackages }}</h4>
                                    </div>
                                    <p class="mb-1">Total Packages</p>
                                </div>
                            </div>
                        </div>

                        {{-- Packages Added This Month --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="ti ti-plane-inflight ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $packagesThisMonth }}</h4>
                                    </div>
                                    <p class="mb-1">Packages Added This Month</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Revenue --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-currency-dollar ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ number_format($totalRevenue, 2) }}</h4>
                                    </div>
                                    <p class="mb-1">Total Revenue</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Inquiries --}}
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card card-border-shadow-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 pb-1">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="ti ti-message ti-md"></i>
                                            </span>
                                        </div>
                                        <h4 class="ms-1 mb-0">{{ $totalInquiries }}</h4>
                                    </div>
                                    <p class="mb-1">Total Inquiries</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-xxl-4 mb-4 order-5 order-xxl-0">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">{{ number_format($platformProfit, 2) }}</h5>
                            <small class="text-muted">Platform Earnings (Last Month)</small>
                        </div>
                        <div class="card-body">
                            <div id="expensesChart"></div>
                            <div class="mt-md-2 text-center mt-lg-3 mt-3">
                                <small class="text-muted mt-3">Summary of platform earnings from confirmed payments</small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title mb-0">
                                <h5 class="m-0">Payment Methods</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-none d-lg-flex vehicles-progress-labels mb-4">
                                <div class="vehicles-progress-label on-the-way-text" style="width: 100%">
                                    Wallet</div>
                                <div class="vehicles-progress-label unloading-text" style="width: 100%">
                                    Cash</div>
                                <div class="vehicles-progress-label loading-text" style="width: 100%">
                                    Credit Card</div>
                            </div>

                            <div class="vehicles-overview-progress progress rounded-2 my-4" style="height: 46px">
                                <div class="progress-bar fw-medium text-start bg-body text-dark px-3 rounded-0"
                                    role="progressbar" style="width: {{ $walletPercent }}%"
                                    aria-valuenow="{{ $walletPercent }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $walletPercent }}%
                                </div>
                                <div class="progress-bar fw-medium text-start bg-primary px-3" role="progressbar"
                                    style="width: {{ $cashPercent }}%" aria-valuenow="{{ $cashPercent }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    {{ $cashPercent }}%
                                </div>
                                <div class="progress-bar fw-medium text-start text-bg-info px-2 rounded-0 px-lg-2 px-xxl-3"
                                    role="progressbar" style="width: {{ $cardPercent }}%"
                                    aria-valuenow="{{ $cardPercent }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $cardPercent }}%
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table card-table">
                                    <tbody class="table-border-bottom-0">
                                        <tr>
                                            <td class="w-50 ps-0">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <div class="me-2">
                                                        <i class="ti ti-wallet mt-n1"></i>
                                                    </div>
                                                    <h6 class="mb-0 fw-normal">Wallet</h6>
                                                </div>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="fw-medium">{{ $walletPercent }}%</span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="w-50 ps-0">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <div class="me-2">
                                                        <i class="ti ti-cash mt-n1"></i>
                                                    </div>
                                                    <h6 class="mb-0 fw-normal">Cash</h6>
                                                </div>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="fw-medium">{{ $cashPercent }}%</span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="w-50 ps-0">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <div class="me-2">
                                                        <i class="ti ti-credit-card mt-n1"></i>
                                                    </div>
                                                    <h6 class="mb-0 fw-normal">Credit Card</h6>
                                                </div>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="fw-medium">{{ $cardPercent }}%</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12 col-xl-6 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 id="visitors_data">Visit Statistics by Country</h4>

                            <div class="col-md-4 mb-4">
                                <select name="visitors_year" id="visitors_year" class="form-control select2">
                                    <option value="">Select Year</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="visitorsAreaChart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6 col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="card-title mb-0">
                                <h5 class="m-0 me-2">Top Booking Clients</h5>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless border-top">
                                <thead class="border-bottom">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-end">Number of Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topClients as $client)
                                        <tr>
                                            <td class="pt-2">
                                                <div class="d-flex justify-content-start align-items-center mt-lg-4">
                                                    <div class="avatar me-3 avatar-sm">
                                                        <img src="{{ asset('dashboard/assets/img/avatars/1.png') }}"
                                                            alt="" class="rounded-circle" />
                                                    </div>

                                                    <span class="mb-0">
                                                        {{ trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: $client->email ?? 'No Name' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-end pt-2">
                                                <div class="user-progress mt-lg-4">
                                                    <p class="mb-0 fw-medium">{{ $client->bookings_count }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($topClients->isEmpty())
                                        <tr>
                                            <td colspan="2" class="text-center py-4">No data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 id="sales_data">Booking Statistics 2026</h4>

                            <div class="col-md-4 mb-4">
                                <select name="sales_year" id="year" class="form-control select2 year">
                                    <option value="">Select Year</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="lineAreaChart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 id="all_data">Performance Statistics for 2026</h4>

                            <div class="col-md-4 mb-4">
                                <select name="data_year" id="data_year" class="form-control select2 year">
                                    <option value="">Select Year</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="lineAreaChart1"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        function loadVisitorsChart(year = null) {
            $.ajax({
                url: "{{ route('admin.visitors.chart') }}",
                data: {
                    year: year
                },
                success: function(response) {
                    var chartEl = document.querySelector("#visitorsAreaChart");
                    chartEl.innerHTML = '';

                    var chartOptions = {
                        series: [{
                            name: 'Total Visits',
                            data: response.count ?? response.values ?? []
                        }],
                        chart: {
                            height: 350,
                            type: "area",
                            toolbar: false
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: "smooth",
                            width: 2
                        },
                        xaxis: {
                            categories: response.countries ?? response.labels ?? []
                        },
                        colors: ['#7367F0'],
                        fill: {
                            type: "gradient",
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.5,
                                opacityTo: 0.3,
                                stops: [0, 90, 100]
                            }
                        }
                    };

                    var chart = new ApexCharts(chartEl, chartOptions);
                    chart.render();
                }
            });
        }

        loadVisitorsChart();

        $('#visitors_year').on('change', function() {
            let year = $(this).val();
            $("#visitorsAreaChart").html('');
            loadVisitorsChart(year);
        });

        document.addEventListener('DOMContentLoaded', function() {
            function loadBookingsChart(year) {
                fetch(`{{ url('admin/bookings/stats') }}/${year}`)
                    .then(res => res.json())
                    .then(data => {
                        let months = data.map(i => i.month);
                        let totals = data.map(i => i.total);

                        let options = {
                            chart: {
                                height: 350,
                                type: 'area'
                            },
                            series: [{
                                name: 'Total Bookings',
                                data: totals
                            }],
                            xaxis: {
                                categories: months
                            }
                        };

                        let chartDiv = document.querySelector('#lineAreaChart');
                        chartDiv.innerHTML = "";
                        let chart = new ApexCharts(chartDiv, options);
                        chart.render();
                    });
            }

            function loadGeneralStatsChart(year) {
                fetch(`{{ url('admin/bookings/stats') }}/${year}`)
                    .then(res => res.json())
                    .then(data => {
                        let months = data.map(i => i.month);
                        let totals = data.map(i => i.total);

                        let options = {
                            chart: {
                                height: 350,
                                type: 'line'
                            },
                            series: [{
                                name: 'Overall Performance',
                                data: totals
                            }],
                            xaxis: {
                                categories: months
                            }
                        };

                        let chartDiv = document.querySelector('#lineAreaChart1');
                        chartDiv.innerHTML = "";
                        let chart = new ApexCharts(chartDiv, options);
                        chart.render();
                    });
            }

            document.querySelector('#year').addEventListener('change', function() {
                document.getElementById("sales_data").innerHTML = `Booking Statistics ${this.value}`;
                loadBookingsChart(this.value);
            });

            document.querySelector('#data_year').addEventListener('change', function() {
                document.getElementById("all_data").innerHTML = `Performance Statistics for ${this.value}`;
                loadGeneralStatsChart(this.value);
            });

            loadBookingsChart(2026);
            loadGeneralStatsChart(2026);

            var expensesChartEl = document.querySelector('#expensesChart');
            if (expensesChartEl) {
                var expensesChart = new ApexCharts(expensesChartEl, {
                    chart: {
                        type: 'donut',
                        height: 250
                    },
                    series: [
                        {{ round($platformProfit, 2) }},
                        {{ max(round($totalRevenue - $platformProfit, 2), 0) }}
                    ],
                    labels: ['Platform Earnings', 'Remaining Revenue'],
                    dataLabels: {
                        enabled: true
                    },
                    legend: {
                        position: 'bottom'
                    }
                });

                expensesChart.render();
            }
        });
    </script>
@endsection
