<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\BudgetType;

class BudgetTypes extends Component
{
    public $types, $name, $code, $order_priority, $status = true, $type_id;
    public $isOpen = false;

    protected $listeners = ['deleteConfirmed'];

    public function render()
    {
        abort_if(auth()->user()->cannot('view-budget-types'), 403);
        $this->types = BudgetType::orderBy('order_priority')->get();
        return view('livewire.setup.budget-types')
            ->extends('layouts.skot')
            ->section('content');
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('create-budget-types'), 403);
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
        $this->code = '';
        $this->order_priority = '';
        $this->status = true;
        $this->type_id = '';
    }

    public function store()
    {
        if ($this->type_id) {
            abort_if(auth()->user()->cannot('edit-budget-types'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-budget-types'), 403);
        }

        $this->validate([
            'name' => 'required',
            'code' => 'required|unique:budget_types,code,' . $this->type_id,
            'order_priority' => 'required|numeric',
        ]);

        BudgetType::updateOrCreate(['id' => $this->type_id], [
            'name' => $this->name,
            'code' => $this->code,
            'order_priority' => $this->order_priority,
            'status' => $this->status,
        ]);

        session()->flash(
            'message',
            $this->type_id ? __('Budget Type Updated Successfully.') : __('Budget Type Created Successfully.')
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-budget-types'), 403);
        $type = BudgetType::findOrFail($id);
        $this->type_id = $id;
        $this->name = $type->name;
        $this->code = $type->code;
        $this->order_priority = $type->order_priority;
        $this->status = $type->status;

        $this->openModal();
    }

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-budget-types'), 403);
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {
        abort_if(auth()->user()->cannot('delete-budget-types'), 403);
        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }
        BudgetType::find($id)->delete();
        session()->flash('message', __('Budget Type Deleted Successfully.'));
    }
}
