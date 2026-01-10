<div class="unitoffice-entry-table budget-status-table">
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
                        <div class="d-flex justify-content-between mb-4 align-items-center">
                             <h4 class="mb-sm-0 font-size-18">{{ $expense_id ? __('Edit Expense') : __('Monthly Expense Entry') }}</h4>
                            <button wire:click="closeModal()" class="btn btn-outline-secondary waves-effect">
                                <i class="bx bx-arrow-back me-1"></i> {{ __('Back to List') }}
                            </button>
                        </div>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <form wire:submit.prevent="store">
                                    <div class="row align-items-end mb-4">
                                        <div class="col-md-3 mb-3">
                                            <label for="selectedMonth" class="form-label fw-bold">{{ __('Month') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" id="selectedMonth" wire:model.live="selectedMonth">
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
                                        <div class="col-md-3 mb-3">
                                            <label for="fiscal_year_id" class="form-label fw-bold">{{ __('Fiscal Year') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" id="fiscal_year_id" wire:model.live="fiscal_year_id">
                                                <option value="">{{ __('Select Year') }}</option>
                                                @foreach($fiscalYears as $year)
                                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('fiscal_year_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                        
                                        {{-- Hidden Office --}}
                                        @if(auth()->check())
                                            <input type="hidden" wire:model="rpo_unit_id" value="{{ auth()->user()->rpo_unit_id }}">
                                        @endif
                                    </div>
                                    
                                    @if($rpo_unit_id && $fiscal_year_id)
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 100px;">{{ __('Code') }}</th>
                                                        <th>{{ __('Economic Head') }}</th>
                                                        <th style="width: 200px;">{{ __('Expense Amount') }}</th>
                                                        <th>{{ __('Remarks / Description') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($economicCodes as $code)
                                                        <tr class="{{ $code->parent_id == null ? 'bg-light fw-bold' : '' }}">
                                                            <td>
                                                                <span class="badge {{ $code->parent_id ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary font-size-12' }}">
                                                                    {{ $code->code }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                {{ $code->name }}
                                                                @if($code->description && $code->parent_id)
                                                                    <i class="bx bx-info-circle text-muted ms-1" title="{{ $code->description }}"></i>
                                                                @endif
                                                            </td>
                                                            
                                                            @if($code->parent_id != null)
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text"></span>
                                                                        <input type="number" step="0.01" class="form-control" 
                                                                               wire:model="expenseEntries.{{ $code->id }}.amount" placeholder="0.00">
                                                                    </div>
                                                                </td>
                                                                <td >
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           wire:model="expenseEntries.{{ $code->id }}.description" placeholder="{{ __('Notes...') }}">
                                                                </td>
                                                            @else
                                                                <td colspan="2" class="text-muted fst-italic small text-center">{{ __('Parent Head - No Entry') }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-center py-4">
                                            <i class="bx bx-error-circle font-size-24 me-2"></i>
                                            <div>
                                                <strong>{{ __('Action Required') }}</strong>: {{ __('Please select Office and Fiscal Year to start entering expenses.') }}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary px-4">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bx bx-save me-1"></i> {{ __('Save Expenses') }}
                                        </button>
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
                                        <select wire:model.live="filter_fiscal_year_id" class="form-select form-select-sm">
                                            <option value="">{{ __('All Fiscal Years') }}</option>
                                            @foreach($fiscalYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @can('create-expenses')
                                        <button wire:click="create()" class="btn btn-primary btn-sm waves-effect waves-light">{{ __('Create New') }}</button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Economic Code') }}</th>
                                        <th>{{ __('Office') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        <tr>
                                            <td>{{ $expense->date }}</td>
                                            <td><span class="badge bg-primary">{{ $expense->code }}</span></td>
                                            <td class="text-center">{{ $expense->economicCode->code ?? '-' }}</td>
                                            <td>{{ $expense->office->name ?? '-' }}</td>
                                            <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                                            <td>
                                                @can('edit-expenses')
                                                    <button wire:click="edit({{ $expense->id }})" class="btn btn-sm btn-info">{{ __('Edit') }}</button>
                                                @endcan
                                                @can('delete-expenses')
                                                    <button wire:click="delete({{ $expense->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
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
