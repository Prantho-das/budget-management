<?php

namespace App\Services;

use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use Illuminate\Support\Facades\Auth;

class BudgetWorkflowService
{
    /**
     * Submit a budget for approval.
     */
    public function submit(BudgetEstimation $estimation)
    {
        $firstStep = \App\Models\WorkflowStep::where('is_active', true)->orderBy('order', 'asc')->first();

        if (!$firstStep) {
            return $this->transition($estimation, 'Released', 'Submitted and Auto-Released (No Workflow Defined)', 'approved', null);
        }

        $targetOfficeId = $this->determineTargetOffice($estimation, $firstStep);

        return $this->transition(
            $estimation,
            $firstStep->name,
            "Submitted for {$firstStep->name}",
            'submitted',
            $targetOfficeId,
            $firstStep->id
        );
    }

    /**
     * Approve a budget at the current stage.
     */
    public function approve(BudgetEstimation $estimation, $remarks = null)
    {
        if ($estimation->current_stage === 'Released') {
            throw new \Exception("Budget is already at the final stage.");
        }

        $currentStep = $estimation->workflowStep;
        $nextStep = \App\Models\WorkflowStep::where('is_active', true)
            ->where('order', '>', $currentStep ? $currentStep->order : 0)
            ->orderBy('order', 'asc')
            ->first();

        if ($currentStep && str_contains(strtolower($currentStep->name), 'release')) {
            abort_if(Auth::user()->cannot($currentStep->required_permission), 403, 'You do not have permission to release budgets.');
        }

        if (!$nextStep) {
            $nextStage = 'Released';
            $nextTargetId = null;
            $nextStepId = null;
        } else {
            $nextStage = $nextStep->name;
            $nextTargetId = $this->determineTargetOffice($estimation, $nextStep);
            $nextStepId = $nextStep->id;
        }

        $remarks = $remarks ?? "Approved by " . (Auth::user()->office->name ?? 'System');
        $transition = $this->transition($estimation, $nextStage, $remarks, null, $nextTargetId, $nextStepId);

        if ($nextStage === 'Released') {
            $this->createAllocation($estimation);
        }

        return $transition;
    }

    /**
     * Reject a budget.
     */
    public function reject(BudgetEstimation $estimation, $remarks = null)
    {
        return $this->transition($estimation, 'Draft', $remarks ?? "Rejected by " . (Auth::user()->office->name ?? 'System'), 'draft', null);
    }

    /**
     * Handle the transition logic.
     */
    protected function transition(BudgetEstimation $estimation, $newStage, $remarks, $status = null, $targetOfficeId = null, $workflowStepId = null)
    {
        $log = $estimation->approval_log ?? [];
        $log[] = [
            'from_stage' => $estimation->current_stage,
            'to_stage' => $newStage,
            'action_by' => Auth::id(),
            'action_name' => Auth::user()->name ?? 'System',
            'action_role' => Auth::user()->roles->first()->name ?? 'N/A',
            'action_at' => now()->toDateTimeString(),
            'remarks' => $remarks,
            'amount_demand' => $estimation->amount_demand,
            'amount_approved' => $estimation->amount_approved ?? $estimation->amount_demand
        ];

        $estimation->update([
            'current_stage' => $newStage,
            'target_office_id' => $targetOfficeId,
            'workflow_step_id' => $workflowStepId,
            'approval_log' => $log,
            'status' => $status ?? ($newStage === 'Released' ? 'approved' : 'submitted')
        ]);

        return $estimation;
    }

    protected function determineTargetOffice(BudgetEstimation $estimation, $step)
    {
        $originOffice = $estimation->office;

        switch ($step->office_level) {
            case 'origin':
                return $estimation->rpo_unit_id;
            case 'parent':
                // Simple parent logic: move up from current target if exists, else move from origin.
                // This allows sequential parent steps to climb the tree.
                $currentOfficeId = $estimation->target_office_id ?: $originOffice->id;
                $currentOffice = \App\Models\RpoUnit::find($currentOfficeId);
                return $currentOffice->parent_id ?: $currentOfficeId;
            case 'hq':
                $hq = \App\Models\RpoUnit::whereNull('parent_id')->first();
                return $hq ? $hq->id : $originOffice->id;
            default:
                return $originOffice->id;
        }
    }

    /**
     * Create the official allocation once fully approved.
     */
    protected function createAllocation(BudgetEstimation $estimation)
    {
        $allocation = BudgetAllocation::where([
            'fiscal_year_id' => $estimation->fiscal_year_id,
            'rpo_unit_id' => $estimation->rpo_unit_id,
            'economic_code_id' => $estimation->economic_code_id,
            'budget_type_id' => $estimation->budget_type_id,
        ])->first();

        $amount = $estimation->amount_approved ?? $estimation->amount_demand;

        if ($allocation) {
            return $allocation->update([
                'amount' => $allocation->amount + $amount,
                'remarks' => ($allocation->remarks ? $allocation->remarks . "\n" : "") . "Added from Demand Batch: {$estimation->id}"
            ]);
        }

        return BudgetAllocation::create([
            'fiscal_year_id' => $estimation->fiscal_year_id,
            'rpo_unit_id' => $estimation->rpo_unit_id,
            'economic_code_id' => $estimation->economic_code_id,
            'budget_type_id' => $estimation->budget_type_id,
            'amount' => $amount,
            'remarks' => "Initial allocation from Demand ID: {$estimation->id}"
        ]);
    }
}
