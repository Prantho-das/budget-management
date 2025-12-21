<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('My Budget Summary') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Summary') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Fiscal Year Selector -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">{{ __('Fiscal Year') }}</label>
            <select wire:model.live="fiscal_year_id" class="form-select">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Budget Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Draft Budgets') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalDraft }}">{{ $totalDraft }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-secondary mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-secondary">
                                    <i class="bx bx-file font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Submitted') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalSubmitted }}">{{ $totalSubmitted }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-primary mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="bx bx-send font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Released') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalReleased }}">{{ $totalReleased }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-success mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="bx bx-check-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('Rejected') }}</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $totalRejected }}">{{ $totalRejected }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0 text-end dash-widget">
                            <div class="avatar-sm rounded-circle bg-soft-danger mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-danger">
                                    <i class="bx bx-x-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Total Allocated') }}</h5>
                    <div class="text-center">
                        <h2 class="text-primary mb-0">৳ {{ number_format($totalAllocated, 2) }}</h2>
                        <p class="text-muted">{{ __('Total Budget Released') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Total Expenses') }}</h5>
                    <div class="text-center">
                        <h2 class="text-warning mb-0">৳ {{ number_format($totalExpenses, 2) }}</h2>
                        <p class="text-muted">{{ __('Total Amount Spent') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Available Balance') }}</h5>
                    <div class="text-center">
                        <h2 class="text-success mb-0">৳ {{ number_format($availableBalance, 2) }}</h2>
                        <p class="text-muted">{{ __('Remaining Budget') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget by Economic Code -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Budget Breakdown by Economic Code') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Allocated') }}</th>
                                    <th class="text-end">{{ __('Spent') }}</th>
                                    <th class="text-end">{{ __('Balance') }}</th>
                                    <th class="text-center">{{ __('Utilization') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetByCode as $item)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $item['code'] }}</span></td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-end fw-semibold">৳ {{ number_format($item['allocated'], 2) }}</td>
                                        <td class="text-end text-warning">৳ {{ number_format($item['spent'], 2) }}</td>
                                        <td class="text-end text-success">৳ {{ number_format($item['balance'], 2) }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $item['utilization'] > 90 ? 'bg-danger' : ($item['utilization'] > 70 ? 'bg-warning' : 'bg-success') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($item['utilization'], 100) }}%">
                                                    {{ number_format($item['utilization'], 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('No budget allocations found for this fiscal year.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
