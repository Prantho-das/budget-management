<div>

<style>
    .expense-voucher-form {
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .voucher-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-bottom: 3px solid #0d6efd !important;
    }
    
    .expense-entry-table {
        font-size: 0.9rem;
    }
    
    .expense-entry-table thead th {
        background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%) !important;
        color: white !important;
        font-weight: 600;
        border: 1px solid #0056b3 !important;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    .expense-entry-table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .expense-entry-table tbody tr:not(.table-secondary):hover {
        background-color: #f8f9fa;
    }
    
    .expense-entry-table tbody td {
        padding: 10px 8px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    
    .expense-entry-table .table-secondary {
        background-color: #e9ecef !important;
    }
    
    .expense-entry-table .table-secondary td {
        font-weight: 600;
        color: #212529;
        border-top: 2px solid #adb5bd;
        border-bottom: 2px solid #adb5bd;
    }
    
    .expense-entry-table tfoot {
        border-top: 3px solid #0d6efd !important;
    }
    
    .expense-entry-table tfoot td {
        padding: 12px 8px;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
    }
    
    .expense-entry-table input[type="number"],
    .expense-entry-table input[type="text"] {
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .expense-entry-table input[type="number"]:focus,
    .expense-entry-table input[type="text"]:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    @media print {
        .expense-voucher-form {
            box-shadow: none;
            border: 2px solid #000;
        }
        
        .btn, .breadcrumb {
            display: none !important;
        }
        
        .expense-entry-table input {
            border: none !important;
            background: transparent !important;
        }
    }
</style>

<div class="unitoffice-entry-table expense-table">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Create New Expense') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('setup.expenses') }}" wire:navigate>{{ __('Expenses') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Create New') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Expense Voucher Form --}}
                    <div class="expense-voucher-form card border shadow-sm mb-4">
                        <div class="card-body p-0">
                            <form wire:submit.prevent.stop="store">
                                @csrf
                                {{-- Form Header Section --}}
                                <div class="voucher-header bg-light border-bottom p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-2 text-primary fw-bold">{{ __('মাসিক ব্যয় বিবরণী / Monthly Expense Statement') }}</h5>
                                            <div class="row g-2 small">
                                                <div class="col-md-4">
                                                    <strong>{{ __('Office Group') }}:</strong> 
                                                    {{ $officeName ?? '-' }}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>{{ __('Fiscal Year') }}:</strong>
                                                    {{ bn_num($fiscalYearName) ?? '-' }}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>{{ __('Month') }}:</strong>
                                                    @if($selectedMonth)
                                                        {{ __(DateTime::createFromFormat('!m', $selectedMonth)->format('F')) }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="small">
                                                <div><strong>{{ __('Entry By') }}:</strong> {{ auth()->user()->name }}</div>
                                                <div><strong>{{ __('Date') }}:</strong> {{ bn_num(date('d')) }}-{{ __(date('M')) }}-{{ bn_num(date('Y')) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Selection Controls --}}
                                <div class="p-3 bg-light-subtle border-bottom">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label for="selectedMonth" class="form-label fw-semibold small mb-1">
                                                Select Month <span class="text-danger">*</span>
                                            </label>
                                                <select class="form-select form-select-sm shadow-sm border-primary" id="selectedMonth" wire:model.live="selectedMonth">
                                                    <option value="">{{ __('Select Month') }}</option>
                                                    @foreach(['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'] as $val => $label)
                                                        <option value="{{ $val }}" {{ $selectedMonth != $val && !$isDraftSaved ? 'disabled' : '' }} style="{{ $selectedMonth != $val && !$isDraftSaved ? 'color: #ccc;' : '' }}">
                                                            {{ __($label) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @error('selectedMonth') <span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fiscal_year_id" class="form-label fw-semibold small mb-1">
                                                Fiscal Year <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm shadow-sm border-primary" id="fiscal_year_id" wire:model.live="fiscal_year_id">
                                                <option value="">{{ __('Select Year') }}</option>
                                                @foreach($fiscalYears as $year)
                                                    <option value="{{ $year->id }}" {{ $fiscal_year_id != $year->id && !$isDraftSaved ? 'disabled' : '' }} style="{{ $fiscal_year_id != $year->id && !$isDraftSaved ? 'color: #ccc;' : '' }}">
                                                        {{ $year->bn_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('fiscal_year_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            @if($isHq || auth()->user()->hasRole('Admin'))
                                                <label for="rpo_unit_id" class="form-label fw-semibold small mb-1">
                                                    {{ __('Select Office Group') }} <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select form-select-sm" id="rpo_unit_id" wire:model.live="rpo_unit_id">
                                                    <option value="">{{ __('Select Office Group') }}</option>
                                                    @foreach($offices as $office)
                                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('rpo_unit_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                            @else
                                                <label class="form-label fw-semibold small mb-1">{{ __('Office Group') }}</label>
                                                <div class="form-control form-control-sm bg-light">
                                                    {{ auth()->user()->office->name ?? 'N/A' }}
                                                </div>
                                                <input type="hidden" wire:model="rpo_unit_id">
                                            @endif
                                        </div>
                                </div>
                                
                                @if($rpo_unit_id && $fiscal_year_id)
                                    {{-- Expense Entry Table --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover mb-0 expense-entry-table">
                                            <thead class="table-primary">
                                                <tr class="text-center align-middle">
                                                    <th style="width: 40px;" class="small d-none">{{ __('SL') }}</th>
                                                    <th style="width: 80px;" class="small">{{ __('Economic Code') }}</th>
                                                    <th class="small">{{ __('Economic Head') }}</th>
                                                    <th style="width: 110px;" class="small">{{ __('Budget') }}</th>
                                                    <th style="width: 110px;" class="small">{{ __('Prev. Total') }}</th>
                                                    <th style="width: 110px;" class="small">{{ __('Current Month') }}</th>
                                                    <th style="width: 110px;" class="small">{{ __('Total Expense') }}</th>
                                                    <th style="width: 110px;" class="small">{{ __('Balance') }}</th>
                                                    <th style="width: 180px;" class="small">{{ __('Remarks') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $serialNo = 1; @endphp
                                                @foreach($economicCodes as $code)
                                                    <tr wire:key="economic-code-{{ $code->id }}" class="{{ $code->parent_id == null ? 'table-secondary fw-bold' : '' }}">
                                                        {{-- Serial Number --}}
                                                        <td class="text-center small d-none">
                                                            @php
                                                                $hasChildren = collect($economicCodes)->where('parent_id', $code->id)->count() > 0;
                                                            @endphp
                                                            @if(!$hasChildren)
                                                                {{ bn_num($serialNo++) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        
                                                        {{-- Economic Code --}}
                                                        <td class="text-center">
                                                            <span class="badge {{ $code->parent_id ? 'bg-info text-white' : 'bg-dark' }} font-monospace">
                                                                {{ bn_num($code->code) }}
                                                            </span>
                                                        </td>
                                                        
                                                        {{-- Economic Head Name --}}
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($code->parent_id == null)
                                                                    <i class="bx bx-folder text-primary me-2"></i>
                                                                @else
                                                                    <i class="bx bx-file text-muted me-2"></i>
                                                                @endif
                                                                <span class="{{ $code->parent_id == null ? 'fw-bold text-uppercase' : '' }}">
                                                                    {{ $code->name }}
                                                                </span>
                                                                @if($code->description && $code->parent_id)
                                                                    <i class="bx bx-info-circle text-muted ms-1" title="{{ $code->description }}"></i>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        
                                                        @php
                                                            // Only allow input for codes with no children (third layer)
                                                            $hasChildren = collect($economicCodes)->where('parent_id', $code->id)->count() > 0;
                                                            $canHaveExpense = !$hasChildren;
                                                        @endphp
                                                        
                                                        @if($canHaveExpense)
                                                            {{-- Budget Allocation --}}
                                                            <td class="text-end">
                                                                @php
                                                                     $budgetAllocation = $budgetAllocations[$code->id] ?? 0;
                                                                @endphp
                                                                <span class="font-monospace small {{ $budgetAllocation > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                                                                    {{ bn_comma_format($budgetAllocation, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- Previous Total (Up to last month) --}}
                                                            <td class="text-end">
                                                                @php
                                                                    $previousTotal = $previousExpenses[$code->id] ?? 0;
                                                                @endphp
                                                                <span class="font-monospace small text-primary">
                                                                    {{ bn_comma_format($previousTotal, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- This Month (Current Input/Spent) --}}
                                                            <td>
                                                                    <input type="number" 
                                                                           step="0.01" 
                                                                           class="form-control form-control-sm text-end font-monospace" 
                                                                           wire:model="expenseEntries.{{ $code->id }}.amount" 
                                                                           placeholder="0.00">
                                                            </td>
                                                            
                                                            {{-- Total Expenditure (Previous + This Month) --}}
                                                            <td class="text-end">
                                                                @php
                                                                    $thisMonth = floatval($expenseEntries[$code->id]['amount'] ?? 0);
                                                                    $totalExpenditure = $previousTotal + $thisMonth;
                                                                @endphp
                                                                <span class="font-monospace small text-success fw-semibold">
                                                                    {{ bn_comma_format($totalExpenditure, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- Balance (Budget - Total Expenditure) --}}
                                                            <td class="text-end">
                                                                @php
                                                                    $balance = $budgetAllocation - $totalExpenditure;
                                                                @endphp
                                                                <span class="font-monospace small {{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted') }} fw-semibold">
                                                                    {{ bn_comma_format($balance, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- Remarks --}}
                                                            <td>
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           wire:model="expenseEntries.{{ $code->id }}.description" 
                                                                           placeholder="Notes...">
                                                            </td>
                                                        @else
                                                                 <td colspan="7" class="text-center fst-italic text-muted small bg-light">
                                                                {{ $code->parent_id == null ? __('Parent Head - No Direct Entry') : __('Sub-Head - No Direct Entry') }}
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light border-top-2">
                                                <tr class="fw-bold">
                                                    <td colspan="2" class="text-end">{{ __('Total') }}:</td>
                                                    
                                                    {{-- Total Budget --}}
                                                    <td class="text-end font-monospace text-info">
                                                        {{ bn_comma_format($totalBudget, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Previous --}}
                                                    <td class="text-end font-monospace text-primary">
                                                        {{ bn_comma_format($totalPrevious, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total This Month (New Entries) --}}
                                                    <td class="text-end font-monospace text-success">
                                                        @php
                                                            $totalThisMonth = 0;
                                                            foreach($expenseEntries as $entry) {
                                                                $totalThisMonth += floatval($entry['amount'] ?? 0);
                                                            }
                                                        @endphp
                                                        {{ bn_comma_format($totalThisMonth, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Expenditure --}}
                                                    <td class="text-end font-monospace text-success fw-bold">
                                                        @php
                                                            $totalExpenditure = $totalPrevious + $totalThisMonth;
                                                        @endphp
                                                        {{ bn_comma_format($totalExpenditure, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Balance --}}
                                                    <td class="text-end font-monospace fw-bold">
                                                        @php
                                                            $totalBalance = $totalBudget - $totalExpenditure;
                                                        @endphp
                                                        <span class="{{ $totalBalance < 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ bn_comma_format($totalBalance, 2) }}
                                                        </span>
                                                    </td>
                                                    
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-5">
                                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="bx bx-error-circle font-size-24 me-2"></i>
                                            <div>
                                                <strong>{{ __('Action Required') }}</strong>: {{ __('Please select Month and Fiscal Year to start entering expenses.') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Form Footer --}}
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top">
                                    <div class="small text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        All amounts should be entered in BDT (৳)
                                    </div>
                                    <div class="d-flex gap-2">
                                            <button type="submit"
                                            class="btn btn-primary btn-sm px-4"
                                            wire:loading.attr="disabled">
                                                <i class="bx bx-save me-1" wire:loading.remove></i>
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" wire:loading></span>
                                                {{ $isDraftSaved ? __('Update Draft') : __('Save Expenses') }}
                                            </button>
                                        @if($isDraftSaved)
                                            <button type="button" 
                                            onclick="confirmSubmission()"
                                            class="btn btn-success btn-sm px-4"
                                            wire:loading.attr="disabled">
                                                <i class="bx bx-check-circle me-1" wire:loading.remove></i>
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" wire:loading></span>
                                                {{ __('Submit Expenses') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmSubmission() {
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('Once submitted, you will not be able to edit this draft!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#34c38f',
                cancelButtonColor: '#f46a6a',
                confirmButtonText: "{{ __('Yes, Submit it!') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.submitFinal();
                }
            })
        }
    </script>
    @endpush
</div>

    
</div>