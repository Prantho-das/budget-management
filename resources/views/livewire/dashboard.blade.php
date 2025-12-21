<div>

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Dashboard') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Menu') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Dashboard') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    @if($fiscalYear)
    <div class="row">
        <div class="col-xl-4">
            <div class="card overflow-hidden">
                <div class="bg-primary bg-soft">
                    <div class="row">
                        <div class="col-7">
                            <div class="text-primary p-3">
                                <h5 class="text-primary">{{ __('Welcome Back !') }}</h5>
                                <p>{{ config('app.name') }} {{ __('Dashboard') }}</p>
                            </div>
                        </div>
                        <div class="col-5 align-self-end">
                            <img src="{{ asset('assets/images/profile-img.png') }}" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="avatar-md profile-user-wid mb-4">
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="img-thumbnail rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                            </div>
                            <h5 class="font-size-15 text-truncate">{{ Auth::user()->name }}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ Auth::user()->roles->first()->name ?? 'User' }}</p>
                        </div>

                        <div class="col-sm-8">
                            <div class="pt-4">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="font-size-15">{{ $pendingApprovals }}</h5>
                                        <p class="text-muted mb-0">{{ __('Pending Request') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="font-size-15">{{ number_format($budgetRemaining / 100000, 2) }} L</h5>
                                        <p class="text-muted mb-0">{{ __('Remaining') }}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('profile.edit') }}" class="btn btn-primary waves-effect waves-light btn-sm">{{ __('View Profile') }} <i class="mdi mdi-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('Monthly Expense Trend') }}</h4>
                    <div id="expense-trend-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">{{ __('Total Estimated') }}</p>
                                    <h4 class="mb-0">{{ number_format($totalEstimated) }}</h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                        <span class="avatar-title">
                                            <i class="bx bx-copy-alt font-size-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">{{ __('Total Allocated') }}</p>
                                    <h4 class="mb-0">{{ number_format($totalAllocated) }}</h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                        <span class="avatar-title">
                                            <i class="bx bx-archive-in font-size-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">{{ __('Total Expense') }}</p>
                                    <h4 class="mb-0">{{ number_format($totalExpense) }}</h4>
                                </div>

                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-sm-flex flex-wrap">
                        <h4 class="card-title mb-4">{{ __('Budget Overview') }}</h4>
                        <div class="ms-auto">
                            <ul class="nav nav-pills"> 
                                <li class="nav-item"> 
                                    <a class="nav-link active" href="#">{{ $fiscalYear->name }}</a> 
                                </li> 
                            </ul>
                        </div>
                    </div>
                    
                    <div id="budget-overview-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('Top Offices by Allocation') }}</h4>

                    <div id="office-breakdown-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('Recent Activities') }}</h4>
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle">{{ __('Date') }}</th>
                                    <th class="align-middle">{{ __('Office') }}</th>
                                    <th class="align-middle">{{ __('Budget Type') }}</th>
                                    <th class="align-middle">{{ __('Status') }}</th>
                                    <th class="align-middle">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->updated_at->format('d M, Y') }}</td>
                                    <td>{{ $activity->office->name ?? 'N/A' }}</td>
                                    <td>{{ $activity->budgetType->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-pill badge-soft-{{ $activity->current_stage == 'Released' ? 'success' : 'primary' }} font-size-11">
                                            {{ ucfirst($activity->current_stage) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ number_format($activity->amount_approved > 0 ? $activity->amount_approved : $activity->amount_demand) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                {{ __('No Active Fiscal Year Found. Please configure a fiscal year to view the dashboard.') }}
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Budget Overview Chart
            var budgetOptions = {
                chart: {
                    height: 350,
                    type: 'bar',
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                series: @json($budgetOverview['series'] ?? []),
                colors: ['#34c38f', '#556ee6', '#f46a6a'],
                xaxis: {
                    categories: @json($budgetOverview['categories'] ?? []),
                },
                yaxis: {
                    title: { text: 'Amount (BDT)' }
                },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " BDT"
                        }
                    }
                }
            };
            var budgetChart = new ApexCharts(document.querySelector("#budget-overview-chart"), budgetOptions);
            budgetChart.render();

            // Expense Trend Chart
            var expenseOptions = {
                chart: {
                    height: 320,
                    type: 'area', // Changed to area for better aesthetic in trend
                    toolbar: { show: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth' },
                series: @json($expenseTrends['series'] ?? []),
                colors: ['#556ee6'],
                xaxis: {
                    categories: @json($expenseTrends['categories'] ?? []),
                },
            };
            var expenseChart = new ApexCharts(document.querySelector("#expense-trend-chart"), expenseOptions);
            expenseChart.render();

            // Office Breakdown Chart - Donut
            var officeOptions = {
                series: @json($officeBreakdown['series'] ?? []),
                chart: {
                    type: 'donut',
                    height: 250,
                },
                labels: @json($officeBreakdown['labels'] ?? []),
                colors: ['#34c38f', '#556ee6', '#f46a6a', '#50a5f1', '#f1b44c'],
                legend: {
                    show: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                }
            };
            var officeChart = new ApexCharts(document.querySelector("#office-breakdown-chart"), officeOptions);
            officeChart.render();
        });
    </script>
    @endpush
</div>
