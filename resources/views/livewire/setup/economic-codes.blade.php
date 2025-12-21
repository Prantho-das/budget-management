<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Economic Codes (Budget Heads)') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Economic Codes') }}</li>
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
                        <h4 class="card-title">{{ __('Economic Code List') }}</h4>
                        @can('create-economic-codes')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Create New') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $economic_code_id ? __('Edit') : __('Create') }} {{ __('Code') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="code" class="form-label">{{ __('Economic Code') }}</label>
                                                <input type="text" class="form-control" id="code" wire:model="code" placeholder="{{ __('e.g. 3257101') }}">
                                                @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('Name') }}">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                                <textarea class="form-control" id="description" wire:model="description"></textarea>
                                                @error('description') <span class="text-danger">{{ $message }}</span>@enderror
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
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($codes as $code)
                                    <tr>
                                        <td>{{ $code->id }}</td>
                                        <td><span class="badge bg-success">{{ $code->code }}</span></td>
                                        <td>{{ $code->name }}</td>
                                        <td>{{ $code->description }}</td>
                                        <td>
                                            @can('edit-economic-codes')
                                                <button wire:click="edit({{ $code->id }})" class="btn btn-sm btn-info">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('delete-economic-codes')
                                                <button wire:click="delete({{ $code->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
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
