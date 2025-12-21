<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Approvals') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Approvals') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($viewMode === 'inbox')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">{{ __('Pending Submissions from Sub-Offices') }}</h4>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Office Name') }}</th>
                                        <th>{{ __('Budget Type') }}</th>
                                        <th>{{ __('Total Demand') }}</th>
                                        <th>{{ __('Current Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($childOffices as $officeData)
                                        @php
                                            $uniqueKey = $officeData['id'] . '_' . str_replace(' ', '_', $officeData['budget_type_id']);
                                        @endphp
                                        <tr>
                                            <td>{{ $officeData['name'] }} <br><small class="text-muted">{{ $officeData['code'] }}</small></td>
                                            <td><span class="badge badge-soft-info">{{ $officeData['budget_type_id'] }}</span></td>
                                            <td><strong>৳ {{ number_format($officeData['total_demand'], 2) }}</strong></td>
                                            <td>
                                                @php
                                                    $badgeClass = match($officeData['status']) {
                                                        'submitted' => 'primary',
                                                        'district_approved' => 'info',
                                                        'hq_approved' => 'success',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'warning'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">{{ __(ucfirst(str_replace('_', ' ', $officeData['status']))) }}</span>
                                            </td>
                                            <td>
                                                <button wire:click="viewDetails({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}')" class="btn btn-sm btn-info waves-effect waves-light">
                                                    <i class="bx bx-search-alt me-1"></i> {{ __('Review') }}
                                                </button>
                                                
                                                <button onclick="confirmApproval({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}')" class="btn btn-sm btn-success waves-effect waves-light">
                                                    <i class="bx bx-check me-1"></i> {{ __('Approve') }}
                                                </button>

                                                <button onclick="promptRejection({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}')" class="btn btn-sm btn-danger waves-effect waves-light">
                                                    <i class="bx bx-x me-1"></i> {{ __('Reject') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">{{ __('No pending submissions found for this fiscal year.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <button wire:click="backToInbox" class="btn btn-outline-secondary btn-sm mb-3">
                                    <i class="bx bx-arrow-back"></i> {{ __('Back to Inbox') }}
                                </button>
                                <h4 class="card-title">{{ __('Reviewing') }}: {{ $office->name }}</h4>
                                <p class="text-muted mb-0">{{ __('Detailed Economic Code Breakdown') }}</p>
                            </div>
                            <div class="text-end">
                                <button onclick="confirmApproval({{ $office->id }}, '{{ $selected_budget_type_id }}')" class="btn btn-success btn-rounded px-4">
                                    <i class="bx bx-check-double me-1"></i> {{ __('Approve Entire Budget') }}
                                </button>
                                <button onclick="promptRejection({{ $office->id }}, '{{ $selected_budget_type_id }}')" class="btn btn-danger btn-rounded px-4 ms-2">
                                    <i class="bx bx-x me-1"></i> {{ __('Reject All') }}
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th style="width: 15%;">{{ __('Demand Amount') }}</th>
                                        <th style="width: 20%;">{{ __('Approved Amount (Adjustment)') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demands as $codeId => $data)
                                        <tr>
                                            <td><strong>{{ $data['code'] }}</strong></td>
                                            <td>{{ $data['name'] }}</td>
                                            <td>৳ {{ number_format($data['demand'], 2) }}</td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">৳</span>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           value="{{ $data['approved'] }}"
                                                           onchange="@this.updateAdjustment({{ $data['id'] }}, this.value)">
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $data['status'] === 'rejected' ? 'danger' : 'secondary' }}">
                                                    {{ __(ucfirst($data['status'])) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function confirmApproval(id, type) {
            Swal.fire({
                title: '{{ __("Confirm Approval") }}',
                text: "{{ __('Approve') }} " + type + " {{ __('for this office?') }}",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#34c38f',
                cancelButtonColor: '#f46a6a',
                confirmButtonText: '{{ __("Yes, approve it!") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.approve(id, type);
                }
            })
        }

        async function promptRejection(id, type) {
            const { value: text } = await Swal.fire({
                title: '{{ __("Reason for Rejection") }} (' + type + ')',
                input: 'textarea',
                inputPlaceholder: '{{ __("Enter your remarks here...") }}',
                showCancelButton: true,
                confirmButtonColor: '#f46a6a',
                confirmButtonText: '{{ __("Reject") }}'
            })

            if (text) {
                // Formatting key to match PHP key generation: $officeId . '_' . $budgetTypeId
                let key = id + '_' + type;
                @this.set('remarks.' + key, text);
                @this.reject(id, type);
            }
        }
    </script>
</div>
