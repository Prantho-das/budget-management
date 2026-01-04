<div>
    <style>
        @media print {
            .vertical-menu, .navbar-header, .footer, .card-body.border-bottom, .btn, .breadcrumb, .filter-section {
                display: none !important;
            }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
        }
        .table-sm td, .table-sm th { padding: 0.3rem !important; font-size: 13px; }
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
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Fiscal Year') }}</label>
                            <select wire:model.lazy="fiscal_year_id" class="form-select">
                                @foreach ($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Budget Type') }}</label>
                            <select wire:model.lazy="budget_type_id" class="form-select">
                                @foreach ($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Economic Code') }} ({{ __('Optional') }})</label>
                            <select wire:model.lazy="economic_code_id" class="form-select">
                                <option value="">{{ __('All Economic Codes (Summary)') }}</option>
                                @foreach ($economicCodes as $ec)
                                    <option value="{{ $ec->id }}">{{ $ec->code }} - {{ $ec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            {{-- Placeholder for left side if any --}}
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-0 small">{{ __('Directorate of Secondary and Higher Education') }}</p>
                            <p class="mb-0 small">{{ __('Planning and Development Division, Budget Unit') }}</p>
                            <p class="mb-0 small">{{ __('Secondary and Higher Education Division, Ministry of Education') }}</p>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <h5 class="fw-bold">{{ __('Budget Preparation (HQ)') }}</h5>
                        @if($selectedCode)
                            <p class="text-muted">{{ __('Economic Code') }}: <strong>{{ $selectedCode->code }} - {{ $selectedCode->name }}</strong></p>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 50px;">{{ __('Sl') }}</th>
                                    <th rowspan="2">{{ __('Office Name') }}</th>
                                    <th rowspan="2" style="width: 80px;">{{ __('Code') }}</th>
                                    <th colspan="{{ count($prevYears) }}" class="bg-soft-info">{{ __('Actual Expenditure') }}</th>
                                    <th colspan="4" class="bg-soft-success">{{ __('Budget Year') }} ({{ \App\Models\FiscalYear::find($fiscal_year_id) ? \App\Models\FiscalYear::find($fiscal_year_id)->name : '' }})</th>
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
                                        'released' => 0
                                    ];
                                @endphp
                                @foreach ($offices as $index => $office)
                                    @php 
                                        $row = $officeWiseData[$office->id];
                                        $balance = $row['approved'] - $row['released'];
                                        
                                        // Update totals
                                        foreach($prevYears as $i => $py) {
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

                                        <td class="text-end text-info fw-bold">{{ $row['demand'] > 0 ? number_format($row['demand'], 0) : '-' }}</td>
                                        <td class="text-end text-success fw-bold">{{ $row['approved'] > 0 ? number_format($row['approved'], 0) : '-' }}</td>
                                        <td class="text-end text-warning fw-bold">{{ $row['released'] > 0 ? number_format($row['released'], 0) : '-' }}</td>
                                        <td class="text-end fw-bold">{{ $balance > 0 ? number_format($balance, 0) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('Grand Total') }}</td>
                                    @foreach($prevYears as $i => $py)
                                        <td class="text-end">{{ $totals['historical'][$i] > 0 ? number_format($totals['historical'][$i], 0) : '-' }}</td>
                                    @endforeach
                                    <td class="text-end text-info">{{ $totals['demand'] > 0 ? number_format($totals['demand'], 0) : '-' }}</td>
                                    <td class="text-end text-success">{{ $totals['approved'] > 0 ? number_format($totals['approved'], 0) : '-' }}</td>
                                    <td class="text-end text-warning">{{ $totals['released'] > 0 ? number_format($totals['released'], 0) : '-' }}</td>
                                    <td class="text-end">{{ ($totals['approved'] - $totals['released']) > 0 ? number_format($totals['approved'] - $totals['released'], 0) : '-' }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
