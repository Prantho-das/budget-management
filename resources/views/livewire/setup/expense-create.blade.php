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
                <h4 class="mb-sm-0 font-size-18">Create New Expense</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('setup.expenses') }}" wire:navigate>Expenses</a></li>
                        <li class="breadcrumb-item active">Create New</li>
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
                            <form wire:submit.prevent="store">
                                {{-- Form Header Section --}}
                                <div class="voucher-header bg-light border-bottom p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-2 text-primary fw-bold">মাসিক ব্যয় বিবরণী / Monthly Expense Statement</h5>
                                            <div class="row g-2 small">
                                                <div class="col-md-4">
                                                    <strong>Office:</strong> 
                                                    @if($rpo_unit_id)
                                                        @php $office = \App\Models\RpoUnit::find($rpo_unit_id); @endphp
                                                        {{ $office->name ?? 'N/A' }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Fiscal Year:</strong>
                                                    @if($fiscal_year_id)
                                                        @php $fy = \App\Models\FiscalYear::find($fiscal_year_id); @endphp
                                                        {{ $fy->name ?? 'N/A' }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Month:</strong>
                                                    @if($selectedMonth)
                                                        {{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }}
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="small">
                                                <div><strong>Entry By:</strong> {{ auth()->user()->name }}</div>
                                                <div><strong>Date:</strong> {{ date('d-M-Y') }}</div>
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
                                            <select class="form-select form-select-sm" id="selectedMonth" wire:model.live="selectedMonth">
                                                <option value="">Select Month</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                            @error('selectedMonth') <span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fiscal_year_id" class="form-label fw-semibold small mb-1">
                                                Fiscal Year <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm" id="fiscal_year_id" wire:model.live="fiscal_year_id">
                                                <option value="">Select Year</option>
                                                @foreach($fiscalYears as $year)
                                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('fiscal_year_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="budget_type_id" class="form-label fw-semibold small mb-1">
                                                Budget Type
                                            </label>
                                            <select class="form-select form-select-sm" id="budget_type_id" wire:model.live="budget_type_id">
                                                @foreach($budgetTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @if(auth()->check())
                                        <input type="hidden" wire:model="rpo_unit_id" value="{{ auth()->user()->rpo_unit_id }}">
                                    @endif
                                </div>
                                
                                @if($rpo_unit_id && $fiscal_year_id)
                                    {{-- Expense Entry Table --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover mb-0 expense-entry-table">
                                            <thead class="table-primary">
                                                <tr class="text-center align-middle">
                                                    <th style="width: 40px;" class="small">ক্রমিক নং<br>#</th>
                                                    <th style="width: 80px;" class="small">অর্থনৈতিক কোড<br>Code</th>
                                                    <th class="small">বিবরণ / অর্থনৈতিক খাতের নাম<br>Economic Head</th>
                                                    <th style="width: 110px;" class="small">বাজেট<br>Budget</th>
                                                    <th style="width: 110px;" class="small">গত মাস পর্যন্ত ব্যয়<br>Prev. Total</th>
                                                    <th style="width: 110px;" class="small">বর্তমান মাসের ব্যয়<br>This Month</th>
                                                    <th style="width: 110px;" class="small">সর্বমোট ব্যয়<br>Total Expense</th>
                                                    <th style="width: 110px;" class="small">উদ্বৃত্ত<br>Balance</th>
                                                    <th style="width: 180px;" class="small">মন্তব্য<br>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $serialNo = 1; @endphp
                                                @foreach($economicCodes as $code)
                                                    <tr class="{{ $code->parent_id == null ? 'table-secondary fw-bold' : '' }}">
                                                        {{-- Serial Number --}}
                                                        <td class="text-center small">
                                                            @php
                                                                $hasChildren = collect($economicCodes)->where('parent_id', $code->id)->count() > 0;
                                                            @endphp
                                                            @if(!$hasChildren)
                                                                {{ $serialNo++ }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        
                                                        {{-- Economic Code --}}
                                                        <td class="text-center">
                                                            <span class="badge {{ $code->parent_id ? 'bg-info text-white' : 'bg-dark' }} font-monospace">
                                                                {{ $code->code }}
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
                                                                     // Fetch budget allocation for this code
                                                                    $budgetAllocation = \App\Models\BudgetAllocation::where([
                                                                        'economic_code_id' => $code->id,
                                                                        'rpo_unit_id' => $rpo_unit_id,
                                                                        'fiscal_year_id' => $fiscal_year_id,
                                                                    ])->sum('amount');
                                                                @endphp
                                                                <span class="font-monospace small {{ $budgetAllocation > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                                                                    {{ number_format($budgetAllocation, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- Previous Total (Up to last month) --}}
                                                            <td class="text-end">
                                                                @php
                                                                    $previousTotal = \App\Models\Expense::where([
                                                                        'economic_code_id' => $code->id,
                                                                        'rpo_unit_id' => $rpo_unit_id,
                                                                        'fiscal_year_id' => $fiscal_year_id,
                                                                    ])
                                                                    ->where('date', '<', date('Y') . '-' . $selectedMonth . '-01')
                                                                    ->sum('amount');
                                                                @endphp
                                                                <span class="font-monospace small text-primary">
                                                                    {{ number_format($previousTotal, 2) }}
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
                                                                    {{ number_format($totalExpenditure, 2) }}
                                                                </span>
                                                            </td>
                                                            
                                                            {{-- Balance (Budget - Total Expenditure) --}}
                                                            <td class="text-end">
                                                                @php
                                                                    $balance = $budgetAllocation - $totalExpenditure;
                                                                @endphp
                                                                <span class="font-monospace small {{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted') }} fw-semibold">
                                                                    {{ number_format($balance, 2) }}
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
                                                                {{ $code->parent_id == null ? 'Parent Head - No Direct Entry' : 'Sub-Head - No Direct Entry' }}
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light border-top-2">
                                                <tr class="fw-bold">
                                                    <td colspan="3" class="text-end">সর্বমোট / Total:</td>
                                                    
                                                    {{-- Total Budget --}}
                                                    <td class="text-end font-monospace text-info">
                                                        @php
                                                            $totalBudget = \App\Models\BudgetAllocation::where([
                                                                'rpo_unit_id' => $rpo_unit_id,
                                                                'fiscal_year_id' => $fiscal_year_id,
                                                              ])->sum('amount');
                                                        @endphp
                                                        {{ number_format($totalBudget, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Previous --}}
                                                    <td class="text-end font-monospace text-primary">
                                                        @php
                                                            $totalPrevious = \App\Models\Expense::where([
                                                                'rpo_unit_id' => $rpo_unit_id,
                                                                'fiscal_year_id' => $fiscal_year_id,
                                                            ])
                                                            ->where('date', '<', date('Y') . '-' . $selectedMonth . '-01')
                                                            ->sum('amount');
                                                        @endphp
                                                        {{ number_format($totalPrevious, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total This Month (New Entries) --}}
                                                    <td class="text-end font-monospace text-success">
                                                        @php
                                                            $totalThisMonth = 0;
                                                            foreach($expenseEntries as $entry) {
                                                                $totalThisMonth += floatval($entry['amount'] ?? 0);
                                                            }
                                                        @endphp
                                                        {{ number_format($totalThisMonth, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Expenditure --}}
                                                    <td class="text-end font-monospace text-success fw-bold">
                                                        @php
                                                            $totalExpenditure = $totalPrevious + $totalThisMonth;
                                                        @endphp
                                                        {{ number_format($totalExpenditure, 2) }}
                                                    </td>
                                                    
                                                    {{-- Total Balance --}}
                                                    <td class="text-end font-monospace fw-bold">
                                                        @php
                                                            $totalBalance = $totalBudget - $totalExpenditure;
                                                        @endphp
                                                        <span class="{{ $totalBalance < 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($totalBalance, 2) }}
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
                                                <strong>Action Required</strong>: Please select Month and Fiscal Year to start entering expenses.
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
                                        <button wire:click="cancel" type="button" class="btn btn-secondary btn-sm px-4">
                                            <i class="bx bx-x me-1"></i>Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-sm px-4">
                                            <i class="bx bx-save me-1"></i> Save Expenses
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
