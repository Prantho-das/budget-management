<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Types') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Budget Types') }}</li>
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
                        <h4 class="card-title">{{ __('All Budget Types') }}</h4>
                        @can('create-budget-types')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Add New Type') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content text-dark">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $type_id ? __('Edit') : __('Create') }} {{ __('Budget Type') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ __('Type Name') }}</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('e.g. Original Budget') }}">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="code" class="form-label">{{ __('Short Code (Unique)') }}</label>
                                                <input type="text" class="form-control" id="code" wire:model="code" placeholder="{{ __('e.g. original') }}">
                                                @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="order_priority" class="form-label">{{ __('Sequence (Order Priority)') }}</label>
                                                <input type="number" class="form-control" id="order_priority" wire:model="order_priority" placeholder="1, 2, 3...">
                                                @error('order_priority') <span class="text-danger">{{ $message }}</span>@enderror
                                                <small class="text-muted">{{ __('Higher priority means it comes later in the sequence.') }}</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="Activestatus" wire:model="status">
                                                <label class="form-check-label" for="status">{{ __('Active Status') }}</label>
                                            </div>
                                         
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-sm btn-danger">{{ __('Close') }}</button>
                                        <button wire:click="store()" type="button" class="btn btn-sm btn-success waves-effect waves-light">{{ __('Save Changes') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Sequence') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td><code>{{ $type->code }}</code></td>
                                        <td>{{ $type->order_priority }}</td>
                                        <td>
                                            <span class="badge bg-{{ $type->status ? 'success' : 'danger' }}">
                                                {{ $type->status ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('edit-budget-types')
                                                <button wire:click="edit({{ $type->id }})" class="btn btn-sm btn-info me-1">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('delete-budget-types')
                                                <button wire:click="delete({{ $type->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
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
