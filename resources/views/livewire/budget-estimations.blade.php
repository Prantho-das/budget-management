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
                            <h4 class="mb-4 text-white">{{ number_format($totalDemand, 2) }}</h4>
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
        <div class="card shadow-sm border-0 mb-4 floating-budget-card sticky">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-muted small">{{ __('Budget Request Type') }}</label>
                        <div class="d-flex align-items-center">
                            @php
                                $currentType = $budgetTypes->firstWhere('id', $budget_type_id);
                            @endphp
                            @if($currentType)
                                <div class="badge bg-primary-subtle text-primary font-size-14 px-3 py-2 border border-primary-subtle rounded-pill">
                                    <i class="bx bx-check-double me-1"></i> {{ $currentType->name }}
                                </div>
                            @else
                                <div class="badge bg-warning-subtle text-warning font-size-14 px-3 py-2 border border-warning-subtle rounded-pill">
                                    {{ __('Loading...') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0 d-none">
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
                            <button wire:click="startNewDemand" class="btn btn-primary shadow-none {{ $has_existing_batch ? 'disabled' : '' }}" type="button"
                                title="{{ $has_existing_batch ? __('A batch already exists for this type') : __('New Demand') }}"
                                {{ $has_existing_batch ? 'disabled' : '' }}>
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-7"></div>
                    <div class="col-md-5 text-center text-md-end">
                        @if ($status === 'draft' || $status === 'rejected')
                            <button wire:click="saveDraft"
                                class="btn btn-outline-secondary px-4 waves-effect">{{ __('Save Draft') }}</button>
                            <button type="button" onclick="confirmSubmission()"
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

        @if ($is_released)
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="bx bxs-check-shield font-size-24 me-2"></i>
                <div>
                    <strong>{{ __('Budget Released!') }}</strong> {{ __('This budget type has already been fully approved and released. You can view the details below.') }}
                </div>
            </div>
        @elseif ($is_pending && $status !== 'submitted')
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="bx bxs-info-circle font-size-24 me-2"></i>
                <div>
                    <strong>{{ __('Submission in Progress') }}</strong> {{ __('You cannot start a new batch. Please wait for the current one to be approved or rejected.') }}
                </div>
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
                                                ->get()
                                                ->reverse();
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

                                    @foreach ($economicCodes as $layer1)
                                        @php $rootIdx = $loop->iteration; @endphp
                                        <tr class="table-primary border-start border-4 border-primary">
                                            <td colspan="2"><strong>{{ $rootIdx }}. {{ $layer1->code }} - {{ $layer1->name }}</strong></td>
                                            @for ($i = 0; $i < 3; $i++)
                                                <td class="text-end fw-bold">
                                                    @php
                                                        // Calculate Subtotal for Layer 1
                                                        $layer1Ids = $layer1->children->flatMap->children->pluck('id')->toArray();
                                                        $L1Total = 0;
                                                         if (isset($layer1Ids) && count($layer1Ids) > 0) {
                                                            foreach($layer1Ids as $lid) {
                                                                if(isset($previousDemands[$lid]["year_{$i}"])) {
                                                                    $L1Total += $previousDemands[$lid]["year_{$i}"]['amount'];
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    {{ $L1Total > 0 ? number_format($L1Total, 0) : '-' }}
                                                </td>
                                            @endfor
                                            <td class="text-end fw-bold text-primary">
                                                @php
                                                     $L1CurrentTotal = collect($demands)->only($layer1Ids)->sum();
                                                @endphp
                                                {{ $L1CurrentTotal > 0 ? number_format($L1CurrentTotal) : '-' }}
                                            </td>
                                            <td></td>
                                        </tr>

                                        @foreach ($layer1->children as $layer2)
                                            @php $subIdx = $loop->iteration; @endphp
                                            <tr class="table-light">
                                                <td colspan="2" style="padding-left: 25px;">
                                                    <i class="mdi mdi-arrow-right-bottom me-1 text-muted"></i>
                                                    <span class="badge bg-info-subtle text-info">{{ $layer2->code }}</span> 
                                                    <span class="fw-medium text-info">{{ $layer2->name }}</span>
                                                </td>
                                                @for ($i = 0; $i < 3; $i++)
                                                    <td class="text-end fw-medium text-secondary">
                                                        @php
                                                            // Calculate Subtotal for Layer 2
                                                            $layer2Ids = $layer2->children->pluck('id')->toArray();
                                                            $L2Total = 0;
                                                            if (isset($layer2Ids) && count($layer2Ids) > 0) {
                                                                foreach($layer2Ids as $lid) {
                                                                    if(isset($previousDemands[$lid]["year_{$i}"])) {
                                                                        $L2Total += $previousDemands[$lid]["year_{$i}"]['amount'];
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $L2Total > 0 ? number_format($L2Total, 0) : '-' }}
                                                    </td>
                                                @endfor
                                                 <td class="text-end fw-medium text-info">
                                                    @php
                                                         $L2CurrentTotal = collect($demands)->only($layer2Ids)->sum();
                                                    @endphp
                                                    {{ $L2CurrentTotal > 0 ? number_format($L2CurrentTotal) : '-' }}
                                                </td>
                                                <td></td>
                                            </tr>

                                            @foreach ($layer2->children as $layer3)
                                                 <tr>
                                                    <td style="width: 120px; padding-left: 50px;">
                                                        <span class="badge bg-light text-dark border">{{ $layer3->code }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $layer3->name }}
                                                        @if ($layer3->description)
                                                            <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                                                {{ $layer3->description }}
                                                            </small>
                                                        @endif
                                                    </td>

                                                    <!-- Previous 3 years actual expenditure -->
                                                    @for ($i = 0; $i < 3; $i++)
                                                        <td class="text-end">
                                                            @if (isset($previousDemands[$layer3->id]["year_{$i}"]))
                                                                {{ number_format($previousDemands[$layer3->id]["year_{$i}"]['amount'], 0) }}
                                                            @else
                                                                <span class="opacity-25">-</span>
                                                            @endif
                                                        </td>
                                                    @endfor

                                                    <!-- Current demand input -->
                                                    <td style="width: 150px;">
                                                        <div class="form-group mb-0">
                                                            <input type="text"
                                                                class="form-control form-control-sm text-end"
                                                                wire:model.defer="demands.{{ $layer3->id }}"
                                                                placeholder="0"
                                                                {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>

                                                            @php
                                                                $yCount = count($prevYears);
                                                                $latestIdx = $yCount - 1;
                                                            @endphp
                                                            @if (($status === 'draft' || $status === 'rejected') && $latestIdx >= 0 && isset($previousDemands[$layer3->id]["year_{$latestIdx}"]))
                                                                @php
                                                                    $suggested = round(
                                                                        $previousDemands[$layer3->id]["year_{$latestIdx}"]['amount'] *
                                                                            1.1,
                                                                    );
                                                                @endphp
                                                               
                                                                <button class="btn btn-soft-info btn-sm px-2 py-0 border-0 w-100 text-end mt-1" 
                                                                    type="button" 
                                                                    wire:click="applySuggestion({{ $layer3->id }})"
                                                                    title="{{ __('Click to apply 10% increase') }}">
                                                                    <small class="fw-bold" style="font-size: 10px;">{{ __('Sugg: ') . number_format($suggested) }}</small>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <!-- Remarks -->
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model.defer="remarks.{{ $layer3->id }}"
                                                            placeholder="{{ __('Note...') }}"
                                                            {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($status === 'approved' || $current_stage === 'Released')
            <div class="card shadow-sm border-0">
                <div class="card-body py-5 text-center">
                    <div class="mb-4">
                        <i class="bx bxs-check-shield text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">{{ __('Budget Fully Released') }}</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                        {{ __('This budget demand has been successfully approved and released for the current fiscal year. No further modifications are allowed for this batch.') }}
                    </p>
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success px-4 py-2 rounded-pill">
                        <i class="bx bx-check-double me-2"></i>
                        <span class="fw-semibold">{{ __('Status') }}: {{ __('Released') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0">
                <div class="card-body py-5 text-center">
                    <div class="mb-4">
                        <i class="bx bxs-hourglass-top text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">{{ __('The budget is in the approval situation') }}</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                        {{ __('This budget demand has been submitted and is currently traveling through the approval hierarchy. You will be notified once it is Released or if any modifications are requested.') }}
                    </p>
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning px-4 py-2 rounded-pill">
                        <i class="bx bx-loader-alt bx-spin me-2"></i>
                        <span class="fw-semibold">{{ __('Current Status') }}: {{ __($current_stage) }}</span>
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
@push('scripts')
    <script>
        function confirmSubmission() {
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You want to submit this budget demand for approval?') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#34c38f',
                cancelButtonColor: '#f46a6a',
                confirmButtonText: "{{ __('Yes, submit it!') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('submitForApproval');
                }
            })
        }
    </script>
@endpush
