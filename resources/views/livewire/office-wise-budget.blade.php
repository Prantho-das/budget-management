<div>
    <style>
        @page {
            size: legal landscape;
            margin: 5mm;
        }

        @media print {
            /* 1. Global Print Reset */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: #000 !important;
                background: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            .vertical-menu,
            .navbar-header,
            .footer,
            .page-title-box,
            .filter-section,
            .btn,
            .breadcrumb,
            #page-topbar,
            .main-content footer,
            .bx {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .page-content {
                padding: 0 !important;
            }

            .card {
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-body {
                padding: 0 !important;
            }

            /* 2. Professional Table Styling */
            .table-responsive {
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 8px !important; /* Professional high-density font */
                border: 1px solid #000 !important;
                table-layout: auto !important; /* Allow columns to fit content */
            }

            table th, table td {
                padding: 2px 3px !important;
                border: 0.5pt solid #000 !important; /* Hairline professional border */
                word-wrap: break-word !important;
                line-height: 1.1 !important;
                text-align: center !important;
            }

            table td.text-end {
                text-align: right !important;
            }

            table td.text-start {
                text-align: left !important;
            }

            /* Header Specifics */
            .table-title-box {
                margin-bottom: 10px !important;
                text-align: center !important;
                border-bottom: 1px double #000 !important;
                padding-bottom: 5px !important;
            }

            .title-box-inner .title {
                font-size: 14px !important;
                text-transform: uppercase !important;
                font-weight: bold !important;
                margin: 0 !important;
            }

            .title-box-inner .ministry {
                font-size: 10px !important;
                font-weight: normal !important;
                margin-top: 2px !important;
            }

            /* 3. Logical Row Styling */
            .table-primary {
                background-color: #eee !important; /* Very light gray for headers */
            }

            .fw-bold {
                font-weight: bold !important;
            }

            .table-light {
                background-color: #f9f9f9 !important;
            }

            /* 4. Formatting Fixes */
            .form-control {
                border: none !important;
                padding: 0 !important;
                font-size: 8px !important;
                height: auto !important;
            }

            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
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
                                    @foreach ($flattenedTable as $index => $item)
                                        @php
                                            $office = $item['office'];
                                            $row = $item['data'];
                                            $depth = $item['depth'];
                                            $type = $item['type'];
                                            $isParent = $item['has_children'];
                                            $rowKey = $type . '-' . $office->id . '-' . $index;
                                        @endphp
                                        <tr wire:key="{{ $rowKey }}" class="{{ $type === 'subtotal' ? 'table-light fw-bold' : ($isParent ? 'fw-bold' : '') }}">
                                            <td style="padding-left: {{ $depth * 25 + 10 }}px !important;">
                                                @if($type === 'subtotal')
                                                    {{ __('Total') }} ({{ $office->name }})
                                                @else
                                                    @if($isParent)
                                                        <i class="bx bx-chevron-down me-1"></i>
                                                    @endif
                                                    {{ $office->name }}
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $type === 'office' ? $office->code : '-' }}</td>

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
                                                @if($type === 'office')
                                                    <input type="number" class="form-control form-control-sm text-end {{ $isParent ? 'fw-bold' : '' }}" 
                                                           value="{{ $row['projection_1'] ?: $row['estimation_suggestion'] }}"
                                                           wire:change.debounce.500ms="updateAmount({{ $office->id }}, $event.target.value, 'projection_1')"
                                                           style="min-width: 90px;"
                                                           {{ $isParent ? 'readonly' : '' }}>
                                                @else
                                                    <span class="fw-bold">{{ number_format($row['projection_1'] ?: $row['estimation_suggestion'], 0) }}</span>
                                                @endif
                                            </td>

                                            {{-- Projection 1 (10) --}}
                                            <td class="text-end">
                                                @if($type === 'office')
                                                    <input type="number" class="form-control form-control-sm text-end {{ $isParent ? 'fw-bold' : '' }}" 
                                                           value="{{ $row['projection_2'] ?: $row['projection1_suggestion'] }}"
                                                           wire:change.debounce.500ms="updateAmount({{ $office->id }}, $event.target.value, 'projection_2')"
                                                           style="min-width: 90px;"
                                                           {{ $isParent ? 'readonly' : '' }}>
                                                @else
                                                    <span class="fw-bold">{{ number_format($row['projection_2'] ?: $row['projection1_suggestion'], 0) }}</span>
                                                @endif
                                            </td>

                                            {{-- Projection 2 (11) --}}
                                            <td class="text-end">
                                                @if($type === 'office')
                                                    @php
                                                        // Calculate Max Limit if Economic Code is selected
                                                        $maxLimit = 0;
                                                        $limitWarning = '';
                                                        $remaining = 0;
                                                        
                                                        // Only apply if an Economic Code is selected and limits exist
                                                        if($economic_code_id && isset($ministryLimits[$economic_code_id])) {
                                                             $remaining = $ministryLimits[$economic_code_id]['remaining'];
                                                             // Add current value back to remaining to allow editing
                                                             $currentVal = $row['projection_3'] ?: 0;
                                                             $maxLimit = $remaining + $currentVal;
                                                             $limitWarning = "Max: " . number_format($remaining + $currentVal);
                                                        }
                                                    @endphp
                                                    <input type="number" class="form-control form-control-sm text-end {{ $isParent ? 'fw-bold' : '' }}" 
                                                           value="{{ $row['projection_3'] ?: $row['projection2_suggestion'] }}"
                                                           wire:change.debounce.500ms="updateAmount({{ $office->id }}, $event.target.value, 'projection_3')"
                                                           style="min-width: 90px;"
                                                           @if($economic_code_id && $maxLimit > 0) 
                                                               max="{{ $maxLimit }}" 
                                                               title="{{ $limitWarning }}" 
                                                               data-bs-toggle="tooltip"
                                                           @endif
                                                           {{ $isParent ? 'readonly' : '' }}>
                                                @else
                                                    <span class="fw-bold">{{ number_format($row['projection_3'] ?: $row['projection2_suggestion'], 0) }}</span>
                                                @endif
                                            </td>

                                            {{-- Extra Demand (12) --}}
                                            <td class="text-center">-</td>
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
