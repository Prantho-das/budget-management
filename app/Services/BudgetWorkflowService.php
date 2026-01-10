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
    /**
     * Approve a batch of estimations.
     */
    public function approveBatch($estimations)
    {
        if ($estimations->isEmpty()) return;

        $firstEst = $estimations->first();
        if ($firstEst->current_stage === 'Released') {
            throw new \Exception("Budget is already at the final stage.");
        }

        $currentStep = $firstEst->workflowStep;
        $nextStep = \App\Models\WorkflowStep::where('is_active', true)
            ->where('order', '>', $currentStep ? $currentStep->order : 0)
            ->orderBy('order', 'asc')
            ->first();

        // Permission check (once for the batch)
        if ($currentStep && $currentStep->required_permission) {
            abort_if(Auth::user()->cannot($currentStep->required_permission), 403, "You do not have the required permission.");
        }

        // Determine next path (once for the batch)
        if (!$nextStep) {
            $nextStage = 'Released';
            $nextTargetId = null;
            $nextStepId = null;
        } else {
            $nextStage = $nextStep->name;
            $nextTargetId = $this->determineTargetOffice($firstEst, $nextStep);
            $nextStepId = $nextStep->id;
        }

        $remarks = "Approved by " . (Auth::user()->office->name ?? 'System');
        $this->batchTransition($estimations, $nextStage, $remarks, null, $nextTargetId, $nextStepId);

        if ($nextStage === 'Released') {
            foreach ($estimations as $est) {
                $this->createAllocation($est);
            }
        }
    }

    /**
     * Reject a batch of estimations.
     */
    public function rejectBatch($estimations, $remarks = null)
    {
        $remarks = $remarks ?? "Rejected by " . (Auth::user()->office->name ?? 'System');
        $this->batchTransition($estimations, 'Draft', $remarks, 'draft', null);
    }

    protected function batchTransition($estimations, $newStage, $remarks, $status = null, $targetOfficeId = null, $workflowStepId = null)
    {
        $user = Auth::user();
        $actionBy = $user->id;
        $actionName = $user->name ?? 'System';
        $actionRole = $user->roles->first()->name ?? 'N/A';
        $actionAt = now()->toDateTimeString();

        foreach ($estimations as $estimation) {
            $log = $estimation->approval_log ?? [];
            $log[] = [
                'from_stage' => $estimation->current_stage,
                'to_stage' => $newStage,
                'action_by' => $actionBy,
                'action_name' => $actionName,
                'action_role' => $actionRole,
                'action_at' => $actionAt,
                'remarks' => $remarks,
                'amount_demand' => $estimation->amount_demand,
                'amount_approved' => $estimation->amount_approved ?? $estimation->amount_demand
            ];

            // We still update individually because JSON fields and 'amount_approved' might vary per row if edited
            $estimation->update([
                'current_stage' => $newStage,
                'target_office_id' => $targetOfficeId,
                'workflow_step_id' => $workflowStepId,
                'approval_log' => $log,
                'status' => $status ?? ($newStage === 'Released' ? 'approved' : 'submitted')
            ]);
        }
    }

    /**
     * Approve a single budget (wrapper for backward compatibility or single use).
     */
    public function approve(BudgetEstimation $estimation, $remarks = null)
    {
        $this->approveBatch(collect([$estimation]));
    }

    /**
     * Reject a single budget.
     */
    public function reject(BudgetEstimation $estimation, $remarks = null)
    {
        $this->rejectBatch(collect([$estimation]), $remarks);
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
        $targetOfficeId = null;

        switch ($step->office_level) {
            case 'origin':
                $targetOfficeId = $estimation->rpo_unit_id;
                break;
            case 'parent':
                $currentOfficeId = $estimation->target_office_id ?: $originOffice->id;
                $currentOffice = \App\Models\RpoUnit::find($currentOfficeId);
                $targetOfficeId = $currentOffice->parent_id ?: $currentOfficeId;
                break;
            case 'hq':
                $hq = \App\Models\RpoUnit::whereNull('parent_id')->first();
                $targetOfficeId = $hq ? $hq->id : $originOffice->id;
                break;
            default:
                $targetOfficeId = $originOffice->id;
        }

        // Optimization: Automatic Escalation
        // If the office has no users with the required permission, climb to HQ
        if ($step->required_permission && $step->office_level !== 'origin') {
            $hasUsers = \App\Models\User::where('rpo_unit_id', $targetOfficeId)
                ->where(function($query) use ($step) {
                    $query->whereHas('permissions', fn($q) => $q->where('name', $step->required_permission))
                          ->orWhereHas('roles.permissions', fn($q) => $q->where('name', $step->required_permission));
                })->exists();

            if (!$hasUsers) {
                $hq = \App\Models\RpoUnit::whereNull('parent_id')->first();
                return $hq ? $hq->id : $targetOfficeId;
            }
        }

        return $targetOfficeId;
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
