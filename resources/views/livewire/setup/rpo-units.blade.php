<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Office Management (RPO/DVPO)') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Offices') }}</li>
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
                        <h4 class="card-title">{{ __('Office List') }}</h4>
                        @can('create-offices')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Create New') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $rpo_unit_id ? __('Edit') : __('Create') }} {{ __('Office') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{ __('Office Name') }}</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('e.g. Passport Office, Dhaka') }}">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            
                                                <div class="col-md-12 mb-3">
                                                    <label for="code" class="form-label">{{ __('Office Code') }}</label>
                                                    <input type="text" class="form-control" id="code" wire:model="code" placeholder="{{ __('Unique Code') }}">
                                                    @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="parent_id" class="form-label">{{ __('Parent Office') }}</label>
                                                <select class="form-select" id="parent_id" wire:model="parent_id">
                                                    <option value="">{{ __('None (Top Level)') }}</option>
                                                    @foreach($parents as $parent)
                                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">{{ __('Select the supervising office (e.g., HQ for an RPO).') }}</small>
                                                @error('parent_id') <span class="text-danger">{{ $message }}</span>@enderror
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
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Parent Office') }}</th>
                                    <th>{{ __('Parent Office') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rpo_units as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td>{{ $unit->name }}</td>
                                        <td><span class="badge bg-primary">{{ $unit->code }}</span></td>
                                        <td>{{ $unit->parent ? $unit->parent->name : '-' }}</td>
                                        <td>
                                            @can('edit-offices')
                                                <button wire:click="edit({{ $unit->id }})" class="btn btn-sm btn-info">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('delete-offices')
                                                <button wire:click="delete({{ $unit->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
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
