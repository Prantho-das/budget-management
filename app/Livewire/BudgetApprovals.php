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
    public $selected_budget_type_id;
    public $childOffices = [];
    public $demands = []; // [economic_code_id => amount]
    public $remarks = []; // [unique_key => text]
    public $approval_remarks = []; // [estimation_id => text]
    public $previousDemands = []; // [economic_code_id => amount]
    public $selected_stage;
    public $selected_batch_id;
    public $viewMode = 'inbox'; // 'inbox' or 'detail'

    public function mount()
    {
        abort_if(auth()->user()->cannot('approve-budget') && auth()->user()->cannot('release-budget') && auth()->user()->cannot('reject-budget'), 403);
        $this->fiscal_year_id = get_active_fiscal_year_id();
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
            ->where(function ($query) {
                // If the demand is in a workflow step, the user must have the required permission
                $query->whereHas('workflowStep', function ($q) {
                    $permissions = auth()->user()->getAllPermissions()->pluck('name');
                    $q->whereIn('required_permission', $permissions);
                })
                    ->orWhereNull('workflow_step_id'); // Fallback for demands without a step (e.g. pre-existing or auto-release)
            })
            ->select('rpo_unit_id', 'budget_type_id', 'status', 'current_stage', 'workflow_step_id', 'batch_id', 
                DB::raw('SUM(amount_demand) as total_demand'), 
                DB::raw('COUNT(approver_remarks) as remarks_count'),
                DB::raw('COUNT(amount_approved) as approved_count'),
                DB::raw('MIN(created_at) as created_at'))
            ->groupBy('rpo_unit_id', 'budget_type_id', 'status', 'current_stage', 'workflow_step_id', 'batch_id')
            ->with(['office', 'workflowStep', 'budgetType'])
            ->get();

        $this->childOffices = [];
        foreach ($submissions as $sub) {
            $this->childOffices[] = [
                'id' => $sub->rpo_unit_id,
                'name' => $sub->office->name,
                'code' => $sub->office->code,
                'budget_type_id' => $sub->budget_type_id,
                'budget_type_name' => $sub->budgetType->name ?? 'N/A',
                'total_demand' => $sub->total_demand,
                'status' => $sub->status,
                'current_stage' => $sub->workflowStep ? $sub->workflowStep->name : $sub->current_stage,
                'batch_id' => $sub->batch_id,
                'created_at' => $sub->created_at,
                'is_drafted' => ($sub->remarks_count > 0 || $sub->approved_count > 0),
            ];
        }
    }

    public function saveAsDraft()
    {
        // Actually, updateAdjustment already saves to DB. 
        // This method just provides a formal "Save" feedback and returns to inbox.
        session()->flash('message', __('Draft saved successfully.'));
        $this->backToInbox();
    }

    public function viewDetails($officeId, $budgetTypeId, $currentStage, $batchId)
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $this->selected_office_id = $officeId;
        $this->selected_budget_type_id = $budgetTypeId;
        $this->selected_stage = $currentStage;
        $this->selected_batch_id = $batchId;
        $this->viewMode = 'detail';
        $this->loadDemands();
    }

    public function loadDemands()
    {
        $estimations = BudgetEstimation::where('batch_id', $this->selected_batch_id)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->with(['economicCode', 'fiscalYear'])
            ->get();

        $this->demands = [];
        $this->approval_remarks = [];

        if ($estimations->isEmpty()) return;

        $currentFiscalYear = $estimations->first()->fiscalYear;

        // Previous 3 Years Expense Data
        $this->previousDemands = [];
        $previousYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
            ->orderBy('end_date', 'desc')
            ->take(3)
            ->get()
            ->reverse(); // Reorder to Ascending (Oldest -> Newest) to match demand screen

        foreach ($previousYears as $index => $prevYear) {
            $expenses = \App\Models\Expense::where('fiscal_year_id', $prevYear->id)
                ->where('rpo_unit_id', $this->selected_office_id)
                ->selectRaw('economic_code_id, SUM(amount) as total_expense')
                ->groupBy('economic_code_id')
                ->get();

            foreach ($expenses as $expense) {
                if (!isset($this->previousDemands[$expense->economic_code_id])) {
                    $this->previousDemands[$expense->economic_code_id] = [];
                }
                $this->previousDemands[$expense->economic_code_id]["year_{$index}"] = [
                    'year' => $prevYear->name,
                    'amount' => $expense->total_expense
                ];
            }
        }
        foreach ($estimations as $est) {
            $this->demands[$est->economic_code_id] = [
                'id' => $est->id,
                'code' => $est->economicCode->code,
                'name' => $est->economicCode->name,
                'demand' => $est->amount_demand,
                'approved' => $est->amount_approved ?? $est->amount_demand,
                'status' => $est->status,
                'remarks' => $est->remarks,
                'approver_remarks' => $est->approver_remarks,
            ];
            $this->approval_remarks[$est->id] = $est->approver_remarks;
        }
    }

    public function backToInbox()
    {
        $this->viewMode = 'inbox';
        $this->loadChildSubmissions();
    }

    public function approve($officeId, $budgetTypeId, $currentStage, $batchId)
    {
        abort_if(auth()->user()->cannot('approve-budget') && auth()->user()->cannot('release-budget'), 403);

        $workflow = new \App\Services\BudgetWorkflowService();
        $estimations = BudgetEstimation::where('batch_id', $batchId)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->get();

        if ($estimations->isNotEmpty()) {
            $workflow->approveBatch($estimations);
        }

        session()->flash('message', __('Budget Approved Successfully.'));
        $this->backToInbox();
    }

    public function reject($officeId, $budgetTypeId, $currentStage, $batchId)
    {
        abort_if(auth()->user()->cannot('reject-budget'), 403);

        $key = $officeId . '_' . $budgetTypeId . '_' . str_replace(' ', '_', $currentStage) . '_' . $batchId;
        $this->validate([
            "remarks.$key" => 'required|min:5'
        ], [
            "remarks.$key.required" => __('Please provide a reason for rejection.')
        ]);

        $workflow = new \App\Services\BudgetWorkflowService();
        $estimations = BudgetEstimation::where('batch_id', $batchId)
            ->where('target_office_id', Auth::user()->rpo_unit_id)
            ->get();

        if ($estimations->isNotEmpty()) {
            $workflow->rejectBatch($estimations, $this->remarks[$key]);
        }

        session()->flash('message', __('Budget Rejected.'));
        $this->backToInbox();
    }

    public function updateAdjustment($estimationId, $amount, $remark = null)
    {
        // This should probably also have a permission check
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $est = BudgetEstimation::find($estimationId);
        if ($est && $est->current_stage !== 'Released') {
            $est->update([
                'amount_approved' => $amount,
                'approver_remarks' => $remark ?: ($this->approval_remarks[$estimationId] ?? null)
            ]);
        }
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('approve-budget') && auth()->user()->cannot('release-budget') && auth()->user()->cannot('reject-budget'), 403);
        return view('livewire.budget-approvals', [
            'office' => $this->selected_office_id ? RpoUnit::find($this->selected_office_id) : null
        ])->extends('layouts.skot')->section('content');
    }
}
