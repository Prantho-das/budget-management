<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Fiscal Years') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Fiscal Years') }}</li>
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
                        <h4 class="card-title">{{ __('Fiscal Year List') }}</h4>
                        @can('create-fiscal-years')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Create New') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $fiscal_year_id ? __('Edit') : __('Create') }} {{ __('Fiscal Year') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('e.g. 2025-2026') }}">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                                                <input type="date" class="form-control" id="start_date" wire:model="start_date">
                                                @error('start_date') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                                                <input type="date" class="form-control" id="end_date" wire:model="end_date">
                                                @error('end_date') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="status" wire:model="status">
                                                    <label class="form-check-label" for="status">{{ __('Active Status') }}</label>
                                                </div>
                                                @error('status') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __('Close') }}</button>
                                        <button wire:click="store()" type="button" class="btn btn-primary">{{ __('Save changes') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fiscal_years as $fiscal)
                                    <tr>
                                        <td>{{ $fiscal->id }}</td>
                                        <td>{{ $fiscal->name }}</td>
                                        <td>{{ $fiscal->start_date }}</td>
                                        <td>{{ $fiscal->end_date }}</td>
                                        <td>
                                            @if($fiscal->status)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('edit-fiscal-years')
                                                <button wire:click="edit({{ $fiscal->id }})" class="btn btn-sm btn-info">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('delete-fiscal-years')
                                                <button wire:click="delete({{ $fiscal->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                                            @endcan
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
