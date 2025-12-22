<div>
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
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3">
                        <h4 class="card-title">{{ __('Expense List') }}</h4>
                        @can('create-expenses')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Create New') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $expense_id ? __('Edit') : __('Create') }} {{ __('Expense') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code" class="form-label">{{ __('Expense Code / Bill No') }}</label>
                                                    <input type="text" class="form-control" id="code" wire:model="code" placeholder="{{ __('Unique Code') }}">
                                                    @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="date" class="form-label">{{ __('Date') }}</label>
                                                    <input type="date" class="form-control" id="date" wire:model="date">
                                                    @error('date') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="economic_code_id" class="form-label">{{ __('Economic Code') }}</label>
                                                    <select class="form-select" id="economic_code_id" wire:model.live="economic_code_id">
                                                        <option value="">{{ __('Select Code') }}</option>
                                                        @foreach($economicCodes as $code)
                                                            <option value="{{ $code->id }}">{{ $code->code }} - {{ $code->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('economic_code_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="budget_type_id" class="form-label">{{ __('Budget Source (Optional)') }}</label>
                                                    <select class="form-select" id="budget_type_id" wire:model.live="budget_type_id">
                                                        <option value="">{{ __('Select Source') }}</option>
                                                        @foreach($budgetTypes as $type)
                                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('budget_type_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="amount" class="form-label">{{ __('Amount') }}</label>
                                                    <input type="number" step="0.01" class="form-control" id="amount" wire:model.live="amount" placeholder="0.00">
                                                    @error('amount') <span class="text-danger">{{ $message }}</span>@enderror
                                                    
                                                    @if($economic_code_id && $rpo_unit_id && $fiscal_year_id)
                                                        <div class="mt-2 d-flex gap-2">
                                                            <div>
                                                                <small class="text-muted">{{ __('Total Budget') }}: </small>
                                                                <span class="badge bg-info shadow">
                                                                    ৳ {{ number_format($totalReleased, 2) }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted">{{ __('Available Balance') }}: </small>
                                                                <span class="badge bg-{{ $availableBalance > 0 ? 'success' : 'danger' }} shadow">
                                                                    ৳ {{ number_format($availableBalance, 2) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="rpo_unit_id" class="form-label">{{ __('Office') }}</label>
                                                    <select class="form-select" id="rpo_unit_id" wire:model.live="rpo_unit_id">
                                                        <option value="">{{ __('Select Office') }}</option>
                                                        @foreach($offices as $office)
                                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('rpo_unit_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="fiscal_year_id" class="form-label">{{ __('Fiscal Year') }}</label>
                                                    <select class="form-select" id="fiscal_year_id" wire:model.live="fiscal_year_id">
                                                        <option value="">{{ __('Select Year') }}</option>
                                                        @foreach($fiscalYears as $year)
                                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('fiscal_year_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                                <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
                                                @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>

                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __('Close') }}</button>
                                        <button wire:click="store()" type="button" class="btn btn-primary" {{ $amount > $availableBalance ? 'disabled' : '' }}>{{ __('Save changes') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                        <td>{{ $expense->economicCode->code ?? '-' }}</td>
                                        <td>{{ $expense->office->name ?? '-' }}</td>
                                        <td>৳ {{ number_format($expense->amount, 2) }}</td>
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

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
