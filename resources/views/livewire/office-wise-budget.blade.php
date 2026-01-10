<div>
    <style>
        @media print {
            body > *{
                line-height: 1.1 !important;
            }
            .table-responsive{
                max-height: unset !important;
            }
            .form-control{
                border: none !important;
                box-shadow: none !important;
                padding: 0 auto !important;
            }
            td,th{
                padding: 0 !important;
                line-height: 1.1 !important;
                margin: 0 !important;
                /* border-color:#000 !important; */
            }

            .vertical-menu,
            .navbar-header,
            .footer,
            .card-body.border-bottom,
            .btn,
            .breadcrumb,
            .filter-section {
                /* display: none !important; */
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* .card {
                border: none !important;
                box-shadow: none !important;
            } */

            .table-responsive {
                overflow: visible !important;
            }

            .office-wise-budget-table .title-box-inner {
                font-size: 7px !important;
            }

            .office-wise-budget-table .card .table-title-box .title-box-inner .ministry,
            .office-wise-budget-table .card .table-title-box .title-box-inner .title {
                font-size: 7px !important;
            }

            .table td,
            table th {
                /* padding: 0.3rem !important; */
                font-size: 7px !important;
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
                <h4 class="mb-sm-0 font-size-18">{{ __('Ministry Budget Preparation') }}</h4>

                <div class="page-title-right d-flex align-items-center">
                    <button type="button" class="btn btn-soft-warning btn-sm me-2" wire:click="moveAllToDraft" wire:loading.attr="disabled">
                        <i class="bx bx-undo"></i> {{ __('Move All to Draft') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-sm me-2" onclick="window.print()">
                        <i class="bx bx-printer"></i> {{ __('Print Report') }}
                    </button>
                    <ol class="breadcrumb m-0 d-none d-sm-flex">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Ministry Budget Preparation') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row filter-section">
        <div class="col-lg-12">
            <div class="card mb-3 d-none">
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
                                <select wire:model.lazy="economic_code_id" class="form-select custom-select2">
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
                                    @php
                                        // Helper for Dynamic Headers
                                        $fyName = $selectedFy->name;
                                        $parts = explode('-', $fyName);
                                        $startYearNum = (int)$parts[0];
                                        
                                        $h1 = $fullPrevYears[0]->name ?? 'N/A';
                                        $h2 = $fullPrevYears[1]->name ?? 'N/A';
                                        $h3_part = $fullPrevYears[1]->name ?? 'N/A';
                                        $h4_part = $selectedFy->name;
                                        
                                        $budget_fy = $selectedFy->name;
                                        $est_fy = $startYearNum + 1 . '-' . substr($startYearNum + 2, -2);
                                        $proj1_fy = $startYearNum + 2 . '-' . substr($startYearNum + 3, -2);
                                        $proj2_fy = $startYearNum + 3 . '-' . substr($startYearNum + 4, -2);
                                    @endphp
                                    <tr class="table-primary text-center">
                                        <th rowspan="2">অফিসের নাম</th>
                                        <th rowspan="2">অফিস কোড</th>
                                        <th colspan="4">প্রকৃত ব্যায়</th>
                                        <th>বাজেট</th>
                                        <th>প্রস্তাবিত <br> সংশোধিত</th>
                                        <th>প্রাক্কলন</th>
                                        <th colspan="2">প্রক্ষেপন</th>
                                        <th rowspan="2">অতিরিক্ত <br> দাবি</th>
                                    </tr>
                                    <tr>
                                        <th>{{ $h1 }}</th>
                                        <th>{{ $h2 }}</th>
                                        <th>প্রথম ৬ মাস <br> {{ $h3_part }}</th>
                                        <th>প্রথম ৬ মাস <br> {{ $h4_part }}</th>
                                        <th>{{ $budget_fy }}</th>
                                        <th>{{ $budget_fy }}</th>
                                        <th>{{ $est_fy }}</th>
                                        <th>{{ $proj1_fy }}</th>
                                        <th>{{ $proj2_fy }}</th>
                                    </tr>
                                    <tr>
                                        <th>1</th>
                                        <th>2</th>
                                        <th>3</th>
                                        <th>4</th>
                                        <th>5</th>
                                        <th>6</th>
                                        <th>7</th>
                                        <th>8</th>
                                        <th>9</th>
                                        <th>10</th>
                                        <th>11</th>
                                        <th>12</th>
                                        {{-- <th>13</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totals = [
                                            'h1' => 0, 'h2' => 0, 'hp1' => 0, 'hp2' => 0,
                                            'demand' => 0, 'revised' => 0, 'p1' => 0, 'p2' => 0, 'p3' => 0,
                                            'extra' => 0
                                        ];
                                    @endphp
                                    @foreach ($offices as $index => $office)
                                        @php
                                            $row = $officeWiseData[$office->id];
                                            
                                            $totals['h1'] += $row['history_full_1'];
                                            $totals['h2'] += $row['history_full_2'];
                                            $totals['hp1'] += $row['history_part_1'];
                                            $totals['hp2'] += $row['history_part_2'];
                                            $totals['demand'] += $row['demand'];
                                            $totals['revised'] += $row['revised'];
                                            $totals['p1'] += $row['projection_1'];
                                            $totals['p2'] += $row['projection_2'];
                                            $totals['p3'] += $row['projection_3'];
                                        @endphp
                                        <tr>
                                            <td>{{ $office->name }}</td>
                                            <td class="text-center">{{ $office->code }}</td>

                                            {{-- Actual Expenditure (3, 4, 5, 6) --}}
                                            <td class="text-end">{{ $row['history_full_1'] > 0 ? number_format($row['history_full_1'], 0) : '-' }}</td>
                                            <td class="text-end">{{ $row['history_full_2'] > 0 ? number_format($row['history_full_2'], 0) : '-' }}</td>
                                            <td class="text-end">{{ $row['history_part_1'] > 0 ? number_format($row['history_part_1'], 0) : '-' }}</td>
                                            <td class="text-end text-primary fw-bold">{{ $row['history_part_2'] > 0 ? number_format($row['history_part_2'], 0) : '-' }}</td>

                                            {{-- Budget (Demand) (7) --}}
                                            <td class="text-end">{{ $row['demand'] > 0 ? number_format($row['demand'], 0) : '-' }}</td>

                                            {{-- Revised (8) --}}
                                            <td class="text-end">{{ $row['revised'] > 0 ? number_format($row['revised'], 0) : '-' }}</td>

                                            {{-- Estimation (9) --}}
                                            <td class="text-end">
                                                <input type="number" class="form-control form-control-sm text-end" 
                                                       value="{{ $row['projection_1'] ?: $row['estimation_suggestion'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'projection_1')"
                                                       style="min-width: 90px;">
                                            </td>

                                            {{-- Projection 1 (10) --}}
                                            <td class="text-end">
                                                <input type="number" class="form-control form-control-sm text-end" 
                                                       value="{{ $row['projection_2'] ?: $row['projection1_suggestion'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'projection_2')"
                                                       style="min-width: 90px;">
                                            </td>

                                            {{-- Projection 2 (11) --}}
                                            <td class="text-end">
                                                <input type="number" class="form-control form-control-sm text-end" 
                                                       value="{{ $row['projection_3'] ?: $row['projection2_suggestion'] }}"
                                                       wire:change="updateAmount({{ $office->id }}, $event.target.value, 'projection_3')"
                                                       style="min-width: 90px;">
                                            </td>

                                            {{-- Extra Demand (12) --}}
                                            {{-- <td class="text-end">0</td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr class="text-end">
                                        <td colspan="2" class="text-center">{{ __('Grand Total') }}</td>
                                        <td>{{ $totals['h1'] > 0 ? number_format($totals['h1'], 0) : '-' }}</td>
                                        <td>{{ $totals['h2'] > 0 ? number_format($totals['h2'], 0) : '-' }}</td>
                                        <td>{{ $totals['hp1'] > 0 ? number_format($totals['hp1'], 0) : '-' }}</td>
                                        <td>{{ $totals['hp2'] > 0 ? number_format($totals['hp2'], 0) : '-' }}</td>
                                        <td>{{ $totals['demand'] > 0 ? number_format($totals['demand'], 0) : '-' }}</td>
                                        <td>{{ $totals['revised'] > 0 ? number_format($totals['revised'], 0) : '-' }}</td>
                                        <td>{{ $totals['p1'] > 0 ? number_format($totals['p1'], 0) : '-' }}</td>
                                        <td>{{ $totals['p2'] > 0 ? number_format($totals['p2'], 0) : '-' }}</td>
                                        <td>{{ $totals['p3'] > 0 ? number_format($totals['p3'], 0) : '-' }}</td>
                                        <td>-</td>
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
