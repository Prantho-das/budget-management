<?php

namespace App\Livewire\Setup;

use App\Models\User;
use App\Models\RpoUnit;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class Users extends Component
{
    use WithPagination;

    public $name, $email, $password, $user_id, $rpo_unit_id, $role;
    public $isOpen = false;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $users = User::with(['office', 'roles'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.setup.users', [
            'users' => $users,
            'offices' => RpoUnit::all(),
            'roles' => Role::all()
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
        $this->email = '';
        $this->password = '';
        $this->user_id = '';
        $this->rpo_unit_id = '';
        $this->role = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'password' => $this->user_id ? 'nullable|min:6' : 'required|min:6',
            'rpo_unit_id' => 'required',
            'role' => 'required',
        ]);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'rpo_unit_id' => $this->rpo_unit_id,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->user_id], $userData);
        $user->syncRoles([$this->role]);

        session()->flash(
            'message',
            $this->user_id ? 'User Updated/Transferred Successfully.' : 'User Created Successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->rpo_unit_id = $user->rpo_unit_id;
        $this->role = $user->roles->first()?->name;

        $this->openModal();
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'User Deleted Successfully.');
    }
}
