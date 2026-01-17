<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Permissions</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Setup</a></li>
                        <li class="breadcrumb-item active">Permissions</li>
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
                        <h4 class="card-title">Permission List</h4>
                        <button wire:click="create()" class="btn btn-primary waves-effect waves-light">Create New</button>
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show common-modal" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $permission_id ? 'Edit' : 'Create' }} Permission</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Permission Name</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="e.g. user.create">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="group_name" class="form-label">Group Name</label>
                                                <input type="text" class="form-control" id="group_name" wire:model="group_name" placeholder="e.g. User Management">
                                                <small class="text-muted">Used to group permissions in the Role assignment screen.</small>
                                                @error('group_name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="closeModal()" type="button" class="btn btn-secondary">Close</button>
                                        <button wire:click="store()" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
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
                                    <th>Group</th>
                                    <th>Name</th>
                                    <th>Guard</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $perm)
                                    <tr>
                                        <td>{{ $perm->id }}</td>
                                        <td><span class="badge bg-info">{{ $perm->group_name }}</span></td>
                                        <td>{{ $perm->name }}</td>
                                        <td>{{ $perm->guard_name }}</td>
                                        <td class="text-center">
                                            <button wire:click="edit({{ $perm->id }})" class="btn btn-sm btn-info btn-soft-info waves-effect waves-light" title="{{ __('Edit') }}">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button onclick="confirm('{{ __('Are you sure?') }}') || event.stopImmediatePropagation()" wire:click="delete({{ $perm->id }})" class="btn btn-sm btn-danger btn-soft-danger waves-effect waves-light" title="{{ __('Delete') }}">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
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
