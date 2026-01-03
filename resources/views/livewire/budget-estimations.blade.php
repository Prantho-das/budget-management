<div class="demand-budget-main">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Demand') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Budget Demand') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (!$currentFiscalYear || !$currentOffice)
        <div class="alert alert-warning">
            {{ __('Please ensure you have an Active Fiscal Year and at least one Office created.') }}
        </div>
    @else
        <div class="row d-none">
            <div class="col-xl-4 col-md-6">
                <div class="common-card card shadow-sm border-0 mini-stat bg-primary">
                    <div class="card-body mini-stat-img">
                        <div class="float-end mini-stat-icon">
                            <i class="bx bx-wallet-alt font-size-24 text-white"></i>
                        </div>
                        <div class="text-white">
                            <h6 class="text-uppercase mb-3 font-size-13 text-white-50">{{ __('Total Demand Amount') }}
                            </h6>
                            <h4 class="mb-4 text-white">৳ {{ number_format($totalDemand, 2) }}</h4>
                            <span class="badge bg-white text-primary"> {{ $currentFiscalYear->name }} </span> <span
                                class="ms-2 text-white-50">{{ __('Current FY') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="common-card card shadow-sm border-0 mini-stat bg-info">
                    <div class="card-body mini-stat-img">
                        <div class="float-end mini-stat-icon">
                            <i class="bx bx-check-shield font-size-24 text-white"></i>
                        </div>
                        <div class="text-white">
                            <h6 class="text-uppercase mb-3 font-size-13 text-white-50">{{ __('Current Status') }}</h6>
                            <h4 class="mb-4 text-white">{{ __(ucfirst($status)) }}</h4>
                            <span class="badge bg-white text-info"> {{ __($current_stage) }} </span> <span
                                class="ms-2 text-white-50">{{ __('Current Stage') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="common-card card shadow-sm border-0 mini-stat bg-success">
                    <div class="card-body mini-stat-img">
                        <div class="float-end mini-stat-icon">
                            <i class="bx bx-building-house font-size-24 text-white"></i>
                        </div>
                        <div class="text-white">
                            <h6 class="text-uppercase mb-3 font-size-13 text-white-50">{{ __('Office Info') }}</h6>
                            <h4 class="mb-4 text-white">{{ $currentOffice->name }}</h4>
                            <span class="badge bg-white text-success"> {{ $currentOffice->code }} </span> <span
                                class="ms-2 text-white-50">{{ __('Office Code') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Controls -->
        <div class="card shadow-sm border-0 mb-4 d-none">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-muted small">{{ __('Budget Request Type') }}</label>
                        <select wire:model.live="budget_type_id" class="form-select border-light shadow-none"
                            {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                            @foreach ($budgetTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-muted small">{{ __('Submission Batch') }}</label>
                        <div class="input-group">
                            <select wire:model.live="batch_id" class="form-select border-light shadow-none">
                                @foreach ($allBatches as $batch)
                                    <option value="{{ $batch['batch_id'] }}">
                                        {{ date('d M Y', strtotime($batch['created_at'])) }}
                                        ({{ __($batch['status']) }})
                                    </option>
                                @endforeach
                                @if (!collect($allBatches)->pluck('batch_id')->contains($batch_id))
                                    <option value="{{ $batch_id }}">{{ __('New Submission') }}</option>
                                @endif
                            </select>
                            <button wire:click="startNewDemand" class="btn btn-primary shadow-none" type="button"
                                title="{{ __('New Demand') }}">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        @if ($status === 'draft' || $status === 'rejected')
                            <button wire:click="saveDraft"
                                class="btn btn-outline-secondary px-4 waves-effect">{{ __('Save Draft') }}</button>
                            <button wire:click="submitForApproval"
                                class="btn btn-primary px-4 ms-2 waves-effect waves-light">{{ __('Submit for Approval') }}</button>
                        @else
                            <div class="text-success fw-bold d-inline-flex align-items-center">
                                <i class="bx bxs-check-circle font-size-24 me-2"></i>
                                <span>{{ __('Successfully Submitted') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>



        <div class="d-none card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                <h5 class="card-title mb-0 ps-3">{{ __('Budget Approval Roadmap') }}</h5>
            </div>
            <div class="card-body">
                @php
                    $stageOrder = [
                        'Draft',
                        'District Review',
                        'Regional Review',
                        'HQ Audit',
                        'Final Release',
                        'Released',
                    ];
                    $stagesInfo = [
                        'Draft' => ['icon' => 'bx-file-blank', 'label' => __('Preparation')],
                        'District Review' => ['icon' => 'bx-git-repo-forked', 'label' => __('District')],
                        'Regional Review' => ['icon' => 'bx-map-pin', 'label' => __('Regional')],
                        'HQ Audit' => ['icon' => 'bx-search-alt', 'label' => __('HQ Audit')],
                        'Final Release' => ['icon' => 'bx-lock-open-alt', 'label' => __('Finalize')],
                        'Released' => ['icon' => 'bx-rocket', 'label' => __('Released')],
                    ];
                    $currentIndex = array_search($current_stage, $stageOrder);
                    if ($currentIndex === false && $current_stage === 'Draft') {
                        $currentIndex = 0;
                    }
                    $progressWidth = ($currentIndex / (count($stageOrder) - 1)) * 100;
                @endphp

                <div class="workflow-stepper">
                    <div class="step-line-bg"></div>
                    <div class="step-line-progress" style="width: calc({{ $progressWidth }}% - 100px + 54px);"></div>

                    @foreach ($stageOrder as $index => $stage)
                        @php
                            $isCompleted = $index < $currentIndex;
                            $isActive = $index == $currentIndex;
                        @endphp
                        <div class="workflow-step {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}">
                            <div class="step-icon">
                                @if ($isCompleted)
                                    <i class="bx bx-check-circle font-size-22"></i>
                                @else
                                    <i class="bx {{ $stagesInfo[$stage]['icon'] }} font-size-22"></i>
                                @endif
                            </div>
                            <div class="step-label">{{ $stagesInfo[$stage]['label'] }}</div>
                            <div class="step-badge">
                                @if ($isCompleted)
                                    {{ __('Done') }}
                                @elseif($isActive)
                                    {{ __('In Progress') }}
                                @else
                                    {{ __('Pending') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bx bxs-check-circle me-2 font-size-16 align-middle"></i>
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($status === 'rejected')
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="bx bxs-error-circle me-3 font-size-24"></i>
                <div>
                    <h6 class="alert-heading mb-1 fw-bold">{{ __('Budget Demand Rejected') }}</h6>
                    <p class="mb-0 small text-dark-50">
                        {{ __('Your demand has been rejected. Please review, adjust the amounts, and resubmit.') }}</p>
                </div>
            </div>
        @endif

        
        @if ($status == 'draft' || $status == 'rejected')
        <div class="unitoffice-entry-table">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle custom-budget-table">
                            <thead>
                                <tr class="table-primary text-center">
                                    <th rowspan="2">{{ __('Economic Code') }}</th>
                                    <th rowspan="2">{{ __('Description') }}</th>
                                    <th colspan="3">{{ __('Original') }}</th>
                                    <th rowspan="2">{{ __('Demand') }}<br>{{ current_fiscal_year() }}</th>
                                    <th rowspan="2">{{ __('Remarks') }}</th>
                                </tr>
                                </tr>
                                <tr class="table-primary text-center">
                                    @php
                                        $prevYears = \App\Models\FiscalYear::where(
                                            'end_date',
                                            '<',
                                            $currentFiscalYear->start_date,
                                        )
                                            ->orderBy('end_date', 'desc')
                                            ->take(3)
                                            ->get();
                                    @endphp
                                    @foreach ($prevYears as $py)
                                        <th>{{ $py->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $estMap = \App\Models\BudgetEstimation::where('batch_id', $batch_id)
                                        ->get()
                                        ->keyBy('economic_code_id');
                                @endphp

                                @foreach ($economicCodes as $code)
                                    <tr class="{{ $code->parent_id == null ? 'parent-expense-code' : '' }}">
                                        <td>
                                            <span
                                                class="badge  bg-{{ $code->parent_id ? 'secondary' : 'primary' }}-subtle text-{{ $code->parent_id ? 'secondary' : 'primary' }} p-2">
                                                {{ $code->code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $code->name }}</div>
                                            @if ($code->description)
                                                <small class="text-muted d-block text-truncate"
                                                    style="max-width: 200px;">
                                                    {{ $code->description }}
                                                </small>
                                            @endif
                                        </td>

                                        <!-- Previous 3 years actual expenditure -->
                                        @for ($i = 0; $i < 3; $i++)
                                            <td class="text-end">
                                                @if (isset($previousDemands[$code->id]["year_{$i}"]))
                                                    {{ number_format($previousDemands[$code->id]["year_{$i}"]['amount'], 0) }}
                                                @else
                                                    <span class="opacity-25">-</span>
                                                @endif
                                            </td>
                                        @endfor

                                        <!-- Current demand input -->
                                        <td>
                                            <div class="input-group input-group-sm">
                                                @if ($code->parent_id != null)
                                                    <input type="text"
                                                        class="form-control form-control-sm text-end"
                                                        wire:model.defer="demands.{{ $code->id }}"
                                                        placeholder="0"
                                                        {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Remarks -->
                                        <td>
                                            @if ($code->parent_id != null)
                                                <input type="text" class="form-control form-control-sm"
                                                    wire:model.defer="remarks.{{ $code->id }}"
                                                    placeholder="{{ __('Note...') }}"
                                                    {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>          
         @else
         <div class="card">
            <div class="card-body">
            <div class="text-success fw-bold d-inline-flex align-items-center justify-content-center text-center">
              <i class="bx bxs-check-circle font-size-24 me-2"></i>
              <span>{{ __('Successfully Submitted') }}</span>
         </div>
            </div>
         </div>
        
         @endif


</div>
</div>
</div>
</div>
@endif
</div>
