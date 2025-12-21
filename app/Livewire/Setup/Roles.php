<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    public $roles, $name, $role_id;
    public $selectedPermissions = [];
    public $selectAll = false;
    public $isOpen = false;

    public function render()
    {
        abort_if(auth()->user()->cannot('view-roles'), 403);
        $this->roles = Role::with('permissions')->orderBy('id', 'desc')->get();
        // Fetch permissions and group them by group_name
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('group_name');

        return view('livewire.setup.roles', [
            'groupedPermissions' => $groupedPermissions
        ])->extends('layouts.skot')->section('content');
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('create-roles'), 403);
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->role_id = '';
        $this->selectedPermissions = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPermissions = Permission::pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function toggleGroup($groupName)
    {
        $groupPermissions = Permission::where('group_name', $groupName)->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
        // If all group permissions are already selected, deselect them
        if (array_intersect($groupPermissions, $this->selectedPermissions) == $groupPermissions) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            // Otherwise, select them all (avoiding duplicates)
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }

        $this->updateSelectAllStatus();
    }

    public function updatedSelectedPermissions()
    {
        $this->updateSelectAllStatus();
    }

    private function updateSelectAllStatus()
    {
        $allPermissionIds = Permission::pluck('id')->map(fn($id) => (string) $id)->toArray();
        if (count($this->selectedPermissions) === count($allPermissionIds)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function store()
    {
        if ($this->role_id) {
            abort_if(auth()->user()->cannot('edit-roles'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-roles'), 403);
        }

        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->role_id,
        ]);

        $role = Role::updateOrCreate(['id' => $this->role_id], [
            'name' => $this->name,
            'guard_name' => 'web' // Ensure guard is set
        ]);

        // Sync permissions
        // Livewire binds checkboxes as strings ("1", "2"). 
        // Spatie treats strings as Permission Names. We must cast to int for IDs.
        $permissions = array_map('intval', $this->selectedPermissions);
        $role->syncPermissions($permissions);

        session()->flash(
            'message',
            $this->role_id ? __('Role Updated Successfully.') : __('Role Created Successfully.')
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-roles'), 403);
        $role = Role::findOrFail($id);
        $this->role_id = $id;
        $this->name = $role->name;

        // Load existing permissions
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->updateSelectAllStatus();

        $this->openModal();
    }

    protected $listeners = ['deleteConfirmed'];

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-roles'), 403);
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {
        abort_if(auth()->user()->cannot('delete-roles'), 403);
        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }
        Role::find($id)->delete();
        session()->flash('message', __('Role Deleted Successfully.'));
    }
}
