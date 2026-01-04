<div>
    <style>
        @media print {

            .vertical-menu,
            .navbar-header,
            .footer,
            .card-body.border-bottom,
            .btn,
            .breadcrumb,
            .filter-section {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .office-wise-budget-table .title-box-inner {
                font-size: 10px !important;
            }

            .office-wise-budget-table .card .table-title-box .title-box-inner .ministry,
            .office-wise-budget-table .card .table-title-box .title-box-inner .title {
                font-size: 10px !important;
            }

            .table td,
            table th {
                padding: 0.3rem !important;
                font-size: 10px !important;
            }
        }

        .table-sm td,
        .table-sm th {
            padding: 0.3rem !important;
            font-size: 13px;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Office-wise Budget Summary') }}</h4>

                <div class="page-title-right d-flex align-items-center">
                    <button type="button" class="btn btn-primary btn-sm me-2" onclick="window.print()">
                        <i class="bx bx-printer"></i> {{ __('Print Report') }}
                    </button>
                    <ol class="breadcrumb m-0 d-none d-sm-flex">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Office-wise') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row filter-section">
        <div class="col-lg-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 justify-content-end">
                        <div class="col-sm-12 col-md-3 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Fiscal Year') }}</label>
                                <select wire:model.lazy="fiscal_year_id" class="form-select">
                                    @foreach ($fiscalYears as $fy)
                                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Budget Type') }}</label>
                                <select wire:model.lazy="budget_type_id" class="form-select">
                                    @foreach ($budgetTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Economic Code') }} ({{ __('Optional') }})</label>
                                <select wire:model.lazy="economic_code_id" class="form-select">
                                    <option value="">{{ __('All Economic Codes (Summary)') }}</option>
                                    @foreach ($economicCodes as $ec)
                                        <option value="{{ $ec->id }}">{{ $ec->code }} - {{ $ec->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- new table as like sheet of HO start --}}

    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="unitoffice-entry-table office-wise-budget-table">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-title-box">
                            <div class="title-box-inner">
                                <div class="title">পরিচালন ব্যায়ের প্রাথমিক প্রাক্কলন ও প্রক্ষেপন সার-সংক্ষেপ</div>
                                <div class="ministry">
                                    <span>মন্ত্রণালয় / বিভাগ</span> : ১৬১ সুরক্ষা সেনা বিভাগ,স্বরাষ্ট্র মন্ত্রণালয়
                                </div>
                                <div class="ministry">
                                    <span>অধিদপ্তর</span> : ১৬১০৫ ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle custom-budget-table">
                                <thead>
                                    <tr class="table-primary text-center">
                                        <th rowspan="2">অফিসের নাম</th>
                                        <th rowspan="2">অফিস কোড</th>
                                        <th colspan="4">প্রকৃত ব্যায়</th>
                                        <th>বাজেট</th>
                                        <th>প্রস্তাবিত <br> সংশোধিত</th>
                                        <th>প্রাক্কলন</th>
                                        <th colspan="2">প্রক্ষেপন</th>
                                        <th rowspan="2">অতিরিক্ত <br> দাবি</th>
                                        <th rowspan="2">ব্যাখ্যামূলক <br> মন্তব্য</th>
                                    </tr>
                                    <tr>
                                        <th>2022-2023</th>
                                        <th>2023-24</th>
                                        <th>প্রথম ৬ মাস <br> 2023-24</th>
                                        <th>প্রথম ৬ মাস <br> 2024-25</th>
                                        <th>2024-25</th>
                                        <th>2024-25</th>
                                        <th>2025-26</th>
                                        <th>2026-27</th>
                                        <th>2027-28</th>
                                    </tr>
                                    <tr>
                                        <th>1</th>
                                        <th>2</th>
                                        <th>3</th>
                                        <th>4</th>
                                        <th></th>
                                        <th>5</th>
                                        <th>6</th>
                                        <th>7</th>
                                        <th>8</th>
                                        <th>9</th>
                                        <th>10</th>
                                        <th></th>
                                        <th>11</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totals = [
                                            'historical' => array_fill(0, count($prevYears), 0),
                                            'demand' => 0,
                                            'approved' => 0,
                                            'released' => 0,
                                        ];
                                    @endphp
                                    @foreach ($offices as $index => $office)
                                        @php
                                            $row = $officeWiseData[$office->id];
                                            $balance = $row['approved'] - $row['released'];

                                            // Update totals
                                            foreach ($prevYears as $i => $py) {
                                                $totals['historical'][$i] += $row['historical']["year_{$i}"] ?? 0;
                                            }
                                            $totals['demand'] += $row['demand'];
                                            $totals['approved'] += $row['approved'];
                                            $totals['released'] += $row['released'];
                                        @endphp
                                        <tr>

                                            <td>{{ $office->name }}</td>
                                            <td class="text-center">{{ $office->code }}</td>

                                            @foreach ($prevYears as $i => $py)
                                                <td class="text-end">
                                                    @php $val = $row['historical']["year_{$i}"] ?? 0; @endphp
                                                    {{ $val > 0 ? number_format($val, 0) : '-' }}
                                                </td>
                                            @endforeach

                                            <td class="text-end text-info fw-bold">
                                                {{ $row['demand'] > 0 ? number_format($row['demand'], 0) : '-' }}</td>
                                            <td class="text-end text-success fw-bold">
                                                {{ $row['approved'] > 0 ? number_format($row['approved'], 0) : '-' }}
                                            </td>
                                            <td class="text-end text-warning fw-bold">
                                                {{ $row['released'] > 0 ? number_format($row['released'], 0) : '-' }}
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ $balance > 0 ? number_format($balance, 0) : '-' }}</td>
                                            <td class="text-end"></td>
                                            <td class="text-end"></td>
                                            <td class="text-end"></td>
                                            <td class="text-end"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td class="text-center">{{ __('Grand Total') }}</td>
                                        @foreach ($prevYears as $i => $py)
                                            <td class="text-end">
                                                {{ $totals['historical'][$i] > 0 ? number_format($totals['historical'][$i], 0) : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="text-end text-info">
                                            {{ $totals['demand'] > 0 ? number_format($totals['demand'], 0) : '-' }}
                                        </td>
                                        <td class="text-end text-success">
                                            {{ $totals['approved'] > 0 ? number_format($totals['approved'], 0) : '-' }}
                                        </td>
                                        <td class="text-end text-warning">
                                            {{ $totals['released'] > 0 ? number_format($totals['released'], 0) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $totals['approved'] - $totals['released'] > 0 ? number_format($totals['approved'] - $totals['released'], 0) : '-' }}
                                        </td>
                                        <td class="text-end"></td>
                                        <td class="text-end"></td>
                                        <td class="text-end"></td>
                                        <td class="text-end"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- new table as like sheet of HO ends --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">

                        <div class="col-12 text-center">
                            <p class="mb-0 small">{{ __('Directorate of Secondary and Higher Education') }}</p>
                            <p class="mb-0 small">{{ __('Planning and Development Division, Budget Unit') }}</p>
                            <p class="mb-0 small">
                                {{ __('Secondary and Higher Education Division, Ministry of Education') }}</p>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <h5 class="fw-bold">{{ __('Budget Preparation (HQ)') }}</h5>
                        @if ($selectedCode)
                            <p class="text-muted">{{ __('Economic Code') }}: <strong>{{ $selectedCode->code }} -
                                    {{ $selectedCode->name }}</strong></p>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 50px;">{{ __('Sl') }}</th>
                                    <th rowspan="2">{{ __('Office Name') }}</th>
                                    <th rowspan="2" style="width: 80px;">{{ __('Code') }}</th>
                                    <th colspan="{{ count($prevYears) }}" class="bg-soft-info">
                                        {{ __('Actual Expenditure') }}</th>
                                    <th colspan="4" class="bg-soft-success">{{ __('Budget Year') }}
                                        ({{ \App\Models\FiscalYear::find($fiscal_year_id) ? \App\Models\FiscalYear::find($fiscal_year_id)->name : '' }})
                                    </th>
                                </tr>
                                <tr>
                                    @foreach ($prevYears as $py)
                                        <th>{{ $py->name }}</th>
                                    @endforeach
                                    <th class="text-info">{{ __('Demand') }}</th>
                                    <th class="text-success">{{ __('Approved') }}</th>
                                    <th class="text-warning">{{ __('Released') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totals = [
                                        'historical' => array_fill(0, count($prevYears), 0),
                                        'demand' => 0,
                                        'approved' => 0,
                                        'released' => 0,
                                    ];
                                @endphp
                                @foreach ($offices as $index => $office)
                                    @php
                                        $row = $officeWiseData[$office->id];
                                        $balance = $row['approved'] - $row['released'];

                                        // Update totals
                                        foreach ($prevYears as $i => $py) {
                                            $totals['historical'][$i] += $row['historical']["year_{$i}"] ?? 0;
                                        }
                                        $totals['demand'] += $row['demand'];
                                        $totals['approved'] += $row['approved'];
                                        $totals['released'] += $row['released'];
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $office->name }}</td>
                                        <td class="text-center">{{ $office->code }}</td>

                                        @foreach ($prevYears as $i => $py)
                                            <td class="text-end">
                                                @php $val = $row['historical']["year_{$i}"] ?? 0; @endphp
                                                {{ $val > 0 ? number_format($val, 0) : '-' }}
                                            </td>
                                        @endforeach

                                        <td class="text-end text-info fw-bold">
                                            {{ $row['demand'] > 0 ? number_format($row['demand'], 0) : '-' }}</td>
                                        <td class="text-end text-success fw-bold">
                                            {{ $row['approved'] > 0 ? number_format($row['approved'], 0) : '-' }}</td>
                                        <td class="text-end text-warning fw-bold">
                                            {{ $row['released'] > 0 ? number_format($row['released'], 0) : '-' }}</td>
                                        <td class="text-end fw-bold">
                                            {{ $balance > 0 ? number_format($balance, 0) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('Grand Total') }}</td>
                                    @foreach ($prevYears as $i => $py)
                                        <td class="text-end">
                                            {{ $totals['historical'][$i] > 0 ? number_format($totals['historical'][$i], 0) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end text-info">
                                        {{ $totals['demand'] > 0 ? number_format($totals['demand'], 0) : '-' }}</td>
                                    <td class="text-end text-success">
                                        {{ $totals['approved'] > 0 ? number_format($totals['approved'], 0) : '-' }}
                                    </td>
                                    <td class="text-end text-warning">
                                        {{ $totals['released'] > 0 ? number_format($totals['released'], 0) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $totals['approved'] - $totals['released'] > 0 ? number_format($totals['approved'] - $totals['released'], 0) : '-' }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
