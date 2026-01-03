  <div>  <style>
        @media print {
            .vertical-menu, .navbar-header, .footer, .card-body.border-bottom, .btn, .breadcrumb {
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
            .page-title-box h4 {
                text-align: center;
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Release (Head Quarter)') }}</h4>

                <div class="page-title-right d-flex align-items-center">
                    <button type="button" class="btn btn-primary btn-sm me-2" onclick="window.print()">
                        <i class="bx bx-printer"></i> {{ __('Print Report') }}
                    </button>
                    <ol class="breadcrumb m-0 d-none d-sm-flex">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Release') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Fiscal Year') }}</label>
                            <select wire:model.lazy="fiscal_year_id" class="form-select">
                                @foreach ($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Budget Type') }}</label>
                            <select wire:model.lazy="budget_type_id" class="form-select">
                                @foreach ($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle" style="width: 80px;">{{ __('Code') }}</th>
                                    <th rowspan="2" class="text-center align-middle">{{ __('Economic Code Name') }}</th>
                                    <th colspan="3" class="text-center">{{ __('Actual Expenditure') }}</th>
                                    <th colspan="4" class="text-center text-primary">{{ __('Budget Year') }} ({{ \App\Models\FiscalYear::find($fiscal_year_id) ? \App\Models\FiscalYear::find($fiscal_year_id)->name : '' }})</th>
                                </tr>
                                <tr>
                                    @foreach ($prevYears as $py)
                                        <th class="text-center">{{ $py->name }}</th>
                                    @endforeach
                                    @for ($i = count($prevYears); $i < 3; $i++)
                                        <th class="text-center">{{ __('Prev Year') }}</th>
                                    @endfor
                                    
                                    <th class="text-center text-info">{{ __('Demand') }}</th>
                                    <th class="text-center text-success">{{ __('Approved') }}</th>
                                    <th class="text-center text-warning">{{ __('Released') }}</th>
                                    <th class="text-center">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($economicCodes as $code)
                                    <tr class="{{ $code->parent_id == null ? 'bg-soft-primary fw-bold' : '' }}">
                                        <td class="text-center">
                                            <span class="badge bg-{{ $code->parent_id ? 'secondary' : 'primary' }}-subtle text-{{ $code->parent_id ? 'secondary' : 'primary' }}">
                                                {{ $code->code }}
                                            </span>
                                        </td>
                                        <td>{{ $code->name }}</td>
                                        
                                        {{-- Historical Data --}}
                                        @for ($i = 0; $i < 3; $i++)
                                            <td class="text-end">
                                                @php $val = $historicalData["year_{$i}"][$code->id] ?? 0; @endphp
                                                {{ $val > 0 ? number_format($val, 0) : '-' }}
                                            </td>
                                        @endfor

                                        {{-- Budget Year Data --}}
                                        @php 
                                            $data = $currentDemands[$code->id] ?? null;
                                            $demand = $data ? $data->demand : 0;
                                            $approved = $data ? $data->approved : 0;
                                            $released = $currentReleased[$code->id] ?? 0;
                                            $balance = $approved - $released;
                                        @endphp
                                        <td class="text-end text-info fw-bold">{{ $demand > 0 ? number_format($demand, 0) : '-' }}</td>
                                        <td class="text-end text-success fw-bold">{{ $approved > 0 ? number_format($approved, 0) : '-' }}</td>
                                        <td class="text-end text-warning fw-bold">{{ $released > 0 ? number_format($released, 0) : '-' }}</td>
                                        <td class="text-end fw-bold">{{ $balance > 0 ? number_format($balance, 0) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-center">{{ __('Grand Total') }}</td>
                                    @for ($i = 0; $i < 3; $i++)
                                        <td class="text-end">
                                            @php 
                                                $totalY = 0;
                                                foreach($economicCodes as $c) {
                                                    if($c->parent_id == null) $totalY += ($historicalData["year_{$i}"][$c->id] ?? 0);
                                                }
                                            @endphp
                                            {{ $totalY > 0 ? number_format($totalY, 0) : '-' }}
                                        </td>
                                    @endfor
                                    <td class="text-end text-info">
                                        @php 
                                            $totalD = 0;
                                            foreach($economicCodes as $c) {
                                                if($c->parent_id == null) $totalD += ($currentDemands[$c->id]->demand ?? 0);
                                            }
                                        @endphp
                                        {{ $totalD > 0 ? number_format($totalD, 0) : '-' }}
                                    </td>
                                    <td class="text-end text-success">
                                        @php 
                                            $totalA = 0;
                                            foreach($economicCodes as $c) {
                                                if($c->parent_id == null) $totalA += ($currentDemands[$c->id]->approved ?? 0);
                                            }
                                        @endphp
                                        {{ $totalA > 0 ? number_format($totalA, 0) : '-' }}
                                    </td>
                                    <td class="text-end text-warning">
                                        @php 
                                            $totalR = 0;
                                            foreach($economicCodes as $c) {
                                                if($c->parent_id == null) $totalR += ($currentReleased[$c->id] ?? 0);
                                            }
                                        @endphp
                                        {{ $totalR > 0 ? number_format($totalR, 0) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ ($totalA - $totalR) > 0 ? number_format($totalA - $totalR, 0) : '-' }}
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
                                        </div>