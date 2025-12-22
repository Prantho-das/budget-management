<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Workflow Management') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Workflow') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ $editingStepId ? __('Edit Workflow Step') : __('Add New Workflow Step') }}</h4>

                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Step Name') }}</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. District Review">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Required Permission') }}</label>
                            <select class="form-select" wire:model="required_permission">
                                <option value="">{{ __('Select Permission') }}</option>
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                            @error('required_permission') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Office Level') }}</label>
                            <select class="form-select" wire:model="office_level">
                                <option value="origin">{{ __('Origin Office') }}</option>
                                <option value="parent">{{ __('Parent Office') }}</option>
                                <option value="hq">{{ __('Headquarters') }}</option>
                            </select>
                            @error('office_level') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Display Order') }}</label>
                            <input type="number" class="form-control" wire:model="order">
                            @error('order') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" wire:model="is_active">
                            <label class="form-check-label">{{ __('Is Active') }}</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-md">{{ $editingStepId ? __('Update') : __('Save') }}</button>
                            @if($editingStepId)
                                <button type="button" wire:click="resetForm" class="btn btn-light w-md">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('Approval Chain') }}</h4>
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">{{ __('Order') }}</th>
                                    <th>{{ __('Step Name') }}</th>
                                    <th>{{ __('Permission') }}</th>
                                    <th>{{ __('Assign To') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($steps as $step)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <button wire:click="moveUp({{ $step->id }})" class="btn btn-link btn-sm p-0 text-muted" {{ $loop->first ? 'disabled' : '' }}>
                                                    <i class="bx bx-chevron-up"></i>
                                                </button>
                                                <span class="fw-bold">{{ $step->order }}</span>
                                                <button wire:click="moveDown({{ $step->id }})" class="btn btn-link btn-sm p-0 text-muted" {{ $loop->last ? 'disabled' : '' }}>
                                                    <i class="bx bx-chevron-down"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>{{ $step->name }}</td>
                                        <td><code class="text-primary">{{ $step->required_permission }}</code></td>
                                        <td>
                                            <span class="badge badge-soft-info">
                                                {{ __(ucfirst($step->office_level)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $step->is_active ? 'success' : 'danger' }}">
                                                {{ $step->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $step->id }})" class="btn btn-sm btn-outline-primary waves-effect waves-light">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <button onclick="confirmDelete({{ $step->id }})" class="btn btn-sm btn-outline-danger waves-effect waves-light">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">{{ __('No workflow steps defined.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: '{{ __("Are you sure?") }}',
                text: "{{ __("You won't be able to revert this!") }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f46a6a',
                cancelButtonColor: '#34c38f',
                confirmButtonText: '{{ __("Yes, delete it!") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.delete(id);
                }
            })
        }
    </script>
</div>
