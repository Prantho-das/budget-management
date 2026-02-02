<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Ministry Budget Entry List') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Ministry Budget Entry List') }}</li>
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
                        <h4 class="card-title">{{ __('Ministry Budget List') }}</h4>
                        <a href="{{ route('setup.ministry-budget-entry') }}" class="btn btn-primary waves-effect waves-light" wire:navigate>
                            <i class="mdi mdi-plus me-1"></i> {{ __('Create New Budget') }}
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Batch No') }}</th>
                                    <th>{{ __('Headquarters Unit') }}</th>
                                    <th>{{ __('Fiscal Year') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Total Amount') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($budgets as $budget)
                                    <tr>
                                        <td><strong>{{ bn_num($budget->batch_no) }}</strong></td>
                                        <td>{{ $budget->rpoUnit->name ?? 'N/A' }}</td>
                                        <td>{{ $budget->fiscalYear->bn_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($budget->budgetType)
                                                <span class="badge {{ $budget->budgetType->code == 'original' ? 'bg-primary' : 'bg-warning' }}">
                                                    {{ $budget->budgetType->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ bn_comma_format($budget->total_amount, 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('setup.ministry-budget-entry', ['master_id' => $budget->id]) }}" 
                                               class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="{{ __('Edit') }}" wire:navigate>
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <button onclick="confirm('{{ __('Are you sure?') }}') || event.stopImmediatePropagation()" 
                                                    wire:click="delete({{ $budget->id }})" 
                                                    class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="{{ __('Delete') }}">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $budgets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
