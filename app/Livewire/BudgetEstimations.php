<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetEstimation;
use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetType;
use App\Services\BudgetWorkflowService;

class BudgetEstimations extends Component
{
    public $fiscal_year_id;
    public $rpo_unit_id;
    public $demands = []; // [economic_code_id => amount]
    public $remarks = []; // [economic_code_id => remark]
    public $previousDemands = []; // [economic_code_id => amount]
    public $budget_type_id;
    public $status = 'draft';
    public $current_stage = 'Draft';
    public $batch_id;
    public $allBatches = [];
    public $is_pending = false;
    public $is_released = false;
    public $has_existing_batch = false;

    public function mount()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        $this->fiscal_year_id = get_active_fiscal_year_id();

        $this->rpo_unit_id = Auth::user()->rpo_unit_id;

        // Determine default budget type: Original if first time, otherwise next active type
        $activeTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
        $defaultTypeId = null;

        foreach ($activeTypes as $type) {
            $defaultTypeId = $type->id;

            $isReleased = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->where('budget_type_id', $type->id)
                ->where('current_stage', 'Released')
                ->exists();

            if (!$isReleased) {
                break;
            }
        }

        // Fallback if all are submitted or none found
        if (!$defaultTypeId && $activeTypes->count() > 0) {
            $defaultTypeId = $activeTypes->first()->id;
        }

        $this->budget_type_id = $defaultTypeId;

        $this->loadDemands();
    }

    public function updatedBudgetTypeId()
    {
        $this->batch_id = null; // Clear batch when budget type changes to reload latest
        $this->loadDemands();
    }

    public function updatedBatchId()
    {
        $this->loadDemands();
    }

    public function startNewDemand()
    {
        if ($this->has_existing_batch) {
            session()->flash('error', __('You cannot start a new demand while a batch already exists for this budget type.'));
            return;
        }
        $this->batch_id = (string) \Illuminate\Support\Str::uuid();
        $this->demands = [];
        $this->remarks = [];
        $this->status = 'draft';
        $this->current_stage = 'Draft';
    }

    public function loadDemands()
    {
        if (!$this->fiscal_year_id || !$this->rpo_unit_id || !$this->budget_type_id) return;

        // Load available batches for selector
        $this->allBatches = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->rpo_unit_id)
            ->where('budget_type_id', $this->budget_type_id)
            ->select('batch_id', 'status', 'current_stage', DB::raw('MIN(created_at) as created_at'))
            ->groupBy('batch_id', 'status', 'current_stage')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        if (!$this->batch_id) {
            $latestBatch = collect($this->allBatches)->first();
            if ($latestBatch) {
                $this->batch_id = $latestBatch['batch_id'];
            } else {
                $this->batch_id = (string) \Illuminate\Support\Str::uuid();
            }
        }

        // Check for existing batches and statuses
        $this->has_existing_batch = !empty($this->allBatches);

        // Batch check in single count query if possible, or just re-use allBatches
        $this->is_pending = collect($this->allBatches)->contains('status', 'submitted');
        $this->is_released = collect($this->allBatches)->contains('current_stage', 'Released');

        $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);

        // Current Year Demands for selected batch
        $estimations = BudgetEstimation::where('batch_id', $this->batch_id)->get();

        $this->demands = [];
        $this->remarks = [];
        foreach ($estimations as $estimation) {
            $this->demands[$estimation->economic_code_id] = $estimation->amount_demand;
            $this->remarks[$estimation->economic_code_id] = $estimation->remarks;
        }

        // Optimized Previous 3 Years Expense Data
        $this->previousDemands = [];
        $previousYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
            ->orderBy('end_date', 'desc')
            ->take(3)
            ->get()
            ->reverse()
            ->values();

        $pastFyIds = $previousYears->pluck('id')->toArray();
        $rawExpenses = \App\Models\Expense::whereIn('fiscal_year_id', $pastFyIds)
            ->where('rpo_unit_id', $this->rpo_unit_id)
            ->get()
            ->groupBy(['economic_code_id', 'fiscal_year_id']);

        foreach ($rawExpenses as $codeId => $byFiscalYear) {
            foreach ($previousYears as $index => $prevYear) {
                if (isset($byFiscalYear[$prevYear->id])) {
                    $this->previousDemands[$codeId]["year_{$index}"] = [
                        'year' => $prevYear->name,
                        'amount' => $byFiscalYear[$prevYear->id]->sum('amount')
                    ];
                }
            }
        }

        // Auto-populate for new drafts (10% increase from previous year)
        if ($estimations->isEmpty()) {
            $latestIndex = count($previousYears) - 1;
            foreach ($this->previousDemands as $codeId => $years) {
                if ($latestIndex >= 0 && isset($years["year_{$latestIndex}"]['amount'])) {
                    $this->demands[$codeId] = round($years["year_{$latestIndex}"]['amount'] * 1.10);
                }
            }
        }

        if ($estimations->isNotEmpty()) {
            $this->status = $estimations->first()->status;
            $this->current_stage = $estimations->first()->current_stage;
        } else {
            $this->status = 'draft';
            $this->current_stage = 'Draft';
        }
    }

    public function applySuggestion($codeId)
    {
        $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);
        $prevYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
            ->orderBy('end_date', 'desc')
            ->take(3)
            ->get();

        $latestIndex = count($prevYears) - 1;
        // We reverse earlier in logic, but internal index is stable if we use the right key.
        // In loadDemands we use reverse()->values(), so year_2 is latest.

        $latestYearKey = "year_" . (count($prevYears) - 1);

        if (isset($this->previousDemands[$codeId][$latestYearKey]['amount'])) {
            $baseline = $this->previousDemands[$codeId][$latestYearKey]['amount'];
            $suggested = round($baseline * 1.10);
            $this->demands[$codeId] = $suggested;
        }
    }

    public function saveDraft()
    {
        abort_if(auth()->user()->cannot('create-budget-estimations'), 403);
        $this->persist('draft');
        $this->loadDemands(); // Refresh batch list and status
        session()->flash('message', __('Budget Draft Saved Successfully.'));
    }

    public function submitForApproval()
    {
        abort_if(auth()->user()->cannot('submit-budget-estimations'), 403);
        $this->persist('submitted');

        $estimations = BudgetEstimation::where('batch_id', $this->batch_id)
            ->get();

        $workflow = new BudgetWorkflowService();
        foreach ($estimations as $estimation) {
            $workflow->submit($estimation);
        }

        $this->loadDemands();
        session()->flash('message', __('Budget Submitted for Approval.'));
    }

    private function persist($status)
    {
        if (!$this->fiscal_year_id || !$this->rpo_unit_id || !$this->budget_type_id) return;

        if ($this->status !== 'draft' && $this->status !== 'rejected') {
            session()->flash('error', __('Budget already submitted or approved and cannot be edited.'));
            return;
        }

        foreach ($this->demands as $code_id => $amount) {
            $remark = $this->remarks[$code_id] ?? null;
            if ($amount > 0 || $remark || BudgetEstimation::where([
                'batch_id' => $this->batch_id,
                'economic_code_id' => $code_id,
            ])->exists()) {
                BudgetEstimation::updateOrCreate(
                    [
                        'fiscal_year_id' => $this->fiscal_year_id,
                        'budget_type_id' => $this->budget_type_id,
                        'rpo_unit_id'    => $this->rpo_unit_id,
                        'economic_code_id' => $code_id,
                        'batch_id' => $this->batch_id,
                    ],
                    [
                        'amount_demand' => $amount ?: 0,
                        'remarks' => $remark,
                        'status' => $status,
                        'current_stage' => 'Draft'
                    ]
                );
            }
        }
        $this->status = $status;
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
        // Fetch Hierarchical Economic Codes (Root -> Layer 2 -> Layer 3)
        $economicCodes = EconomicCode::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('code')->with(['children' => function($q2) {
                    $q2->orderBy('code');
                }]);
            }])
            ->orderBy('code')
            ->get();
        $fiscalYear = FiscalYear::find($this->fiscal_year_id);
        $office = RpoUnit::find($this->rpo_unit_id);

        $allTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();

        $availableTypes = [];

        foreach ($allTypes as $type) {
            $isReleased = BudgetEstimation::where([
                'fiscal_year_id' => $this->fiscal_year_id,
                'rpo_unit_id' => $this->rpo_unit_id,
                'budget_type_id' => $type->id,
                'current_stage' => 'Released'
            ])->exists();

            $availableTypes[] = $type;

            if (!$isReleased) {
                break;
            }
        }

        return view('livewire.budget-estimations', [
            'economicCodes' => $economicCodes,
            'currentFiscalYear' => $fiscalYear,
            'currentOffice' => $office,
            'budgetTypes' => collect($availableTypes),
            'totalDemand' => array_sum($this->demands)
        ])->extends('layouts.skot')->section('content');
    }
}
