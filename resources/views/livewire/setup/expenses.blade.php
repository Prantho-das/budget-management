<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Expenses</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Transaction</a></li>
                        <li class="breadcrumb-item active">Expenses</li>
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
                        <h4 class="card-title">Expense List</h4>
                        <button wire:click="create()" class="btn btn-primary waves-effect waves-light">Create New</button>
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $expense_id ? 'Edit' : 'Create' }} Expense</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code" class="form-label">Expense Code / Bill No</label>
                                                    <input type="text" class="form-control" id="code" wire:model="code" placeholder="Unique Code">
                                                    @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="date" wire:model="date">
                                                    @error('date') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="expense_category_id" class="form-label">Category</label>
                                                    <select class="form-select" id="expense_category_id" wire:model="expense_category_id">
                                                        <option value="">Select Category</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                                                        @endforeach
                                                    </select>
                                                    @error('expense_category_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="amount" class="form-label">Amount</label>
                                                    <input type="number" step="0.01" class="form-control" id="amount" wire:model="amount" placeholder="0.00">
                                                    @error('amount') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="rpo_unit_id" class="form-label">Office</label>
                                                    <select class="form-select" id="rpo_unit_id" wire:model="rpo_unit_id">
                                                        <option value="">Select Office</option>
                                                        @foreach($offices as $office)
                                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('rpo_unit_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="fiscal_year_id" class="form-label">Fiscal Year</label>
                                                    <select class="form-select" id="fiscal_year_id" wire:model="fiscal_year_id">
                                                        <option value="">Select Year (Optional)</option>
                                                        @foreach($fiscalYears as $year)
                                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('fiscal_year_id') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
                                                @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>

                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary">Close</button>
                                        <button wire:click="store()" type="button" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Office</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->date }}</td>
                                        <td><span class="badge bg-primary">{{ $expense->code }}</span></td>
                                        <td>{{ $expense->category->name ?? '-' }}</td>
                                        <td>{{ $expense->office->name ?? '-' }}</td>
                                        <td>{{ number_format($expense->amount, 2) }}</td>
                                        <td>
                                            <button wire:click="edit({{ $expense->id }})" class="btn btn-sm btn-info">Edit</button>
                                            <button wire:click="delete({{ $expense->id }})" class="btn btn-sm btn-danger">Delete</button>
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
</div>
