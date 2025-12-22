<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\WorkflowStep;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class WorkflowManagement extends Component
{
    public $steps;
    public $permissions;

    // Form fields
    public $name;
    public $required_permission;
    public $office_level = 'parent';
    public $order = 0;
    public $is_active = true;
    public $editingStepId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'required_permission' => 'required|string',
        'office_level' => 'required|in:origin,parent,hq',
        'order' => 'required|integer',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->loadSteps();
        $this->permissions = Permission::all();
    }

    public function loadSteps()
    {
        $this->steps = WorkflowStep::orderBy('order')->get();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingStepId) {
            $step = WorkflowStep::find($this->editingStepId);
            $step->update([
                'name' => $this->name,
                'required_permission' => $this->required_permission,
                'office_level' => $this->office_level,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            $this->editingStepId = null;
            session()->flash('message', __('Workflow Step Updated Successfully.'));
        } else {
            // Find max order if not set
            if ($this->order == 0) {
                $this->order = (WorkflowStep::max('order') ?? 0) + 1;
            }

            WorkflowStep::create([
                'name' => $this->name,
                'required_permission' => $this->required_permission,
                'office_level' => $this->office_level,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', __('Workflow Step Created Successfully.'));
        }

        $this->resetForm();
        $this->loadSteps();
    }

    public function edit($id)
    {
        $step = WorkflowStep::find($id);
        $this->editingStepId = $id;
        $this->name = $step->name;
        $this->required_permission = $step->required_permission;
        $this->office_level = $step->office_level;
        $this->order = $step->order;
        $this->is_active = $step->is_active;
    }

    public function delete($id)
    {
        WorkflowStep::find($id)->delete();
        $this->loadSteps();
        session()->flash('message', __('Workflow Step Deleted.'));
    }

    public function moveUp($id)
    {
        $step = WorkflowStep::find($id);
        $previous = WorkflowStep::where('order', '<', $step->order)->orderBy('order', 'desc')->first();

        if ($previous) {
            $oldOrder = $step->order;
            $step->update(['order' => $previous->order]);
            $previous->update(['order' => $oldOrder]);
        }
        $this->loadSteps();
    }

    public function moveDown($id)
    {
        $step = WorkflowStep::find($id);
        $next = WorkflowStep::where('order', '>', $step->order)->orderBy('order', 'asc')->first();

        if ($next) {
            $oldOrder = $step->order;
            $step->update(['order' => $next->order]);
            $next->update(['order' => $oldOrder]);
        }
        $this->loadSteps();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->required_permission = '';
        $this->office_level = 'parent';
        $this->order = 0;
        $this->is_active = true;
        $this->editingStepId = null;
    }

    public function render()
    {
        return view('livewire.setup.workflow-management')
            ->extends('layouts.skot')
            ->section('content');
    }
}
