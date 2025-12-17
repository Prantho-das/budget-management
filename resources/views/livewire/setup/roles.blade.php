<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Roles</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Setup</a></li>
                        <li class="breadcrumb-item active">Roles</li>
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
                        <h4 class="card-title">Role List</h4>
                        <button wire:click="create()" class="btn btn-primary waves-effect waves-light">Create New</button>
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $role_id ? 'Edit' : 'Create' }} Role</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Role Name</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="e.g. Admin, RPO User">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Permissions</label>
                                                <div class="row" style="max-height: 400px; overflow-y: auto;">
                                                    @foreach($groupedPermissions as $groupName => $permissions)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card border">
                                                                <div class="card-header bg-light py-2">
                                                                    <h6 class="mb-0">{{ $groupName ?: 'Uncategorized' }}</h6>
                                                                </div>
                                                                <div class="card-body p-2">
                                                                    @foreach($permissions as $permission)
                                                                        <div class="form-check mb-1">
                                                                            <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" wire:model="selectedPermissions">
                                                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                                {{ $permission->name }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Permissions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @foreach($role->permissions as $perm)
                                                <span class="badge bg-secondary">{{ $perm->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $role->id }})" class="btn btn-sm btn-info">Edit</button>
                                            <button wire:click="delete({{ $role->id }})" class="btn btn-sm btn-danger">Delete</button>
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
