<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Users</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Setup</a></li>
                        <li class="breadcrumb-item active">Users</li>
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
                                <input type="text" class="form-control" placeholder="Search User..." wire:model.live="search">
                                <i class="bx bx-search-alt search-icon"></i>
                            </div>
                        </div>
                        <button wire:click="create()" class="btn btn-primary waves-effect waves-light">Create New User</button>
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $user_id ? 'Edit User / Transfer' : 'Create User' }}</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" wire:model="name">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" wire:model="email">
                                                @error('email') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password {{ $user_id ? '(Leave blank to keep current)' : '' }}</label>
                                                <input type="password" class="form-control" wire:model="password">
                                                @error('password') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Assigned Role</label>
                                                <select class="form-select" wire:model="role">
                                                    <option value="">Select Role</option>
                                                    @foreach($roles as $r)
                                                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('role') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Assigned Office (Transfer Office here)</label>
                                                <select class="form-select" wire:model="rpo_unit_id">
                                                    <option value="">Select Office</option>
                                                    @foreach($offices as $office)
                                                        <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->code }})</option>
                                                    @endforeach
                                                </select>
                                                @error('rpo_unit_id') <span class="text-danger">{{ $message }}</span>@enderror
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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Office</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->office)
                                                <span class="badge bg-info">{{ $user->office->name }}</span>
                                            @else
                                                <span class="badge bg-danger">No Office Assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-info">Edit / Transfer</button>
                                            @if($user->id !== auth()->id())
                                                <button onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" wire:click="delete({{ $user->id }})" class="btn btn-sm btn-danger">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
