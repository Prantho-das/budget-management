<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetAllocation;
use App\Models\BudgetEstimation;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\BudgetType;
use Illuminate\Support\Facades\Auth;

class BudgetStatus extends Component
{
    public $fiscal_year_id;
    public $rpo_unit_id;
    public $budget_type_id;

    public $selectedLog = [];
    public $showLogModal = false;

    public function mount()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);

        $this->fiscal_year_id = get_active_fiscal_year_id();
        $this->rpo_unit_id = Auth::user()->rpo_unit_id;
        $budgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
        $this->budget_type_id = $budgetType ? $budgetType->id : null;
    }

    public function viewHistory($estimationId)
    {
        $estimation = BudgetEstimation::find($estimationId);
        $this->selectedLog = $estimation ? ($estimation->approval_log ?? []) : [];
        $this->showLogModal = true;
    }

    public function closeLogModal()
    {
        $this->showLogModal = false;
        $this->selectedLog = [];
    }

    public function render()
    {
        if (!auth()->user()->can('view-all-offices-data')) {
            $this->rpo_unit_id = auth()->user()->rpo_unit_id;
        }

        $estimations = BudgetEstimation::with(['economicCode', 'budgetType', 'office', 'targetOffice', 'workflowStep'])
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('budget_type_id', $this->budget_type_id)
            ->when($this->rpo_unit_id, function($query) {
                return $query->where('rpo_unit_id', $this->rpo_unit_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
        $offices = \App\Models\RpoUnit::all();

        return view('livewire.budget-status', [
            'estimations' => $estimations,
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'offices' => $offices
        ])->extends('layouts.skot')->section('content');
    }
}
