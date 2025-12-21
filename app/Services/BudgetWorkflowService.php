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
        $parent = $estimation->office->parent;

        if (!$parent) {
            return $this->transition($estimation, 'Released', 'Submitted and Auto-Released (Top Level)', 'approved', null);
        }

        return $this->transition(
            $estimation, 
            "Waiting for {$parent->name}", 
            "Submitted for {$parent->name} review", 
            'submitted', 
            $parent->id
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

        $currentApproverOfficeId = $estimation->target_office_id;
        
        if (!$currentApproverOfficeId) {
            // This case shouldn't happen if logic is correct, but safety first
            $nextStage = 'Released';
            $nextTargetId = null;
        } else {
            $currentApproverOffice = \App\Models\RpoUnit::find($currentApproverOfficeId);
            $nextOffice = $currentApproverOffice->parent;

            if (!$nextOffice) {
                // This is the final release - check for release-budget permission
                abort_if(Auth::user()->cannot('release-budget'), 403, 'You do not have permission to release budgets.');
                $nextStage = 'Released';
                $nextTargetId = null;
            } else {
                $nextStage = "Waiting for {$nextOffice->name}";
                $nextTargetId = $nextOffice->id;
            }
        }

        $remarks = $remarks ?? "Approved by " . (Auth::user()->office->name ?? 'System');
        $transition = $this->transition($estimation, $nextStage, $remarks, null, $nextTargetId);

        // If it reached 'Released', we automatically create an Allocation entry
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
    protected function transition(BudgetEstimation $estimation, $newStage, $remarks, $status = null, $targetOfficeId = null)
    {
        $log = $estimation->approval_log ?? [];
        $log[] = [
            'from_stage' => $estimation->current_stage,
            'to_stage' => $newStage,
            'action_by' => Auth::id(),
            'action_name' => Auth::user()->name ?? 'System',
            'action_role' => Auth::user()->roles->first()->name ?? 'N/A',
            'action_at' => now()->toDateTimeString(),
            'remarks' => $remarks
        ];

        $estimation->update([
            'current_stage' => $newStage,
            'target_office_id' => $targetOfficeId,
            'approval_log' => $log,
            'status' => $status ?? ($newStage === 'Released' ? 'approved' : 'submitted')
        ]);

        return $estimation;
    }

    /**
     * Create the official allocation once fully approved.
     */
    protected function createAllocation(BudgetEstimation $estimation)
    {
        return BudgetAllocation::updateOrCreate(
            [
                'fiscal_year_id' => $estimation->fiscal_year_id,
                'rpo_unit_id' => $estimation->rpo_unit_id,
                'economic_code_id' => $estimation->economic_code_id,
                'budget_type_id' => $estimation->budget_type_id,
            ],
            [
                'amount' => $estimation->amount_approved ?? $estimation->amount_demand,
                'remarks' => "Automatically released from Demand ID: {$estimation->id}"
            ]
        );
    }
}
