<div class="unitoffice-entry-table budget-status-view">
    <style>
        .budget-status-view .page-title-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border-bottom: 2px solid #e2e8f0;
        }
        .budget-status-view .card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-radius: 12px;
            overflow: hidden;
        }
        .budget-status-view .filter-card {
            background: #ffffff;
            margin-bottom: 1.5rem;
        }
        .budget-status-view .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
        }
        .budget-status-view .table tbody td {
            padding: 16px;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        .budget-status-view .table tbody tr:last-child td {
            border-bottom: none;
        }
        .budget-status-view .table tbody tr:hover {
            background-color: #f8fafc;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-badge i { font-size: 1rem; }
        .badge-soft-success { background-color: #dcfce7; color: #166534; }
        .badge-soft-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-soft-primary { background-color: #dbeafe; color: #1e40af; }
        .badge-soft-info { background-color: #e0f2fe; color: #075985; }
        
        .timeline-modal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .timeline-modal .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
            border-left: 2px solid #e2e8f0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
        }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-item.active::before {
            background: #10b981;
            box-shadow: 0 0 0 2px #d1fae5;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 font-size-18 text-dark fw-bold">{{ __('Budget Status & History') }}</h4>
                    <p class="text-muted mb-0 font-size-13">Track the lifecycle of budget estimations and allocations across offices.</p>
                </div>
                <div class="page-title-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);" class="text-secondary">{{ __('Budgeting') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Status') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-semibold small text-uppercase">{{ __('Fiscal Year') }}</label>
                            <select wire:model.live="fiscal_year_id" class="form-select border-light bg-light shadow-sm">
                                @foreach ($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->bn_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-semibold small text-uppercase">{{ __('Budget Type') }}</label>
                            <select wire:model.live="budget_type_id" class="form-select border-light bg-light shadow-sm">
                                @foreach ($budgetTypes as $type)
                                    <option value="{{ $type->id }}">{{ __($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (auth()->user()->can('view-all-offices-data'))
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-semibold small text-uppercase">{{ __('Office') }}</label>
                                <select wire:model.live="rpo_unit_id" class="form-select border-light bg-light shadow-sm">
                                    @foreach ($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th width="20%">{{ __('Economic Code') }}</th>
                                    <th>{{ __('Origin') }}</th>
                                    <th class="text-end">{{ __('Demand') }}</th>
                                    <th class="text-end">{{ __('Approved') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th>{{ __('Current Node') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estimations as $est)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-10">
                                                        {{ substr($est->economicCode->code, -2) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-size-14 text-dark">{{ bn_num($est->economicCode->code) }}</h6>
                                                    <small class="text-muted">{{ Str::limit($est->economicCode->name, 30) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $est->office->name }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold">{{ bn_comma_format($est->amount_demand, 0) }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if($est->amount_approved > 0)
                                                <span class="text-success fw-bold">{{ bn_comma_format($est->amount_approved, 0) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = match($est->status) {
                                                    'approved' => 'badge-soft-success',
                                                    'rejected' => 'badge-soft-danger',
                                                    'draft' => 'badge-soft-warning',
                                                    default => 'badge-soft-info'
                                                };
                                                $statusIcon = match($est->status) {
                                                    'approved' => 'bx-check-circle',
                                                    'rejected' => 'bx-x-circle',
                                                    'draft' => 'bx-edit',
                                                    default => 'bx-time-five'
                                                };
                                                $statusLabel = match($est->status) {
                                                    'approved' => 'Released',
                                                    'rejected' => 'Rejected',
                                                    'draft' => 'Draft',
                                                    default => 'Submitted'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $statusClass }}">
                                                <i class="bx {{ $statusIcon }}"></i>
                                                {{ __($statusLabel) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($est->status === 'approved')
                                                <span class="text-success fw-semibold">{{ __('Completed') }}</span>
                                            @elseif($est->targetOffice)
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-primary">{{ $est->targetOffice->name }}</span>
                                                    @if($est->workflowStep)
                                                        <small class="text-muted text-uppercase font-size-10">{{ __($est->workflowStep->name) }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button wire:click="viewHistory({{ $est->id }})" class="btn btn-sm btn-light text-primary rounded-pill px-3 hover-shadow">
                                                <i class="bx bx-history me-1"></i> {{ __('Log') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <div class="avatar-lg mx-auto mb-3">
                                                    <span class="avatar-title rounded-circle bg-light">
                                                        <i class="bx bx-search-alt-2 font-size-36 text-muted"></i>
                                                    </span>
                                                </div>
                                                <h5 class="text-muted">{{ __('No Budget Records Found') }}</h5>
                                                <p class="text-muted mb-0">{{ __('Adjust filters to see more results.') }}</p>
                                            </div>
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
        <div class="modal-backdrop fade show" style="background-color: rgba(15, 23, 42, 0.6);"></div>
        <div class="modal fade show timeline-modal" tabindex="-1" style="display: block;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center justify-content-between">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-git-branch text-primary me-2"></i>{{ __('Audit Trail') }}
                        </h5>
                        <button wire:click="closeLogModal()" type="button" class="btn-close opacity-50"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                @if(empty($selectedLog))
                                    <div class="text-center py-4 text-muted">
                                        {{ __('No history available.') }}
                                    </div>
                                @else
                                    <div class="timeline ps-2">
                                        @foreach($selectedLog as $log)
                                            <div class="timeline-item active">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1 text-dark fw-bold">{{ $log['action_name'] }}</h6>
                                                        <p class="text-muted small mb-0">{{ $log['action_role'] }}</p>
                                                    </div>
                                                    <span class="badge bg-white text-muted border border-light shadow-sm">
                                                        <i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($log['action_at'])->format('d M, h:i A') }}
                                                    </span>
                                                </div>
                                                
                                                <div class="card border border-light bg-light mb-2">
                                                    <div class="card-body p-2 d-flex justify-content-between font-size-13">
                                                        <div>
                                                            <span class="text-muted">{{ __('Moved:') }}</span>
                                                            <span class="fw-semibold">{{ __($log['from_stage'] ?? 'N/A') }}</span>
                                                            <i class="bx bx-right-arrow-alt mx-1 text-secondary"></i>
                                                            <span class="fw-semibold text-primary">{{ __($log['to_stage']) }}</span>
                                                        </div>
                                                        @if(isset($log['amount_demand']) || isset($log['amount_approved']))
                                                            <div>
                                                                @if(isset($log['amount_demand']))
                                                                    <span class="me-2" title="Demand"><i class="bx bx-money me-1"></i>{{ bn_num($log['amount_demand']) }}</span>
                                                                @endif
                                                                @if(isset($log['amount_approved']))
                                                                    <span class="text-success" title="Approved"><i class="bx bx-check-circle me-1"></i>{{ bn_num($log['amount_approved']) }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(!empty($log['remarks']))
                                                    <div class="bg-soft-warning p-2 rounded start-2 border border-warning-subtle text-warning-emphasis font-size-13">
                                                        <i class="bx bxs-quote-alt-left me-1 opacity-50"></i> {{ $log['remarks'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
