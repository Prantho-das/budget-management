<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetApprovals extends Component
{
    public $fiscal_year_id;
    public $selected_office_id;
    public $selected_budget_type;
    public $childOffices = [];
    public $demands = []; // [economic_code_id => amount]
    public $remarks = []; // [unique_key => text]
    public $viewMode = 'inbox'; // 'inbox' or 'detail'

    public function mount()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $fiscalYear = FiscalYear::where('status', true)->latest()->first();
        $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;
        $this->loadChildSubmissions();
    }

    public function loadChildSubmissions()
    {
        if (!$this->fiscal_year_id) return;

        $userOfficeId = Auth::user()->rpo_unit_id;

        // Find all submissions targetted at THIS office
        $submissions = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('target_office_id', $userOfficeId)
            ->where('status', '!=', 'draft')
            ->select('rpo_unit_id', 'budget_type_id', 'status', DB::raw('SUM(amount_demand) as total_demand'))
            ->groupBy('rpo_unit_id', 'budget_type_id', 'status')
            ->with('office')
            ->get();

        $this->childOffices = [];
        foreach ($submissions as $sub) {
            $this->childOffices[] = [
                'id' => $sub->rpo_unit_id,
                'name' => $sub->office->name,
                'code' => $sub->office->code,
                'budget_type_id' => $sub->budget_type_id,
                'total_demand' => $sub->total_demand,
                'status' => $sub->status,
            ];
        }
    }

    public function viewDetails($officeId, $budgetTypeId)
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $this->selected_office_id = $officeId;
        $this->selected_budget_type_id = $budgetTypeId;
        $this->viewMode = 'detail';
        $this->loadDemands();
    }

    public function loadDemands()
    {
        $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->selected_office_id)
            ->where('budget_type_id', $this->selected_budget_type_id)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->with('economicCode')
            ->get();

        $this->demands = [];
        foreach ($estimations as $est) {
            $this->demands[$est->economic_code_id] = [
                'id' => $est->id,
                'code' => $est->economicCode->code,
                'name' => $est->economicCode->name,
                'demand' => $est->amount_demand,
                'approved' => $est->amount_approved ?? $est->amount_demand,
                'status' => $est->status,
                'remarks' => $est->remarks,
            ];
        }
    }

    public function backToInbox()
    {
        $this->viewMode = 'inbox';
        $this->loadChildSubmissions();
    }

    public function approve($officeId, $budgetTypeId)
    {
        abort_if(auth()->user()->cannot('approve-budget'), 403);

        $workflow = new \App\Services\BudgetWorkflowService();
        $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $officeId)
            ->where('budget_type_id', $budgetTypeId)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->get();

        foreach($estimations as $est) {
            $workflow->approve($est);
        }

        session()->flash('message', __('Budget Approved Successfully.'));
        $this->backToInbox();
    }

    public function reject($officeId, $budgetTypeId)
    {
        abort_if(auth()->user()->cannot('reject-budget'), 403);

        $key = $officeId . '_' . $budgetTypeId;
        $this->validate([
            "remarks.$key" => 'required|min:5'
        ], [
            "remarks.$key.required" => __('Please provide a reason for rejection.')
        ]);

        $workflow = new \App\Services\BudgetWorkflowService();
        $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $officeId)
            ->where('budget_type_id', $budgetTypeId)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->get();

        foreach($estimations as $est) {
            $workflow->reject($est, $this->remarks[$key]);
        }

        session()->flash('message', __('Budget Rejected.'));
        $this->backToInbox();
    }

    public function updateAdjustment($estimationId, $amount)
    {
        // This should probably also have a permission check
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $est = BudgetEstimation::find($estimationId);
        if ($est && $est->current_stage !== 'Released') {
            $est->update(['amount_approved' => $amount]);
        }
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        return view('livewire.budget-approvals', [
            'office' => $this->selected_office_id ? RpoUnit::find($this->selected_office_id) : null
        ])->extends('layouts.skot')->section('content');
    }
}
