<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    public $roles, $name, $role_id;
    public $selectedPermissions = [];
    public $isOpen = false;

    public function render()
    {
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
    }

    public function store()
    {
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
            $this->role_id ? 'Role Updated Successfully.' : 'Role Created Successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->role_id = $id;
        $this->name = $role->name;

        // Load existing permissions
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->openModal();
    }

    protected $listeners = ['deleteConfirmed'];

    public function delete($id)
    {
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {
        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }
        Role::find($id)->delete();
        session()->flash('message', 'Role Deleted Successfully.');
    }
}
