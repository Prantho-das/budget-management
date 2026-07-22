<div>
    @if($viewMode === 'detail')
    <style>
        /* Screen styles: Hide print layout on screen */
        #formal-print-report {
            display: none;
        }

        /* Print styles */
        @media print {
            /* Hide all regular dashboard/screen elements */
            body * {
                visibility: hidden;
            }
            #formal-print-report, #formal-print-report * {
                visibility: visible;
            }
            #formal-print-report {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            /* Reset container widths and margins */
            body, html {
                background: #fff !important;
                color: #000 !important;
                font-size: 13px !important;
                font-family: 'SolaimanLipi', 'Nikosh', sans-serif !important;
            }

            /* Table styling matching the PDF */
            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
            }

            .print-table th, .print-table td {
                border: 1px solid #000 !important;
                padding: 5px 8px !important;
                vertical-align: middle !important;
            }

            .print-table th {
                background-color: #f2f2f2 !important;
                font-weight: bold !important;
                text-align: center !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-center {
                text-align: center !important;
            }

            .text-end {
                text-align: right !important;
            }

            .fw-bold {
                font-weight: bold !important;
            }

            .mb-0 {
                margin-bottom: 0 !important;
            }

            .mt-1 {
                margin-top: 4px !important;
            }

            .header-title-block {
                text-align: center !important;
                margin-bottom: 25px !important;
                line-height: 1.5 !important;
            }

            .header-title-block h3 {
                font-size: 18px !important;
                font-weight: bold !important;
                margin: 0 0 5px 0 !important;
            }

            .header-title-block h4 {
                font-size: 16px !important;
                margin: 0 0 5px 0 !important;
                font-weight: normal !important;
            }

            .header-title-block p {
                margin: 0 !important;
                font-size: 14px !important;
            }
        }
    </style>
    @endif
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
                                        <th>{{ __('Submission Date') }}</th>
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
                                            <td>{{ $officeData['name'] }} <br><small class="text-muted">{{ bn_num($officeData['code']) }}</small></td>
                                            <td><span class="badge badge-soft-info">{{ $officeData['budget_type_name'] }}</span></td>
                                            <td><strong>{{ bn_comma_format($officeData['total_demand'], 2) }}</strong></td>
                                            <td><small class="text-muted">{{ bn_num(date('d', strtotime($officeData['created_at']))) }} {{ __(date('M', strtotime($officeData['created_at']))) }} {{ bn_num(date('Y', strtotime($officeData['created_at']))) }}, {{ bn_num(date('h:i A', strtotime($officeData['created_at']))) }}</small></td>
                                            <td>
                                                <span class="badge badge-soft-primary font-size-12">{{ __($officeData['current_stage']) }}</span>
                                                @if($officeData['is_drafted'])
                                                    <span class="badge badge-soft-warning ms-1" title="{{ __('Has draft adjustments') }}"><i class="bx bx-edit-alt"></i> {{ __('Drafted') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="viewDetails({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}', '{{ $officeData['current_stage'] }}', '{{ $officeData['batch_id'] }}')" class="btn btn-sm btn-info waves-effect waves-light">
                                                    <i class="bx bx-search-alt me-1"></i> {{ __('Review') }}
                                                </button>
                                                
                                                <button onclick="confirmApproval({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}', '{{ $officeData['current_stage'] }}', '{{ $officeData['batch_id'] }}')" class="btn btn-sm btn-success waves-effect waves-light">
                                                    <i class="bx bx-check me-1"></i> {{ __('Approve') }}
                                                </button>
 
                                                <button onclick="promptRejection({{ $officeData['id'] }}, '{{ $officeData['budget_type_id'] }}', '{{ $officeData['current_stage'] }}', '{{ $officeData['batch_id'] }}')" class="btn btn-sm btn-danger waves-effect waves-light">
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
                                <button onclick="window.print()" class="btn btn-secondary btn-rounded px-4">
                                    <i class="bx bx-printer me-1"></i> {{ __('Print') }}
                                </button>
                                <button wire:click="saveAsDraft" class="btn btn-warning btn-rounded px-4 ms-2">
                                    <i class="bx bx-save me-1"></i> {{ __('Save as Draft') }}
                                </button>
                                <button onclick="confirmApproval({{ $office->id }}, '{{ $selected_budget_type_id }}', '{{ $selected_stage }}', '{{ $selected_batch_id }}')" class="btn btn-success btn-rounded px-4 ms-2">
                                    <i class="bx bx-check-double me-1"></i> {{ __('Approve Entire Budget') }}
                                </button>
                                <button onclick="promptRejection({{ $office->id }}, '{{ $selected_budget_type_id }}', '{{ $selected_stage }}', '{{ $selected_batch_id }}')" class="btn btn-danger btn-rounded px-4 ms-2">
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
                                        @foreach($prevYears as $yearName)
                                            <th class="text-center">{{ __('Exp') }}<br><small>{{ $yearName }}</small></th>
                                        @endforeach
                                        <th style="width: 12%;">{{ __('Demand Amount') }}</th>
                                        <th style="width: 15%;">{{ __('Requester Remarks') }}</th>
                                        <th style="width: 18%;">{{ __('Approved Amount') }}</th>
                                        <th style="width: 18%;">{{ __('Approver Remarks') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demands as $codeId => $data)
                                        <tr>
                                            <td><strong>{{ bn_num($data['code']) }}</strong></td>
                                            <td>{{ $data['name'] }}</td>
                                            @for($i = 0; $i < 3; $i++)
                                                <td class="text-end">
                                                    @if(isset($previousDemands[$codeId]["year_{$i}"]))
                                                        <strong class="text-info">{{ bn_comma_format($previousDemands[$codeId]["year_{$i}"]['amount'], 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endfor
                                            <td>{{ bn_comma_format($data['demand'], 2) }}</td>
                                            <td><small>{{ $data['remarks'] ?: '-' }}</small></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"></span>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           value="{{ $data['approved'] }}"
                                                           onchange="@this.updateAdjustment({{ $data['id'] }}, this.value)"
                                                           @cannot('edit-budget-approval-amount') readonly @endcannot>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       wire:model.debounce.500ms="approval_remarks.{{ $data['id'] }}"
                                                       placeholder="{{ __('Approver Remarks') }}"
                                                       onchange="@this.updateAdjustment({{ $data['id'] }}, null, this.value)"
                                                       @cannot('edit-budget-approval-amount') readonly @endcannot>
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
        function confirmApproval(id, type, stage, batch) {
            Swal.fire({
                title: '{{ __("Confirm Approval") }}',
                text: "{{ __('Approve') }} " + type + " (" + stage + ") {{ __('for this office?') }}",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#34c38f',
                cancelButtonColor: '#f46a6a',
                confirmButtonText: '{{ __("Yes, approve it!") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.approve(id, type, stage, batch);
                }
            })
        }

        async function promptRejection(id, type, stage, batch) {
            const { value: text } = await Swal.fire({
                title: '{{ __("Reason for Rejection") }} (' + type + ' - ' + stage + ')',
                input: 'textarea',
                inputPlaceholder: '{{ __("Enter your remarks here...") }}',
                showCancelButton: true,
                confirmButtonColor: '#f46a6a',
                confirmButtonText: '{{ __("Reject") }}'
            })

            if (text) {
                let key = id + '_' + type + '_' + stage.replace(/ /g, '_') + '_' + batch;
                @this.set('remarks.' + key, text);
                @this.reject(id, type, stage, batch);
            }
        }
    </script>

    @if($viewMode === 'detail' && $office)
    <!-- START OF FORMAL PRINT REPORT LAYOUT -->
    <div id="formal-print-report">
        <div class="header-title-block">
            <h3>গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</h3>
            <h4>ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর</h4>
            <p>ই-৭, আগারগাঁও, ঢাকা</p>
            <p class="mt-1 fw-bold">অফিসের নাম: {{ $office->name ?? '' }}@if(isset($office->code)); অফিস কোড নং-{{ bn_num($office->code) }}@endif</p>
            <p class="fw-bold">{{ bn_num($selectedFiscalYear->name ?? '') }} অর্থবছরের বাজেট বরাদ্দ (বরাদ্দ নং ১)</p>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 20%;">অর্থনৈতিক গ্রুপ/কোড</th>
                    <th style="width: 45%;">বিবরণ</th>
                    <th style="width: 17%; text-align: right;">পরিমাণ (হাজার টাকায়)</th>
                    <th style="width: 18%; text-align: right;">মোট (হাজার টাকায়)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotalInThousands = 0;
                @endphp
                @forelse($groupedDemands as $groupCode => $group)
                    @php
                        $subtotalInThousands = $group['subtotal_approved'];
                        $grandTotalInThousands += $subtotalInThousands;
                    @endphp
                    <!-- Group Header Row -->
                    <tr class="fw-bold">
                        <td>{{ bn_num($group['group_code']) }}</td>
                        <td>{{ $group['group_name'] }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    
                    <!-- Child Items Rows -->
                    @foreach($group['items'] as $item)
                        @php
                            $amountInThousands = $item['approved'];
                        @endphp
                        <tr>
                            <td style="padding-left: 20px;">{{ bn_num($item['code']) }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-end">{{ $amountInThousands > 0 ? bn_comma_format($amountInThousands, 0) : '০' }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    <!-- Subtotal Row -->
                    <tr class="fw-bold">
                        <td></td>
                        <td class="text-end">উপমোট</td>
                        <td></td>
                        <td class="text-end">{{ $subtotalInThousands > 0 ? bn_comma_format($subtotalInThousands, 0) : '০' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">কোনো বাজেট বরাদ্দ পাওয়া যায়নি।</td>
                    </tr>
                @endforelse

                <!-- Grand Total Row -->
                @if(count($groupedDemands) > 0)
                    <tr class="fw-bold">
                        <td class="text-center">সর্বমোট</td>
                        <td></td>
                        <td class="text-end">{{ bn_comma_format($grandTotalInThousands, 0) }}</td>
                        <td class="text-end">{{ bn_comma_format($grandTotalInThousands, 0) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <!-- END OF FORMAL PRINT REPORT LAYOUT -->
    @endif
</div>
