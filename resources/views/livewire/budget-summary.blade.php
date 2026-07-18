<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('My Budget Summary') }}</h4>
                <div class="page-title-right d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> {{ __('Print Allocation Report') }}
                    </button>
                    <ol class="breadcrumb m-0 align-self-center">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Summary') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Fiscal Year Selector -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">{{ __('Fiscal Year') }}</label>
            <select wire:model.live="fiscal_year_id" class="form-select">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->bn_name }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->can('view-all-offices-data'))
            <div class="col-md-4">
                <label class="form-label fw-semibold">{{ __('Office') }}</label>
                <select wire:model.live="rpo_unit_id" class="form-select">
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <!-- Budget Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Draft Budgets') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalDraft }}">{{ bn_num($totalDraft) }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-secondary mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-secondary">
                                    <i class="bx bx-file font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Submitted') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalSubmitted }}">{{ bn_num($totalSubmitted) }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-primary mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="bx bx-send font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Released') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalReleased }}">{{ bn_num($totalReleased) }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-success mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="bx bx-check-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Rejected') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalRejected }}">{{ bn_num($totalRejected) }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-danger mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-danger">
                                    <i class="bx bx-x-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Total Allocated') }}</h5>
                    <div class="text-center">
                        <h2 class="text-primary mb-0">{{ bn_comma_format($totalAllocated, 2) }}</h2>
                        <p class="text-muted">{{ __('Total Budget Released') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Total Expenses') }}</h5>
                    <div class="text-center">
                        <h2 class="text-warning mb-0">{{ bn_comma_format($totalExpenses, 2) }}</h2>
                        <p class="text-muted">{{ __('Total Amount Spent') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Available Balance') }}</h5>
                    <div class="text-center">
                        <h2 class="text-success mb-0">{{ bn_comma_format($availableBalance, 2) }}</h2>
                        <p class="text-muted">{{ __('Remaining Budget') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget by Economic Code -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Budget Breakdown by Economic Code') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Allocated') }}</th>
                                    <th class="text-end">{{ __('Spent') }}</th>
                                    <th class="text-end">{{ __('Balance') }}</th>
                                    <th class="text-center">{{ __('Utilization') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetByCode as $item)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ bn_num($item['code']) }}</span></td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-end fw-semibold">{{ bn_comma_format($item['allocated'], 2) }}</td>
                                        <td class="text-end text-warning">{{ bn_comma_format($item['spent'], 2) }}</td>
                                        <td class="text-end text-success">{{ bn_comma_format($item['balance'], 2) }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $item['utilization'] > 90 ? 'bg-danger' : ($item['utilization'] > 70 ? 'bg-warning' : 'bg-success') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($item['utilization'], 100) }}%">
                                                    {{ bn_num(number_format($item['utilization'], 1)) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('No budget allocations found for this fiscal year.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- START OF FORMAL PRINT REPORT LAYOUT -->
    <style>
        /* Screen styles: Hide print layout on screen */
        #formal-print-report {
            display: none;
        }

        /* Print styles */
        @media print {
            /* Hide all regular dashboard/screen elements */
            .vertical-menu,
            .navbar-header,
            .footer,
            .page-title-box,
            .row,
            .alert,
            select,
            label,
            #page-topbar,
            .main-content {
                display: none !important;
            }

            /* Reset container widths and margins */
            body, html {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 13px !important;
                font-family: 'SolaimanLipi', 'Nikosh', sans-serif !important;
            }

            /* Make print container full width and visible */
            #formal-print-report {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
            }

            /* Table styling matching the PDF */
            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
            }

            .print-table th, .print-table td {
                border: 1px solid #000 !important;
                padding: 5px 8px !important;
                vertical-align: middle !important;
            }

            .print-table th {
                background-color: #f2f2f2 !important;
                font-weight: bold !important;
                text-align: center !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-center {
                text-align: center !important;
            }

            .text-end {
                text-align: right !important;
            }

            .fw-bold {
                font-weight: bold !important;
            }

            .mb-0 {
                margin-bottom: 0 !important;
            }

            .mt-1 {
                margin-top: 4px !important;
            }

            .header-title-block {
                text-align: center !important;
                margin-bottom: 25px !important;
                line-height: 1.5 !important;
            }

            .header-title-block h3 {
                font-size: 18px !important;
                font-weight: bold !important;
                margin: 0 0 5px 0 !important;
            }

            .header-title-block h4 {
                font-size: 16px !important;
                margin: 0 0 5px 0 !important;
                font-weight: normal !important;
            }

            .header-title-block p {
                margin: 0 !important;
                font-size: 14px !important;
            }
        }
    </style>

    <div id="formal-print-report">
        <div class="header-title-block">
            <h3>গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</h3>
            <h4>ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর</h4>
            <p>ই-৭, আগারগাঁও, ঢাকা</p>
            <p class="mt-1 fw-bold">অফিসের নাম: {{ $selectedOffice->name ?? '' }}@if(isset($selectedOffice->code)); অফিস কোড নং-{{ bn_num($selectedOffice->code) }}@endif</p>
            <p class="fw-bold">{{ bn_num($selectedFiscalYear->name ?? '') }} অর্থবছরের বাজেট বরাদ্দ (বরাদ্দ নং ১)</p>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 20%;">অর্থনৈতিক গ্রুপ/কোড</th>
                    <th style="width: 45%;">বিবরণ</th>
                    <th style="width: 17%; text-align: right;">পরিমাণ (হাজার টাকায়)</th>
                    <th style="width: 18%; text-align: right;">মোট (হাজার টাকায়)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotalInThousands = 0;
                @endphp
                @forelse($groupedAllocations as $groupCode => $group)
                    @php
                        $subtotalInThousands = $group['subtotal'] / 1000;
                        $grandTotalInThousands += $subtotalInThousands;
                    @endphp
                    <!-- Group Header Row -->
                    <tr class="fw-bold">
                        <td>{{ bn_num($group['group_code']) }}</td>
                        <td>{{ $group['group_name'] }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    
                    <!-- Child Items Rows -->
                    @foreach($group['items'] as $item)
                        @php
                            $amountInThousands = $item['amount'] / 1000;
                        @endphp
                        <tr>
                            <td style="padding-left: 20px;">{{ bn_num($item['code']) }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-end">{{ $amountInThousands > 0 ? bn_comma_format($amountInThousands, 0) : '০' }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    <!-- Subtotal Row -->
                    <tr class="fw-bold">
                        <td></td>
                        <td class="text-end">উপমোট</td>
                        <td></td>
                        <td class="text-end">{{ $subtotalInThousands > 0 ? bn_comma_format($subtotalInThousands, 0) : '০' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">কোনো বাজেট বরাদ্দ পাওয়া যায়নি।</td>
                    </tr>
                @endforelse

                <!-- Grand Total Row -->
                @if(count($groupedAllocations) > 0)
                    <tr class="fw-bold">
                        <td class="text-center">সর্বমোট</td>
                        <td></td>
                        <td class="text-end">{{ bn_comma_format($grandTotalInThousands, 0) }}</td>
                        <td class="text-end">{{ bn_comma_format($grandTotalInThousands, 0) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <!-- END OF FORMAL PRINT REPORT LAYOUT -->
</div>
