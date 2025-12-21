<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Permissions extends Component
{
    public $permissions, $name, $group_name, $permission_id;
    public $isOpen = false;

    public function render()
    {
        abort_if(auth()->user()->cannot('view-permissions'), 403);
        $this->permissions = Permission::orderBy('group_name')->get();
        return view('livewire.setup.permissions')->extends('layouts.skot')->section('content');
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('create-permissions'), 403);
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
        $this->group_name = '';
        $this->permission_id = '';
    }

    public function store()
    {
        if ($this->permission_id) {
            abort_if(auth()->user()->cannot('edit-permissions'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-permissions'), 403);
        }

        $this->validate([
            'name' => 'required|unique:permissions,name,' . $this->permission_id,
            'group_name' => 'required',
        ]);

        Permission::updateOrCreate(['id' => $this->permission_id], [
            'name' => $this->name,
            'group_name' => $this->group_name
        ]);

        session()->flash(
            'message',
            $this->permission_id ? __('Permission Updated Successfully.') : __('Permission Created Successfully.')
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-permissions'), 403);
        $permission = Permission::findOrFail($id);
        $this->permission_id = $id;
        $this->name = $permission->name;
        $this->group_name = $permission->group_name;

        $this->openModal();
    }

    protected $listeners = ['deleteConfirmed'];

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-permissions'), 403);
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {
        abort_if(auth()->user()->cannot('delete-permissions'), 403);
        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }
        Permission::find($id)->delete();
        session()->flash('message', __('Permission Deleted Successfully.'));
    }
}
