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
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">{{ __('SL') }}</th>
                                    <th style="width: 150px;">{{ __('Office Code') }}</th>
                                    <th>{{ __('Office Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rpo_units as $root)
                                    @php $rootIdx = $loop->iteration; @endphp
                                    <tr class="table-primary border-start border-4 border-primary">
                                        <td>{{ $rootIdx }}</td>
                                        <td>
                                            <span class="badge bg-primary fs-13">{{ $root->code }}</span>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $root->name }}</td>
                                        <td><span class="badge badge-soft-primary px-3">{{ __('Headquarters') }}</span></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @can('edit-offices')
                                                    <button wire:click="edit({{ $root->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                @endcan
                                                @can('delete-offices')
                                                    <button onclick="confirm('{{ __('Are you sure you want to delete this office?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $root->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @foreach($root->children as $child)
                                        @php $childIdx = $loop->iteration; @endphp
                                        <tr class="table-light">
                                            <td style="padding-left: 20px;">{{ $rootIdx }}.{{ $childIdx }}</td>
                                            <td>
                                                <i class="mdi mdi-arrow-right-bottom me-1 text-muted"></i>
                                                <span class="badge bg-info fs-12">{{ $child->code }}</span>
                                            </td>
                                            <td class="fw-medium text-info">{{ $child->name }}</td>
                                            <td><span class="badge badge-soft-info px-3">{{ __('District Office') }}</span></td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    @can('edit-offices')
                                                        <button wire:click="edit({{ $child->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                    @endcan
                                                    @can('delete-offices')
                                                        <button onclick="confirm('{{ __('Are you sure you want to delete this office?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $child->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach($child->children as $grandChild)
                                            <tr>
                                                <td style="padding-left: 40px;">{{ $rootIdx }}.{{ $childIdx }}.{{ $loop->iteration }}</td>
                                                <td>
                                                    <i class="mdi mdi-subdirectory-arrow-right me-1 text-muted"></i>
                                                    <span class="badge bg-success fs-11">{{ $grandChild->code }}</span>
                                                </td>
                                                <td class="text-success">{{ $grandChild->name }}</td>
                                                <td><span class="badge badge-soft-success px-3">{{ __('Field Office') }}</span></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        @can('edit-offices')
                                                            <button wire:click="edit({{ $grandChild->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                        @endcan
                                                        @can('delete-offices')
                                                            <button onclick="confirm('{{ __('Are you sure you want to delete this office?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $grandChild->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
