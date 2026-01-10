<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Users') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Users') }}</li>
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
                        <div class="search-box me-2">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="{{ __('Search User...') }}" wire:model.live="search">
                                <i class="bx bx-search-alt search-icon"></i>
                            </div>
                        </div>
                        @can('create-users')
                            <button wire:click="create()" class="btn btn-primary waves-effect waves-light">{{ __('Create New User') }}</button>
                        @endcan
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                      <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        {{ $user_id ? __('Edit User / Transfer') : __('Create New User') }}
                                    </h5>
                                    <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <form>
                                        <div class="row">

                                            <div class="col-12">
                                                <h6 class="text-primary">
                                                    <i class="bx bx-id-card"></i>
                                                    {{ __('Personal Information') }}
                                                </h6>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" wire:model="name" placeholder="{{ __('Enter full name') }}">
                                                </div>
                                                @error('name') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">{{ __('Username') }} <span class="text-danger">*</span></label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" wire:model="username" placeholder="{{ __('e.g., john_doe') }}">
                                                </div>
                                                @error('username') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" wire:model="phone" placeholder="{{ __('e.g., +123456789') }}">
                                                </div>
                                                @error('phone') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">{{ __('Designation') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" wire:model="designation" placeholder="{{ __('e.g., Assistant Director') }}">
                                                </div>
                                                @error('designation') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                                <div class="form-group">
                                                    <input type="email" class="form-control" wire:model="email" placeholder="{{ __('user@example.com') }}">
                                                </div>
                                                @error('email') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    {{ __('Password') }}
                                                    @if($user_id)
                                                        <small class="text-muted">({{ __('Leave blank to keep current') }})</small>
                                                    @else
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <div class="form-group">
                                                    <input type="password" class="form-control" wire:model="password" placeholder="{{ __('Enter password') }}">
                                                </div>
                                                @error('password') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-12">
                                                <h6 class="section-devider">
                                                    <i class="bx bx-cog"></i>
                                                    {{ __('System Assignment') }}
                                                </h6>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">{{ __('Assigned Role') }} <span class="text-danger">*</span></label>
                                                <div class="form-group">
                                                    <select class="form-select" wire:model="role">
                                                        <option value="">{{ __('Select Role') }}</option>
                                                        @foreach($roles as $r)
                                                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('role') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    {{ __('Assigned Office') }} <span class="text-danger">*</span>
                                                    @if($user_id)
                                                        <small class="text-info">({{ __('Change to transfer') }})</small>
                                                    @endif
                                                </label>
                                                <div class="form-group">
                                                    <select class="form-select" wire:model="rpo_unit_id">
                                                        <option value="">{{ __('Select Office') }}</option>
                                                        @foreach($offices as $office)
                                                            <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->code }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('rpo_unit_id') <span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>

                                        </div>
                                    </form>
                                </div>

                                <div class="modal-footer">
                                    <button wire:click="closeModal()" type="button" class="btn btn-sm btn-danger">
                                        <i class="bx bx-x"></i>{{ __('Cancel') }}
                                    </button>
                                    <button wire:click="store()" type="button" class="btn btn-sm btn-success waves-effect waves-light">
                                        <i class="bx bx-save"></i>{{ $user_id ? __('Update User') : __('Create User') }}
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Username / Phone') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Office') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>
                                            <div class="text-primary fw-bold">{{ $user->username }}</div>
                                            <div class="text-muted small">{{ $user->phone }}</div>
                                        </td>
                                        <td>
                                            @if($user->designation)
                                                <span class="text-muted">{{ $user->designation }}</span>
                                            @else
                                                <span class="text-muted fst-italic">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->office)
                                                <span class="badge bg-info">{{ $user->office->name }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('No Office Assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @can('edit-users')
                                                <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-info">{{ __('Edit') }}</button>
                                            @endcan
                                            <button wire:click="showHistory({{ $user->id }})" class="btn btn-sm btn-secondary">{{ __('History') }}</button>
                                            @if($user->id !== auth()->id())
                                                @can('delete-users')
                                                    <button onclick="confirm('{{ __('Are you sure?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $user->id }})" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $users->links() }}
                    </div>
                    
                    @if($showTransferHistoryModal)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('Office Transfer History') }}</h5>
                                        <button wire:click="closeHistoryModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('From Office') }}</th>
                                                        <th>{{ __('To Office') }}</th>
                                                        <th>{{ __('Transferred By') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($transferHistory as $history)
                                                        <tr>
                                                            <td>{{ $history->transfer_date }}</td>
                                                            <td>{{ $history->fromOffice->name ?? __('N/A') }}</td>
                                                            <td>{{ $history->toOffice->name ?? __('N/A') }}</td>
                                                            <td>{{ $history->creator->name ?? __('System') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">{{ __('No transfer history found.') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeHistoryModal()" type="button" class="btn btn-secondary">{{ __('Close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
