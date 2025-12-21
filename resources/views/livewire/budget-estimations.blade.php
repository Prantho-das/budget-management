<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Estimation (Prakkalon)') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Estimation') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(!$currentFiscalYear || !$currentOffice)
        <div class="alert alert-warning">
            {{ __('Please ensure you have an Active Fiscal Year and at least one Office created.') }}
        </div>
    @else

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="card-title">{{ __('Fiscal Year') }}: {{ $currentFiscalYear->name }}</h5>
                            <p class="card-title-desc mb-2">{{ __('Office') }}: {{ $currentOffice->name }} ({{ $currentOffice->code }})</p>
                            
                            <div class="mb-3" style="max-width: 300px;">
                                <label class="form-label font-size-13 text-muted">{{ __('Budget Request Type') }}</label>
                                <select wire:model.live="budget_type_id" class="form-select form-select-sm" {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                    @foreach($budgetTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="mb-0">
                                <span class="text-muted">{{ __('Status') }}:</span>
                                @php
                                    $badgeClass = match($status) {
                                        'draft' => 'secondary',
                                        'submitted' => 'primary',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ __(ucfirst($status)) }}
                                </span>
                                
                                <span class="ms-3 text-muted">{{ __('Current Stage') }}:</span>
                                <span class="badge bg-info">{{ __($current_stage) }}</span>
                            </p>
                        </div>
                        <div>
                            @if($status === 'draft' || $status === 'rejected')
                                <button wire:click="saveDraft" class="btn btn-secondary waves-effect waves-light me-2">{{ __('Save Draft') }}</button>
                                <button wire:click="submitForApproval" class="btn btn-primary waves-effect waves-light">{{ __('Submit for Review') }}</button>
                            @else
                                <button class="btn btn-success disabled">
                                    <i class="bx bx-check-double me-1"></i> 
                                    @if($current_stage === 'Released') 
                                        {{ __('Released') }} 
                                    @else 
                                        {{ __('In-Review') }} ({{ __($current_stage) }}) 
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($status === 'approved' && $current_stage !== 'Released')
                        <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                            <i class="bx bx-info-circle me-3 font-size-24"></i>
                            <div>
                                <h5 class="alert-heading font-size-16 mb-1">{{ __('Budget In Progress') }}</h5>
                                <p class="mb-0">{{ __('Your budget request is currently at:') }} <strong>{{ __($current_stage) }}</strong></p>
                            </div>
                        </div>
                    @endif

                    @if($status === 'rejected')
                        <div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                            <i class="bx bx-error-circle me-3 font-size-24"></i>
                            <div>
                                <h5 class="alert-heading font-size-16 mb-1">{{ __('Budget Rejected') }}</h5>
                                <p class="mb-0">{{ __('Your budget request has been rejected. Please review the remarks and resubmit.') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($current_stage === 'Released')
                        <div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                            <i class="bx bx-check-circle me-3 font-size-24"></i>
                            <div>
                                <h5 class="alert-heading font-size-16 mb-1">{{ __('Budget Released!') }}</h5>
                                <p class="mb-0">{{ __('The budget for this period has been finalized and released. You can now view the approved amounts and track expenses.') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">{{ __('Economic Code') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @php
                                        // Get the fiscal years for headers
                                        $currentFY = \App\Models\FiscalYear::find($fiscal_year_id);
                                        $prevYears = \App\Models\FiscalYear::where('end_date', '<', $currentFY->start_date)
                                            ->orderBy('end_date', 'desc')
                                            ->take(3)
                                            ->get();
                                    @endphp
                                    @foreach($prevYears as $py)
                                        <th style="width: 10%;" class="text-center">{{ __('Expense') }}<br><small>{{ $py->name }}</small></th>
                                    @endforeach
                                    <th style="width: 12%;">{{ __('Demand Amount') }}</th>
                                    @if($status !== 'draft')
                                        <th style="width: 12%;">{{ __('Approved Amount') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Fetch estimations mapping for approved amounts
                                    $estMap = \App\Models\BudgetEstimation::where('fiscal_year_id', $fiscal_year_id)
                                        ->where('rpo_unit_id', $rpo_unit_id)
                                        ->where('budget_type_id', $budget_type_id)
                                        ->get()
                                        ->keyBy('economic_code_id');
                                @endphp
                                @foreach($economicCodes as $code)
                                    <tr>
                                        <td><strong>{{ $code->code }}</strong></td>
                                        <td>
                                            {{ $code->name }} <br>
                                            <small class="text-muted">{{ $code->description }}</small>
                                        </td>
                                        @for($i = 0; $i < 3; $i++)
                                            <td class="text-end">
                                                @if(isset($previousDemands[$code->id]["year_{$i}"]))
                                                    <strong class="text-info">৳ {{ number_format($previousDemands[$code->id]["year_{$i}"]['amount'], 2) }}</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endfor
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control" 
                                                       wire:model.defer="demands.{{ $code->id }}" 
                                                       placeholder="0.00"
                                                       {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        @if($status !== 'draft')
                                            <td class="text-end">
                                                @php $est = $estMap[$code->id] ?? null; @endphp
                                                @if($est)
                                                    <strong class="text-success">৳ {{ number_format($est->amount_approved ?? $est->amount_demand, 2) }}</strong>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                         @if($status === 'draft')
                            <button wire:click="saveDraft" class="btn btn-secondary waves-effect waves-light">{{ __('Save Draft') }}</button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
