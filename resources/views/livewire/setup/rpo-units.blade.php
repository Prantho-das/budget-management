<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Office Management (RPO/DVPO)</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Setup</a></li>
                        <li class="breadcrumb-item active">Offices</li>
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
                        <h4 class="card-title">Office List</h4>
                        <button wire:click="create()" class="btn btn-primary waves-effect waves-light">Create New</button>
                    </div>

                    @if($isOpen)
                        <div class="modal-backdrop fade show"></div>
                        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $rpo_unit_id ? 'Edit' : 'Create' }} Office</h5>
                                        <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Office Name</label>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="e.g. Passport Office, Dhaka">
                                                @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code" class="form-label">Office Code</label>
                                                    <input type="text" class="form-control" id="code" wire:model="code" placeholder="Unique Code">
                                                    @error('code') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="type" class="form-label">Type</label>
                                                    <select class="form-select" id="type" wire:model="type">
                                                        <option value="">Select Type</option>
                                                        <option value="ministry">Ministry</option>
                                                        <option value="headquarters">Headquarters</option>
                                                        <option value="regional">Regional</option>
                                                        <option value="divisional">Divisional</option>
                                                    </select>
                                                    @error('type') <span class="text-danger">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="parent_id" class="form-label">Parent Office</label>
                                                <select class="form-select" id="parent_id" wire:model="parent_id">
                                                    <option value="">None (Top Level)</option>
                                                    @foreach($parents as $parent)
                                                        <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->type }})</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Select the supervising office (e.g., HQ for an RPO).</small>
                                                @error('parent_id') <span class="text-danger">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="district" class="form-label">District</label>
                                                <input type="text" class="form-control" id="district" wire:model="district" placeholder="District Name">
                                                @error('district') <span class="text-danger">{{ $message }}</span>@enderror
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
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Parent Office</th>
                                    <th>District</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rpo_units as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td>{{ $unit->name }}</td>
                                        <td><span class="badge bg-primary">{{ $unit->code }}</span></td>
                                        <td>{{ ucfirst($unit->type) }}</td>
                                        <td>{{ $unit->parent ? $unit->parent->name : '-' }}</td>
                                        <td>{{ $unit->district }}</td>
                                        <td>
                                            <button wire:click="edit({{ $unit->id }})" class="btn btn-sm btn-info">Edit</button>
                                            <button wire:click="delete({{ $unit->id }})" class="btn btn-sm btn-danger">Delete</button>
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
