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
                            <i class="mdi mdi-check-all me-2"></i>
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3">
                        <div class="search-box me-2">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="{{ __('Search Office Name/Code...') }}" wire:model.live="search">
                                <i class="bx bx-search-alt search-icon"></i>
                            </div>
                        </div>
                        @can('create-offices')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">
                                <i class="bx bx-plus me-1"></i> {{ __('Create New') }}
                            </button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title font-size-16">
                                            <i class="bx bx-buildings me-2"></i>
                                            {{ $rpo_unit_id ? __('Edit Office') : __('Create New Office') }}
                                        </h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label for="name" class="form-label fw-bold">{{ __('Office Name') }} <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-building"></i></span>
                                                        <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('e.g. বিভাগীয় পাসপোর্ট ও ভিসা অফিস, ঢাকা') }}">
                                                    </div>
                                                    @error('name') <span class="text-danger small">{{ $message }}</span>@enderror
                                                </div>
                                                
                                                <div class="col-md-4 mb-3">
                                                    <label for="code" class="form-label fw-bold">{{ __('Office Code') }} <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-hash"></i></span>
                                                        <input type="text" class="form-control" id="code" wire:model="code" placeholder="{{ __('e.g. 133255') }}">
                                                    </div>
                                                    @error('code') <span class="text-danger small">{{ $message }}</span>@enderror
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label for="parent_id" class="form-label fw-bold">{{ __('Supervising Office (Parent)') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-git-merge"></i></span>
                                                        <select class="form-select" id="parent_id" wire:model="parent_id">
                                                            <option value="">{{ __('None (Root Level / Headquarters)') }}</option>
                                                            @foreach($parents as $parent)
                                                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        {{ __('Select the office that directly supervises this unit.') }}
                                                    </small>
                                                    @error('parent_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer bg-light p-3">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary waves-effect">
                                            <i class="bx bx-x me-1"></i> {{ __('Cancel') }}
                                        </button>
                                        <button wire:click="store()" type="button" class="btn btn-primary waves-effect waves-light">
                                            <i class="bx bx-save me-1"></i> {{ $rpo_unit_id ? __('Update Office') : __('Create Office') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-centered table-nowrap mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 80px;">{{ __('ID') }}</th>
                                    <th class="text-start">{{ __('Office Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th class="text-start">{{ __('Supervising Office') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rpo_units as $unit)
                                    <tr class="text-center">
                                        <td><span class="text-muted">{{ $unit->id }}</span></td>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-18">
                                                        <i class="bx bx-buildings"></i>
                                                    </span>
                                                </div>
                                                <h5 class="font-size-14 mb-0 text-truncate" style="max-width: 300px;">{{ $unit->name }}</h5>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary-subtle text-primary font-size-12">{{ $unit->code }}</span></td>
                                        <td class="text-start">
                                            @if($unit->parent)
                                                <span class="text-info font-size-13"><i class="bx bx-link me-1"></i>{{ $unit->parent->name }}</span>
                                            @else
                                                <span class="text-muted font-size-13 fst-italic">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                @can('edit-offices')
                                                    <button wire:click="edit({{ $unit->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="{{ __('Edit') }}">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
                                                @endcan
                                                @can('delete-offices')
                                                    <button onclick="confirm('{{ __('Are you sure you want to delete this office?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $unit->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="{{ __('Delete') }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-search-alt font-size-24 d-block mb-2"></i>
                                                {{ __('No offices found matching your search.') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
