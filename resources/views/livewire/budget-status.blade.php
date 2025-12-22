<div>
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
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('Fiscal Year') }}</label>
                            <select wire:model.live="fiscal_year_id" class="form-select">
                                @foreach($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('Budget Type') }}</label>
                            <select wire:model.live="budget_type_id" class="form-select">
                                @foreach($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ __($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->can('view-all-offices-data'))
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('Office') }}</label>
                                <select wire:model.live="rpo_unit_id" class="form-select">
                                    @foreach($offices as $office)
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
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Allocated Amount') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allocations as $alloc)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $alloc->economicCode->code }}</span></td>
                                        <td>{{ $alloc->economicCode->name }}</td>
                                        <td class="text-end fw-bold text-success">৳ {{ number_format($alloc->amount, 2) }}</td>
                                        <td>
                                            <button wire:click="viewHistory({{ $alloc->economic_code_id }})" class="btn btn-sm btn-info waves-effect waves-light">
                                                <i class="bx bx-history me-1"></i> {{ __('View History') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">{{ __('No allocations found for the selected criteria.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($showLogModal)
        <div class="modal-backdrop fade show"></div>
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Budget Approval History') }}</h5>
                        <button wire:click="closeLogModal()" type="button" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Action At') }}</th>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('To') }}</th>
                                        <th>{{ __('Action By') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Remarks') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedLog as $log)
                                        <tr>
                                            <td>{{ $log['action_at'] }}</td>
                                            <td><span class="badge bg-secondary">{{ __($log['from_stage']) }}</span></td>
                                            <td><span class="badge bg-info">{{ __($log['to_stage']) }}</span></td>
                                            <td>{{ $log['action_name'] }}</td>
                                            <td><small>{{ $log['action_role'] }}</small></td>
                                            <td>{{ $log['remarks'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeLogModal()" type="button" class="btn btn-secondary text-white">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
