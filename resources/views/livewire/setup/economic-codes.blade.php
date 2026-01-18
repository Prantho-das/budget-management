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

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
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
                        <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $economic_code_id ? __('Edit') : __('Create') }} {{ __('Code') }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($isUsed)
                                            <div class="alert alert-warning mb-3" role="alert">
                                                <i class="mdi mdi-alert-outline me-2"></i>
                                                {{ __('This code is in use (has children or financial data) and cannot be moved or have its code changed.') }}
                                            </div>
                                        @endif
                                        <form>
                                            <div class="mb-3">
                                                <label for="code" class="form-label">{{ __('Economic Code') }}</label>
                                                <input type="text" class="form-control" id="code" wire:model.live="code" placeholder="{{ __('e.g. 3257101') }}" {{ $isUsed ? 'disabled' : '' }}>
                                                @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="{{ __('Name') }}">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="selectedParentId" class="form-label">{{ __('First Stage') }}</label>
                                                <select class="form-control" id="selectedParentId" wire:model.live="selectedParentId" disabled>
                                                    <option value="">{{ __('None (Root Level)') }}</option>
                                                    @foreach($rootCodes as $pCode)
                                                        <option value="{{ $pCode->id }}">
                                                            {{ $pCode->code }} - {{ $pCode->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedParentId') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>

                                            @if($selectedParentId && $subHeadCodes->count() > 0)
                                                <div class="mb-3">
                                                    <label for="selectedSubHeadId" class="form-label">{{ __('Second Stage') }}</label>
                                                    <select class="form-control" id="selectedSubHeadId" wire:model.live="selectedSubHeadId" disabled>
                                                        <option value="">{{ __('None (Parent Head is the actual parent)') }}</option>
                                                        @foreach($subHeadCodes as $child)
                                                            <option value="{{ $child->id }}">
                                                                {{ $child->code }} - {{ $child->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedSubHeadId') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            @endif

                                            <script>
                                                document.addEventListener('livewire:navigated', () => {
                                                    if(typeof initSelect2 === 'function') initSelect2();
                                                });
                                                
                                                window.addEventListener('select2-reinit', event => {
                                                    setTimeout(() => {
                                                        if(typeof initSelect2 === 'function') initSelect2();
                                                    }, 100);
                                                });
                                            </script>
                                            <div class="mb-3 d-none">
                                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                                <textarea class="form-control" id="description" wire:model="description"></textarea>
                                                @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __('Close') }}</button>
                                        <button wire:click="store()" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <div class="position-relative">
                                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by code or name...') }}">
                                    <i class="mdi mdi-magnify search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">{{ __('SL') }}</th>
                                    <th style="width: 150px;">{{ __('Economic Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($codes as $root)
                                    @php $rootIdx = $loop->iteration; @endphp
                                    <tr class="table-primary border-start border-4 border-primary">
                                        <td>{{ $rootIdx }}</td>
                                        <td>
                                            <span class="badge bg-primary fs-13">{{ $root->code }}</span>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $root->name }}</td>
                                        <td><span class="badge badge-soft-primary px-3">{{ __('First Stage') }}</span></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($root->isUsed())
                                                    <span class="badge bg-soft-warning text-warning p-2" title="{{ __('This record is locked because it is in use') }}">
                                                        <i class="mdi mdi-lock fs-14"></i>
                                                    </span>
                                                @else
                                                    @can('edit-economic-codes')
                                                        <button wire:click="edit({{ $root->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                    @endcan
                                                    @can('delete-economic-codes')
                                                        <button wire:click="delete({{ $root->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @foreach($root->children as $subHead)
                                        @php $subIdx = $loop->iteration; @endphp
                                        <tr class="table-light">
                                            <td style="padding-left: 20px;">{{ $rootIdx }}.{{ $subIdx }}</td>
                                            <td>
                                                <i class="mdi mdi-arrow-right-bottom me-1 text-muted"></i>
                                                <span class="badge bg-info fs-12">{{ $subHead->code }}</span>
                                            </td>
                                            <td class="fw-medium text-info">{{ $subHead->name }}</td>
                                            <td><span class="badge badge-soft-info px-3">{{ __('Second Stage') }}</span></td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    @if($subHead->isUsed())
                                                        <span class="badge bg-soft-warning text-warning p-2" title="{{ __('This record is locked because it is in use') }}">
                                                            <i class="mdi mdi-lock fs-14"></i>
                                                        </span>
                                                    @else
                                                        @can('edit-economic-codes')
                                                            <button wire:click="edit({{ $subHead->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light"><i class="mdi mdi-pencil"></i></button>
                                                        @endcan
                                                        @can('delete-economic-codes')
                                                            <button wire:click="delete({{ $subHead->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light"><i class="mdi mdi-trash-can"></i></button>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach($subHead->children as $project)
                                            <tr>
                                                <td style="padding-left: 40px;">{{ $rootIdx }}.{{ $subIdx }}.{{ $loop->iteration }}</td>
                                                <td>
                                                    <i class="mdi mdi-subdirectory-arrow-right me-1 text-muted"></i>
                                                    <span class="badge bg-success fs-11">{{ $project->code }}</span>
                                                </td>
                                                <td class="text-success">{{ $project->name }}</td>
                                                <td><span class="badge badge-soft-success px-3">{{ __('Third Stage') }}</span></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        @if($project->isUsed())
                                                            <span class="badge bg-soft-warning text-warning p-2" title="{{ __('This record is locked because it is in use') }}">
                                                                <i class="mdi mdi-lock fs-14"></i>
                                                            </span>
                                                        @else
                                                            @can('edit-economic-codes')
                                                                <button wire:click="edit({{ $project->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light"><i class="mdi mdi-pencil"></i></button>
                                                            @endcan
                                                            @can('delete-economic-codes')
                                                                <button wire:click="delete({{ $project->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light"><i class="mdi mdi-trash-can"></i></button>
                                                            @endcan
                                                        @endif
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
