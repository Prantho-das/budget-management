<div>
    <div>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">{{ __('Budget Distribution Entry') }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budget') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Distribution Entry') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4"
                                role="alert">
                                <i class="bx bxs-check-circle me-2"></i>
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"
                                role="alert" style="max-height: 300px; overflow-y: auto;">
                                <div class="d-flex">
                                    <i class="bx bxs-error-circle me-2 mt-1"></i>
                                    <div>
                                        {!! session('error') !!}
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="fy-select" class="form-label font-weight-bold text-muted small text-uppercase">{{ __('Fiscal Year') }}</label>
                            @php
$numto = new Rakibhstu\Banglanumber\NumberToBangla();

                    
                                    @endphp




                            <select id="fy-select" class="form-select shadow-sm border-primary" wire:model.live="fiscal_year_id">
                                @foreach($fiscalYears as $fy)
                                                                    <option value="{{ $fy->id }}">

                                                                        {{ bn_num($fy->name) }}
                                                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-none">
                            <label for="budget-type" class="form-label font-weight-bold text-muted small text-uppercase">{{ __('Budget Type') }}</label>
                            <select id="budget-type" class="form-select shadow-sm border-primary" wire:model.live="budget_type_id">
                                @foreach($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="parent-office" class="form-label font-weight-bold text-muted small text-uppercase">{{ __('Office Group') }}</label>
                            <select id="parent-office" class="form-select shadow-sm border-primary" wire:model.live="parent_office_id">
                                <option value="">{{ __('Select Office Group') }}</option>
                                @foreach($parentOffices as $office)
                                    <option value="{{ $office->id }}">{{ bn_num($office->code) }} - {{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button class="btn btn-primary px-4 shadow w-100" wire:click="save">
                                <i class="bx bx-save me-1"></i> {{ __('Save Distribution') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive shadow-sm" style="max-height: 700px; overflow: auto; border-radius: 8px;">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light sticky-top" style="z-index: 10;">
                                <tr class="text-nowrap bg-primary text-white">
                                    <th rowspan="2" style="min-width: 250px; left: 0; z-index: 11;" class="bg-primary sticky-left align-middle">{{ __('Office Name') }}</th>
                                    @foreach($economicCodes as $code)
                                        <th class="text-center" style="min-width: 280px;" title="{{ $code->name }}">
                                            <div class="font-size-13">{{ bn_num($code->code) }}</div>
                                            <div class="font-size-11 opacity-75 fw-normal text-truncate" style="max-width: 270px;">{{ $code->name }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr class="bg-primary text-white border-top border-light">
                                    @foreach($economicCodes as $code)

                                        <th class="p-1">

                                            <div class="d-flex justify-content-center gap-2 font-size-10">
                                                <div>
                                                    <h6>প্রকৃত ব্যায়</h2>
                                                    @foreach($prevFiscalYears as $fy)
                                                        <span class="opacity-75" style="min-width: 50px;">{{ bn_num($fy->name) }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex flex-column align-self-end">
                                                    <span class="fw-bold" style="min-width: 100px;">চাহিদা</span>
                                                </div>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @if($parent_office_id)
                                    @forelse($childOffices as $child)
                                        <tr wire:key="office-{{ $child->id }}-{{ $fiscal_year_id }}-{{ $budget_type_id }}">
                                            <td class="fw-medium bg-light sticky-left" style="left: 0; z-index: 5;">
                                                <div class="d-flex flex-column">
                                                    {{-- <span class="text-primary fw-bold">{{ bn_num($child->code) }}</span> --}}
                                                    <span class="font-size-12 text-muted">{{ $child->name }}</span>
                                                </div>
                                            </td>
                                            @foreach($economicCodes as $code)
                                                <td class="p-2 cell-distribution">
                                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                                        {{-- Historical Values aligned with header --}}
                                                        @foreach($prevFiscalYears as $fy)
                                                            <div class="text-center font-size-11 fw-semibold {{ ($history[$child->id][$code->id][$fy->id] ?? 0) > 0 ? 'text-dark' : 'text-muted opacity-50' }}" style="min-width: 50px;">
                                                                {{ bn_comma_format(($history[$child->id][$code->id][$fy->id] ?? 0), 0) }}
                                                            </div>
                                                        @endforeach
                                                        
                                                        <div style="min-width: 100px;">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" 
                                                                    class="form-control text-end border-primary-subtle bg-white fw-bold" 
                                                                    placeholder="0.00"
                                                                    wire:model.live.debounce.300ms="distributions.{{ $child->id }}.{{ $code->id }}"
                                                                    style="min-height: 32px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($economicCodes) + 1 }}" class="text-center py-5 text-muted">
                                                <i class="bx bx-info-circle font-size-24 d-block mb-2"></i>
                                                {{ __('No sub-offices found for the selected parent office.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="{{ count($economicCodes) + 1 }}" class="text-center py-5 text-muted">
                                            <i class="bx bx-pointer font-size-24 d-block mb-2 animate-bounce"></i>
                                            {{ __('Please select a Parent Office to load the distribution matrix.') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            @if($parent_office_id && count($childOffices) > 0)
                                <tfoot class="table-light fw-bold sticky-bottom">
                                    <tr class="shadow-lg">
                                        <td class="bg-light sticky-left" style="left: 0; z-index: 5;">{{ __('Grand Total') }}</td>
                                        @foreach($economicCodes as $code)
                                            <td class="text-end text-success p-2">
                                                @php
        $colTotal = collect($distributions)->map(fn($officeData) => $officeData[$code->id] ?? 0)->sum();
        $ministryBudget = $ministryAllocations[$code->id] ?? 0;
        $isExceeded = $ministryBudget > 0 && $colTotal > $ministryBudget;
                                                @endphp
                                                <div class="font-size-13 {{ $isExceeded ? 'text-danger animate-pulse' : 'text-success' }}">
                                                    {{ bn_comma_format($colTotal, 2) }}
                                                    @if($isExceeded)
                                                        <i class="bx bx-error-circle ms-1" title="Exceeds Ministry Allocation ({{ bn_comma_format($ministryBudget) }})"></i>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .sticky-top {
            position: sticky;
            top: 0;
        }

        .sticky-bottom {
            position: sticky;
            bottom: 0;
        }

        .sticky-left {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 5;
            border-right: 2px solid #eff2f7 !important;
        }

        .table-responsive::-webkit-scrollbar {
            height: 10px;
            width: 10px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #ced4da;
            border-radius: 5px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        .cell-distribution {
            background-color: #fdfdfd;
            transition: all 0.2s ease;
        }

        .cell-distribution:hover {
            background-color: #fff !important;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            background-color: #fff !important;
            box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.25) !important;
            border-color: #556ee6 !important;
        }

        .animate-bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .custom-budget-table thead th {
            background-color: #556ee6 !important;
            color: white !important;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        .custom-budget-table .table-primary {
            background-color: #f1f3fd !important;
        }
        .custom-budget-table .table-light {
            background-color: #f8f9fa !important;
        }
        .custom-budget-table tr td {
            padding: 0.75rem;
        }
        .btn-soft-info {
            background-color: rgba(80, 165, 241, 0.1);
            color: #50a5f1;
            transition: all 0.2s;
        }
        .btn-soft-info:hover {
            background-color: #50a5f1;
            color: white;
        }
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        tr:hover td {
            background-color: rgba(85, 110, 230, 0.05) !important;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
    </style>
</div>