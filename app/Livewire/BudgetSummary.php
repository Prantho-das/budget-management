<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetAllocation;
use App\Models\BudgetEstimation;
use App\Models\Expense;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\Auth;

class BudgetSummary extends Component
{
    public $fiscal_year_id;
    public $rpo_unit_id;

    public function mount()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);

        $this->fiscal_year_id = get_active_fiscal_year_id();
        $this->rpo_unit_id = auth()->user()->rpo_unit_id;
    }

    public function render()
    {
        if (!auth()->user()->can('view-all-offices-data')) {
            $this->rpo_unit_id = auth()->user()->rpo_unit_id;
        }

        $userOfficeId = $this->rpo_unit_id;

        // Budget Statistics
        $totalDraft = BudgetEstimation::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('status', 'draft')
            ->count();

        $totalSubmitted = BudgetEstimation::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('status', 'submitted')
            ->count();

        $totalReleased = BudgetEstimation::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('current_stage', 'Released')
            ->count();

        $totalRejected = BudgetEstimation::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('status', 'draft')
            ->whereJsonLength('approval_log', '>', 0)
            ->count();

        // Financial Summary
        $totalAllocated = BudgetAllocation::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->sum('amount');

        $totalExpenses = Expense::where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->sum('amount');

        $availableBalance = $totalAllocated - $totalExpenses;

        // Budget by Economic Code
        $budgetByCode = BudgetAllocation::with('economicCode')
            ->where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->get()
            ->map(function ($allocation) use ($userOfficeId) {
                $expenses = Expense::where('rpo_unit_id', $userOfficeId)
                    ->where('fiscal_year_id', $this->fiscal_year_id)
                    ->where('economic_code_id', $allocation->economic_code_id)
                    ->sum('amount');

                return [
                    'code' => $allocation->economicCode->code,
                    'name' => $allocation->economicCode->name,
                    'allocated' => $allocation->amount,
                    'spent' => $expenses,
                    'balance' => $allocation->amount - $expenses,
                    'utilization' => $allocation->amount > 0 ? ($expenses / $allocation->amount) * 100 : 0
                ];
            });

        // Grouped allocations for print report
        $allocations = BudgetAllocation::with(['economicCode'])
            ->where('rpo_unit_id', $userOfficeId)
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->get();

        $groupedAllocations = [];
        $grandTotal = 0;
        foreach ($allocations as $alloc) {
            $codeModel = $alloc->economicCode;
            if (!$codeModel) continue;

            $fullCode = $codeModel->code;
            $groupCode = substr($fullCode, 0, 4);

            $groupModel = \App\Models\EconomicCode::where('code', $groupCode)->first();
            $groupName = $groupModel ? $groupModel->name : __('Other');

            if (!isset($groupedAllocations[$groupCode])) {
                $groupedAllocations[$groupCode] = [
                    'group_code' => $groupCode,
                    'group_name' => $groupName,
                    'items' => [],
                    'subtotal' => 0
                ];
            }

            $groupedAllocations[$groupCode]['items'][] = [
                'code' => $fullCode,
                'name' => $codeModel->name,
                'amount' => $alloc->amount
            ];
            $groupedAllocations[$groupCode]['subtotal'] += $alloc->amount;
            $grandTotal += $alloc->amount;
        }

        $selectedOffice = \App\Models\RpoUnit::find($userOfficeId);
        $selectedFiscalYear = FiscalYear::find($this->fiscal_year_id);

        $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
        $offices = \App\Models\RpoUnit::all();

        return view('livewire.budget-summary', [
            'totalDraft' => $totalDraft,
            'totalSubmitted' => $totalSubmitted,
            'totalReleased' => $totalReleased,
            'totalRejected' => $totalRejected,
            'totalAllocated' => $totalAllocated,
            'totalExpenses' => $totalExpenses,
            'availableBalance' => $availableBalance,
            'budgetByCode' => $budgetByCode,
            'groupedAllocations' => $groupedAllocations,
            'grandTotal' => $grandTotal,
            'selectedOffice' => $selectedOffice,
            'selectedFiscalYear' => $selectedFiscalYear,
            'fiscalYears' => $fiscalYears,
            'offices' => $offices
        ])->extends('layouts.skot')->section('content');
    }
}
