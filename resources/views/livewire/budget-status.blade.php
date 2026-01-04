<div class="unitoffice-entry-table budget-status-table">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Status & History') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Status') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card ">
                <div class="card-body">
                    <div class="row mb-4 justify-content-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ __('Fiscal Year') }}</label>
                            <select wire:model.live="fiscal_year_id" class="form-select">
                                @foreach ($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ __('Budget Type') }}</label>
                            <select wire:model.live="budget_type_id" class="form-select">
                                @foreach ($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ __($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (auth()->user()->can('view-all-offices-data'))
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('Office') }}</label>
                                <select wire:model.live="rpo_unit_id" class="form-select">
                                    @foreach ($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Economic Code') }}</th>
                                    <th>{{ __('Origin Office') }}</th>
                                    <th>{{ __('Demand') }}</th>
                                    <th>{{ __('Approved') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th>{{ __('Pending At') }}</th>
                                    <th>{{ __('Next Action') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estimations as $est)
                                    <tr>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-soft-primary text-primary">{{ $est->economicCode->code }}</span>
                                            <div class="small text-muted mt-1">{{ $est->economicCode->name }}</div>
                                        </td>
                                        <td>{{ $est->office->name }}</td>
                                        <td class="text-end fw-bold">৳ {{ number_format($est->amount_demand, 0) }}</td>
                                        <td class="text-end text-success">৳
                                            {{ number_format($est->amount_approved ?: 0, 0) }}</td>
                                        <td class="text-center">
                                            @if ($est->status === 'approved')
                                                <span
                                                    class="badge badge-pill badge-soft-success font-size-11">{{ __('Released') }}</span>
                                            @elseif($est->status === 'rejected')
                                                <span
                                                    class="badge badge-pill badge-soft-danger font-size-11">{{ __('Rejected') }}</span>
                                            @elseif($est->status === 'draft')
                                                <span
                                                    class="badge badge-pill badge-soft-warning font-size-11">{{ __('Draft') }}</span>
                                            @else
                                                <span
                                                    class="badge badge-pill badge-soft-primary font-size-11">{{ __('Submitted') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($est->status === 'approved')
                                                <span class="text-success fw-bold"><i
                                                        class="bx bx-check-double me-1"></i>{{ __('Finalized') }}</span>
                                            @elseif($est->targetOffice)
                                                <span class="text-info"><i
                                                        class="bx bx-building me-1"></i>{{ $est->targetOffice->name }}</span>
                                            @else
                                                <span class="text-muted italic">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($est->status === 'approved')
                                                <span
                                                    class="badge badge-pill badge-soft-success">{{ __('Budget Allocated') }}</span>
                                            @elseif($est->workflowStep)
                                                <span
                                                    class="badge badge-pill badge-soft-info">{{ __($est->workflowStep->name) }}</span>
                                                <div class="small text-muted mt-1">
                                                    {{ __('Required: ') . __($est->workflowStep->required_permission) }}
                                                </div>
                                            @else
                                                <span class="text-muted italic">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button wire:click="viewHistory({{ $est->id }})"
                                                class="btn btn-sm btn-light waves-effect waves-light info-btn"
                                                title="{{ __('View Approval Log') }}">
                                                <i class="bx bx-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-search-alt-2 font-size-24 d-block mb-2"></i>
                                            {{ __('No budget estimations found for the selected criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($showLogModal)
        <div class="modal-backdrop fade show"></div>
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Budget Approval History') }}</h5>
                        <button wire:click="closeLogModal()" type="button" class="btn-close"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Action At') }}</th>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('To') }}</th>
                                        <th class="text-end">{{ __('Demand') }}</th>
                                        <th class="text-end">{{ __('Approved/Released') }}</th>
                                        <th>{{ __('Action By') }}</th>
                                        <th>{{ __('Remarks') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedLog as $log)
                                        <tr>
                                            <td><small>{{ $log['action_at'] }}</small></td>
                                            <td><span
                                                    class="badge badge-soft-secondary">{{ __($log['from_stage'] ?? 'N/A') }}</span>
                                            </td>
                                            <td><span class="badge badge-soft-info">{{ __($log['to_stage']) }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if (isset($log['amount_demand']))
                                                    ৳ {{ number_format($log['amount_demand'], 0) }}
                                                @else
                                                    <span class="text-muted italic">--</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if (isset($log['amount_approved']))
                                                    <strong class="text-success">৳
                                                        {{ number_format($log['amount_approved'], 0) }}</strong>
                                                @elseif(isset($log['amount_demand']))
                                                    <strong class="text-success">৳
                                                        {{ number_format($log['amount_demand'], 0) }}</strong>
                                                @else
                                                    <span class="text-muted italic">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $log['action_name'] }}</div>
                                                <small class="text-muted">{{ $log['action_role'] }}</small>
                                            </td>
                                            <td style="white-space: normal; max-width: 200px;">{{ $log['remarks'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeLogModal()" type="button"
                            class="btn btn-secondary text-white">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
