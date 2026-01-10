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
                        <div class="col-sm-12 col-md-3 col-md-3 d-none">
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
                        <div class="col-sm-12 col-md-3 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Office') }}</label>
                                <select wire:model.lazy="selected_office_id" class="form-select">
                                    <option value="">{{ __('All Offices') }}</option>
                                    @foreach ($allOffices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
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
                                <div class="title">{{ $budgetTypes->find($budget_type_id)->name ?? 'Revenue' }} - {{ __('Expenditure Initial Estimation and Projection Summary') }}</div>
                                <div class="ministry">
                                    <span>{{ __('Ministry / Division') }}</span> : {{ $selectedOffice && $selectedOffice->parent ? $selectedOffice->parent->name : get_setting('ministry_name', '১৬১ সুরক্ষা সেনা বিভাগ,স্বরাষ্ট্র মন্ত্রণালয়') }}
                                </div>
                                <div class="ministry">
                                    <span>{{ __('Department') }}</span> : {{ $selectedOffice ? $selectedOffice->name : get_setting('office_name', '১৬১০৫ ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর') }}
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
                                        <th rowspan="2">Action</th>
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

                                            {{-- Budget (Demand) --}}
                                            <td class="text-end text-info fw-bold">
                                                <input type="number" 
                                                       class="form-control form-control-sm text-end" 
                                                       value="{{ $row['demand'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'demand')"
                                                       style="min-width: 80px;">
                                            </td>

                                            {{-- Revised --}}
                                            <td class="text-end">
                                                <input type="number" 
                                                       class="form-control form-control-sm text-end" 
                                                       value="{{ $row['revised'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'revised')"
                                                       style="min-width: 80px;">
                                            </td>

                                            {{-- Estimation (Next Year) --}}
                                            <td class="text-end">
                                                <input type="number" 
                                                       class="form-control form-control-sm text-end" 
                                                       value="{{ $row['projection_1'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'projection_1')"
                                                       style="min-width: 80px;">
                                            </td>

                                            {{-- Projection 1 --}}
                                            <td class="text-end fw-bold">
                                                 <input type="number" 
                                                       class="form-control form-control-sm text-end" 
                                                       value="{{ $row['projection_2'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'projection_2')"
                                                       style="min-width: 80px;">
                                            </td>
                                            
                                            {{-- Projection 2 --}}
                                            <td class="text-end"></td>
                                            
                                            {{-- Extra Demand --}}
                                            <td class="text-end"></td>
                                            
                                            {{-- Action --}}
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-success" wire:click="approve({{ $office->id }})" wire:loading.attr="disabled">
                                                    <i class="bx bx-check"></i> {{ __('Approve') }}
                                                </button>
                                            </td>
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

    
</div>
