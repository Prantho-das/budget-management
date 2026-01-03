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

  public function mount()
  {
    abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
    $this->fiscal_year_id = get_active_fiscal_year_id();

    $this->rpo_unit_id = Auth::user()->rpo_unit_id;

    // Determine default budget type: Original if first time, otherwise next active type
    $activeTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
    $defaultTypeId = null;

    foreach ($activeTypes as $type) {
        $exists = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->rpo_unit_id)
            ->where('budget_type_id', $type->id)
            ->exists();
        
        if (!$exists) {
            $defaultTypeId = $type->id;
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

    $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);

    // Current Year Demands for selected batch
    $estimations = BudgetEstimation::where('batch_id', $this->batch_id)
      ->get();

    foreach ($estimations as $estimation) {
      $this->demands[$estimation->economic_code_id] = $estimation->amount_demand;
      $this->remarks[$estimation->economic_code_id] = $estimation->remarks;
    }

    // Previous 3 Years Expense Data
    $this->previousDemands = [];

    $previousYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
      ->orderBy('end_date', 'desc')
      ->take(3)
      ->get();

    foreach ($previousYears as $index => $prevYear) {
      $expenses = \App\Models\Expense::where('fiscal_year_id', $prevYear->id)
        ->where('rpo_unit_id', $this->rpo_unit_id)
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

    // Auto-populate for new drafts (10% increase from previous year)
    if ($estimations->isEmpty()) {
      foreach ($this->previousDemands as $codeId => $years) {
        if (isset($years['year_0']['amount'])) {
          $this->demands[$codeId] = round($years['year_0']['amount'] * 1.10);
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
    if (isset($this->previousDemands[$codeId]['year_0']['amount'])) {
      $baseline = $this->previousDemands[$codeId]['year_0']['amount'];
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
    $economicCodes = EconomicCode::with(['children', 'parent'])
      ->orderByRaw('CASE WHEN parent_id IS NULL THEN id ELSE parent_id END, id')
      ->get();

    // Re-order to ensure children always follow parents
    $orderedCodes = [];
    $roots = $economicCodes->whereNull('parent_id')->sortBy('code');
    foreach ($roots as $root) {
      $orderedCodes[] = $root;
      $children = $economicCodes->where('parent_id', $root->id)->sortBy('code');
      foreach ($children as $child) {
        $orderedCodes[] = $child;
      }
    }
    $economicCodes = $orderedCodes;
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
      'budgetTypes' => $availableTypes,
      'totalDemand' => array_sum($this->demands)
    ])->extends('layouts.skot')->section('content');
  }
}
