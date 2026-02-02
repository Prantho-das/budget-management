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
                <h4 class="mb-sm-0 font-size-18">{{ __('Expenses') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Transaction') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Expenses') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($isOpen)
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <h4 class="mb-sm-0 font-size-18">{{ $expense_id ? __('Edit Expense') : __('Monthly Expense Entry') }}</h4>
                            <button wire:click="closeModal()" class="btn btn-outline-secondary waves-effect btn-sm">
                                <i class="bx bx-arrow-back me-1"></i> {{ __('Back to List') }}
                            </button>
                        </div>

                        {{-- Expense Voucher Form --}}
                        <div class="expense-voucher-form card border shadow-sm mb-4">
                            <div class="card-body p-0">
                                <form wire:submit.prevent="store">
                                    {{-- Form Header Section --}}
                                    <div class="voucher-header bg-light border-bottom p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-2 text-primary fw-bold">{{ __('মাসিক ব্যয় বিবরণী / Monthly Expense Statement') }}</h5>
                                                <div class="row g-2 small">
                                                    <div class="col-md-4">
                                                        <strong>{{ __('Office') }}:</strong> 
                                                        @if($rpo_unit_id)
                                                            @php $office = \App\Models\RpoUnit::find($rpo_unit_id); @endphp
                                                            {{ $office->name ?? 'N/A' }}
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>{{ __('Fiscal Year') }}:</strong>
                                                        @if($fiscal_year_id)
                                                            @php $fy = \App\Models\FiscalYear::find($fiscal_year_id); @endphp
                                                            {{ $fy->bn_name ?? 'N/A' }}
                                                        @else
                                                            -
                                                        @endif
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
                                                    {{ __('Select Month') }} <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select form-select-sm" id="selectedMonth" wire:model.live="selectedMonth" {{ $expense_id ? 'disabled' : '' }}>
                                                    <option value="">{{ __('Select Month') }}</option>
                                                    <option value="01">{{ __('January') }}</option>
                                                    <option value="02">{{ __('February') }}</option>
                                                    <option value="03">{{ __('March') }}</option>
                                                    <option value="04">{{ __('April') }}</option>
                                                    <option value="05">{{ __('May') }}</option>
                                                    <option value="06">{{ __('June') }}</option>
                                                    <option value="07">{{ __('July') }}</option>
                                                    <option value="08">{{ __('August') }}</option>
                                                    <option value="09">{{ __('September') }}</option>
                                                    <option value="10">{{ __('October') }}</option>
                                                    <option value="11">{{ __('November') }}</option>
                                                    <option value="12">{{ __('December') }}</option>
                                                </select>
                                                @error('selectedMonth') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="fiscal_year_id" class="form-label fw-semibold small mb-1">
                                                    {{ __('Fiscal Year') }} <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select form-select-sm" id="fiscal_year_id" wire:model.live="fiscal_year_id" {{ $expense_id ? 'disabled' : '' }}>
                                                    <option value="">{{ __('Select Year') }}</option>
                                                    @foreach($fiscalYears as $year)
                                                        <option value="{{ $year->id }}">{{ $year->bn_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('fiscal_year_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="budget_type_id" class="form-label fw-semibold small mb-1">
                                                    {{ __('Budget Type') }}
                                                </label>
                                                <select class="form-select form-select-sm" id="budget_type_id" wire:model.live="budget_type_id" {{ $expense_id ? 'disabled' : '' }}>
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
                                                        <th style="width: 50px;" class="small">#</th>
                                                        <th style="width: 100px;" class="small">{{ __('Code') }}</th>
                                                        <th class="small">{{ __('Economic Head / বাজেট খাত') }}</th>
                                                        <th style="width: 130px;" class="small">{{ __('This Month') }}<br>{{ __('(Spent)') }}</th>
                                                        <th style="width: 130px;" class="small">{{ __('Previous Total') }}</th>
                                                        <th style="width: 150px;" class="small">{{ __('New Amount') }}<br>{{ __('(নতুন পরিমাণ)') }}</th>
                                                        <th style="width: 200px;" class="small">{{ __('Remarks') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $serialNo = 1; @endphp
                                                    @foreach($economicCodes as $code)
                                                        <tr class="{{ $code->parent_id == null ? 'table-secondary fw-bold' : '' }}">
                                                            {{-- Serial Number --}}
                                                            <td class="text-center small">
                                                                @if($code->parent_id != null)
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
                                                            
                                                            @if($code->parent_id != null)
                                                                {{-- This Month (Already Spent) --}}
                                                                <td class="text-end">
                                                                    @php $existing = $existingEntries[$code->id] ?? 0; @endphp
                                                                    <span class="font-monospace small {{ $existing > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                                                        {{ bn_comma_format($existing, 2) }}
                                                                    </span>
                                                                </td>
                                                                
                                                                {{-- Previous Total --}}
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
                                                                        {{ bn_comma_format($previousTotal, 2) }}
                                                                    </span>
                                                                </td>
                                                                
                                                                {{-- New Amount Input --}}
                                                                <td>
                                                                    <input type="number" 
                                                                           step="0.01" 
                                                                           class="form-control form-control-sm text-end font-monospace" 
                                                                           wire:model="expenseEntries.{{ $code->id }}.amount" 
                                                                           placeholder="0.00">
                                                                </td>
                                                                
                                                                {{-- Remarks --}}
                                                                <td>
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           wire:model="expenseEntries.{{ $code->id }}.description" 
                                                                           placeholder="{{ __('Notes...') }}">
                                                                </td>
                                                            @else
                                                                <td colspan="4" class="text-center fst-italic text-muted small bg-light">
                                                                    {{ __('Parent Head - No Direct Entry') }}
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light border-top-2">
                                                    <tr class="fw-bold">
                                                        <td colspan="5" class="text-end">{{ __('Total New Expenses:') }}</td>
                                                        <td class="text-end font-monospace text-primary">
                                                            @php
                                                                $totalNew = 0;
                                                                foreach($expenseEntries as $entry) {
                                                                    $totalNew += floatval($entry['amount'] ?? 0);
                                                                }
                                                            @endphp
                                                            {{ bn_comma_format($totalNew, 2) }}
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
                                            {{ __('All amounts should be entered in BDT (৳)') }}
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button wire:click="closeModal()" type="button" class="btn btn-secondary btn-sm px-4">
                                                <i class="bx bx-x me-1"></i>{{ __('Cancel') }}
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                                <i class="bx bx-save me-1"></i> {{ __('Save Expenses') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h4 class="card-title">{{ __('Expense List') }}</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                    <div>
                                        <select wire:model.live="filter_month" class="form-select form-select-sm">
                                            <option value="">{{ __('All Months') }}</option>
                                            <option value="01">{{ __('January') }}</option>
                                            <option value="02">{{ __('February') }}</option>
                                            <option value="03">{{ __('March') }}</option>
                                            <option value="04">{{ __('April') }}</option>
                                            <option value="05">{{ __('May') }}</option>
                                            <option value="06">{{ __('June') }}</option>
                                            <option value="07">{{ __('July') }}</option>
                                            <option value="08">{{ __('August') }}</option>
                                            <option value="09">{{ __('September') }}</option>
                                            <option value="10">{{ __('October') }}</option>
                                            <option value="11">{{ __('November') }}</option>
                                            <option value="12">{{ __('December') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select wire:model.live="filter_fiscal_year_id" class="form-select form-select-sm">
                                            <option value="">{{ __('All Fiscal Years') }}</option>
                                            @foreach($fiscalYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->bn_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @can('create-expenses')
                                        <a href="{{ route('setup.expenses.create') }}" wire:navigate class="btn btn-primary btn-sm waves-effect waves-light">
                                            <i class="bx bx-plus-circle me-1"></i>{{ __('Create New') }}
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Economic Code') }}</th>
                                        <th>{{ __('Office') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedExpenses as $monthYear => $monthExpenses)
                                        <tr class="bg-light-subtle">
                                            <td colspan="4" class="fw-bold text-primary">
                                                <i class="bx bx-calendar me-1"></i> {{ bn_num($monthYear) }}
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                {{ __('Total') }}: {{ bn_comma_format($monthlyTotals[$monthYear] ?? 0, 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                        @foreach($monthExpenses as $expense)
                                            <tr>
                                                <td class="ps-4">
                                                    @php $expDate = Carbon\Carbon::make($expense->date); @endphp
                                                    {{ bn_num($expDate->format('d')) }}-{{ __($expDate->format('M')) }}-{{ bn_num($expDate->format('Y')) }}
                                                </td>
                                                <td>{{ bn_num($expense->code) }}</td>
                                                <td>{{ bn_num($expense->economicCode->code ?? '-') }} - {{ $expense->economicCode->name ?? '' }}</td>
                                                <td>{{ $expense->office->name ?? '-' }}</td>
                                                <td class="text-end">{{ bn_comma_format($expense->amount, 2) }}</td>
                                                <td class="text-center">
                                                    @if($expense->status === App\Models\Expense::STATUS_APPROVED)
                                                        <span class="badge bg-success" title="{{ __('Approved by') }}: {{ $expense->approvedBy->name ?? 'N/A' }} {{ __('at') }} {{ $expense->approved_at }}">
                                                            {{ __('Approved') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">{{ __('Draft') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        @if($expense->status === App\Models\Expense::STATUS_DRAFT)
                                                            {{-- Approval button: visible to those with permission --}}
                                                            @can('approve-expenses')
                                                                <button wire:click="approve({{ $expense->id }})" class="btn btn-soft-success" title="{{ __('Approve') }}">
                                                                    <i class="bx bx-check-double"></i>
                                                                </button>
                                                            @endcan
                                                            
                                                            {{-- Edit/Delete: only for creator --}}
                                                            @if($expense->created_by === auth()->id() || auth()->user()->hasRole('Admin'))
                                                                @can('edit-expenses')
                                                                    <button wire:click="edit({{ $expense->id }})" class="btn btn-soft-info" title="{{ __('Edit') }}">
                                                                        <i class="bx bx-edit-alt"></i>
                                                                    </button>
                                                                @endcan
                                                                
                                                                @can('delete-expenses')
                                                                    <button wire:click="delete({{ $expense->id }})" class="btn btn-soft-danger" title="{{ __('Delete') }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @endcan
                                                            @endif
                                                        @else
                                                            <button class="btn btn-light btn-sm" disabled title="{{ __('Approved & Locked') }}">
                                                                <i class="bx bx-lock-alt"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                {{ __('No expenses found for the selected filters.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="custom-pagination-wrpaper">
                            {{ $expenses->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
