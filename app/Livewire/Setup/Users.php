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

    public $name, $username, $phone, $designation, $email, $password, $user_id, $rpo_unit_id, $role;
    public $isOpen = false;
    public $search = '';
    public $transferHistory = [];
    public $showTransferHistoryModal = false;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        abort_if(auth()->user()->cannot('view-users'), 403);
        $users = User::with(['office', 'roles'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
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
        abort_if(auth()->user()->cannot('create-users'), 403);
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
        $this->username = '';
        $this->phone = '';
        $this->designation = '';
        $this->email = '';
        $this->password = '';
        $this->user_id = '';
        $this->rpo_unit_id = '';
        $this->role = '';
    }

    public function store()
    {
        if ($this->user_id) {
            abort_if(auth()->user()->cannot('edit-users'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-users'), 403);
        }

        $this->validate([
            'name' => 'required',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user_id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $this->user_id,
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'password' => $this->user_id ? 'nullable|min:6' : 'required|min:6',
            'rpo_unit_id' => 'required',
            'role' => 'required',
        ]);

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'designation' => $this->designation,
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
            $this->user_id ? __('User Updated/Transferred Successfully.') : __('User Created Successfully.')
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-users'), 403);
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->phone = $user->phone;
        $this->designation = $user->designation;
        $this->email = $user->email;
        $this->rpo_unit_id = $user->rpo_unit_id;
        $this->role = $user->roles->first()?->name;

        $this->openModal();
    }

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-users'), 403);
        User::find($id)->delete();
        session()->flash('message', __('User Deleted Successfully.'));
    }

    public function showHistory($userId)
    {
        abort_if(auth()->user()->cannot('view-transfer-history'), 403);
        $this->transferHistory = \App\Models\UserOfficeTransfer::with(['fromOffice', 'toOffice', 'creator'])
            ->where('user_id', $userId)
            ->orderBy('transfer_date', 'desc')
            ->get();
        $this->showTransferHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showTransferHistoryModal = false;
        $this->transferHistory = [];
    }
}
