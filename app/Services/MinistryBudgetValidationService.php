<?php

namespace App\Services;

use App\Models\MinistryAllocation;
use App\Models\BudgetAllocation;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\DB;

class MinistryBudgetValidationService
{
    /**
     * Get available ministry budget for a specific criteria
     */
    public function getAvailableBudget($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId = null)
    {
        $query = MinistryAllocation::query()
            ->join('ministry_budget_masters', 'ministry_allocations.ministry_budget_master_id', '=', 'ministry_budget_masters.id')
            ->where('ministry_budget_masters.fiscal_year_id', $fiscalYearId)
            ->where('ministry_budget_masters.budget_type_id', $budgetTypeId);

        if ($rpoUnitId) {
            $query->where('ministry_budget_masters.rpo_unit_id', $rpoUnitId);
        }

        if ($economicCodeId) {
            $query->where('ministry_allocations.economic_code_id', $economicCodeId);
        }

        return $query->sum('ministry_allocations.amount');
    }

    /**
     * Get total released amount for a specific criteria
     */
    public function getReleasedAmount($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId = null)
    {
        $query = BudgetAllocation::query()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('budget_type_id', $budgetTypeId);

        if ($rpoUnitId) {
            $query->where('rpo_unit_id', $rpoUnitId);
        }

        if ($economicCodeId) {
            $query->where('economic_code_id', $economicCodeId);
        }

        return $query->sum('amount');
    }

    /**
     * Get remaining budget (Allocated - Released)
     */
    public function getRemainingBudget($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId = null)
    {
        $allocated = $this->getAvailableBudget($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId);
        $released = $this->getReleasedAmount($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId);

        return max(0, $allocated - $released);
    }

    /**
     * Validate potential budget release
     */
    public function validateRelease($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId, $amount)
    {
        // For release validation, we check GLOBAL limits unless specific RPO unit target is enforced from Ministry
        // Assuming ministry budget is distributed to Offices, we check if the HQ has enough "Pool" 
        // OR if the specific RPO unit was allocated budget directly from Ministry.
        
        // Strategy: 
        // 1. Check if specific RPO Unit has Ministry Allocation
        // 2. If not, check if HQ (Parent NULL) has Ministry Allocation and treat it as a pool
        
        $allocated = $this->getAvailableBudget($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId);
        
        if ($allocated <= 0) {
           // Fallback to HQ Pool check logic could go here if requirements specify
           // For now, strict validation: No Ministry Budget -> No Release.
             return [
                'valid' => false,
                'message' => __('No Ministry Budget allocation found for this Office and Economic Code.'),
                'remaining' => 0
            ];
        }

        $released = $this->getReleasedAmount($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId);
        $remaining = $allocated - $released;

        if ($amount > $remaining) {
            return [
                'valid' => false,
                'message' => __('Release amount exceeds remaining Ministry Budget. Remaining: ') . number_format($remaining, 2),
                'remaining' => $remaining
            ];
        }

        return [
            'valid' => true,
            'message' => __('Valid release request.'),
            'remaining' => $remaining - $amount
        ];
    }

    /**
     * Get comprehensive summary for all economic codes
     */
    public function getMinistryBudgetSummary($fiscalYearId, $rpoUnitId, $budgetTypeId)
    {
        // 1. Get Allocations grouped by economic_code
        $allocations = DB::table('ministry_allocations')
            ->join('ministry_budget_masters', 'ministry_allocations.ministry_budget_master_id', '=', 'ministry_budget_masters.id')
            ->join('economic_codes', 'ministry_allocations.economic_code_id', '=', 'economic_codes.id')
            ->where('ministry_budget_masters.fiscal_year_id', $fiscalYearId)
            ->where('ministry_budget_masters.budget_type_id', $budgetTypeId)
            ->when($rpoUnitId, function($q) use ($rpoUnitId) {
                return $q->where('ministry_budget_masters.rpo_unit_id', $rpoUnitId);
            })
            ->select(
                'ministry_allocations.economic_code_id',
                'economic_codes.code',
                'economic_codes.name',
                DB::raw('SUM(ministry_allocations.amount) as total_allocated')
            )
            ->groupBy('ministry_allocations.economic_code_id', 'economic_codes.code', 'economic_codes.name')
            ->get()
            ->keyBy('economic_code_id');

        // 2. Get Released Amounts grouped by economic_code
        $released = DB::table('budget_allocations')
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('budget_type_id', $budgetTypeId)
             ->when($rpoUnitId, function($q) use ($rpoUnitId) {
                return $q->where('rpo_unit_id', $rpoUnitId);
            })
            ->select(
                'economic_code_id',
                DB::raw('SUM(amount) as total_released')
            )
            ->groupBy('economic_code_id')
            ->get()
            ->keyBy('economic_code_id');

        $summary = [];

        foreach ($allocations as $codeId => $alloc) {
            $relAmount = $released[$codeId]->total_released ?? 0;
            $remaining = $alloc->total_allocated - $relAmount;
            $percent = $alloc->total_allocated > 0 ? ($relAmount / $alloc->total_allocated) * 100 : 0;

            $summary[$codeId] = [
                'code_id' => $codeId,
                'code' => $alloc->code,
                'code_name' => "{$alloc->code} - {$alloc->name}",
                'allocated' => (float) $alloc->total_allocated,
                'released' => (float) $relAmount,
                'remaining' => (float) $remaining,
                'usage_percent' => round($percent, 2)
            ];
        }

        return $summary;
    }
}
