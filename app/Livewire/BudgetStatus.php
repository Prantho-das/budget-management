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

        $fiscalYear = FiscalYear::where('status', true)->latest()->first();
        $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;
        $this->rpo_unit_id = Auth::user()->rpo_unit_id;
        $this->budget_type_id = BudgetType::where('status', true)->orderBy('order_priority')->first()?->id;
    }

    public function viewHistory($economicCodeId)
    {
        $estimation = BudgetEstimation::where([
            'fiscal_year_id' => $this->fiscal_year_id,
            'rpo_unit_id' => $this->rpo_unit_id,
            'budget_type_id' => $this->budget_type_id,
            'economic_code_id' => $economicCodeId
        ])->first();

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

        $allocations = BudgetAllocation::with(['economicCode', 'budgetType'])
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->rpo_unit_id)
            ->where('budget_type_id', $this->budget_type_id)
            ->get();

        $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
        $offices = \App\Models\RpoUnit::all();

        return view('livewire.budget-status', [
            'allocations' => $allocations,
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'offices' => $offices
        ])->extends('layouts.skot')->section('content');
    }
}
