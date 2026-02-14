<?php

namespace App\Livewire\BudgetDistribution;

use Livewire\Component;

class BudgetDistributionEntry extends Component
{
  public $fiscal_year_id;
  public $parent_office_id;
  public $budget_type_id;

  public $fiscalYears;
  public $parentOffices;
  public $childOffices = [];
  public $economicCodes = [];
  public $budgetTypes = [];

  public $distributions = []; // [office_id][code_id] => amount
  public $demands = []; // [office_id][code_id] => amount
  public $history = []; // [office_id][code_id][fy_id] => amount
  public $ministryAllocations = []; // [code_id] => amount
  public $prevFiscalYears = [];

  public function mount($office_id = null)
  {
    $this->fiscalYears = \App\Models\FiscalYear::orderBy('name', 'desc')->get();
    $this->parentOffices = \App\Models\RpoUnit::whereNull('parent_id')->orderBy('name')->get();
    $this->fiscal_year_id = get_active_fiscal_year_id();
    
    // Fetch only 3rd layer Economic Codes (those that have a grandparent)
    $this->economicCodes = \App\Models\EconomicCode::whereHas('parent.parent')
        ->orderBy('code')
        ->get();

    $this->budgetTypes = \App\Models\BudgetType::where('status', true)->get();

    if ($this->budgetTypes->isNotEmpty()) {
      $this->budget_type_id = $this->budgetTypes->first()->id;
    }

    $this->loadHistoricalYears();

    // Catch office_id from query string if not passed directly
    $officeId = $office_id ?? request()->query('office_id');
    if ($officeId) {
      $this->parent_office_id = $officeId;
      $this->loadData();
    }
  }

  public function updatedFiscalYearId()
  {
    $this->loadHistoricalYears();
    $this->loadData();
  }

  public function updatedParentOfficeId()
  {
    $this->loadData();
  }

  public function updatedBudgetTypeId()
  {
    $this->loadData();
  }

  private function loadHistoricalYears()
  {
    $currentFy = \App\Models\FiscalYear::find($this->fiscal_year_id);
    if ($currentFy) {
      $this->prevFiscalYears = \App\Models\FiscalYear::where('end_date', '<', $currentFy->start_date)
        ->orderBy('end_date', 'desc')
        ->take(3)
        ->get()
        ->reverse()
        ->values();
    }
  }

  public function loadData()
  {
    if (!$this->parent_office_id || !$this->fiscal_year_id) {
      $this->childOffices = [];
      return;
    }

    $this->childOffices = \App\Models\RpoUnit::where('parent_id', $this->parent_office_id)
      ->orderBy('code')
      ->get();

    $officeIds = $this->childOffices->pluck('id')->toArray();
    $codeIds = collect($this->economicCodes)->pluck('id')->toArray();
    $fyIds = collect($this->prevFiscalYears)->pluck('id')->toArray();

    // Load History (Expenses from previous years)
    $historyData = \App\Models\Expense::whereIn('rpo_unit_id', $officeIds)
      ->whereIn('economic_code_id', $codeIds)
      ->whereIn('fiscal_year_id', $fyIds)
      ->get();

    $this->history = [];
    foreach ($historyData as $item) {
      // Aggregate amounts if there are multiple expense entries for same code/office/year
      if (!isset($this->history[$item->rpo_unit_id][$item->economic_code_id][$item->fiscal_year_id])) {
          $this->history[$item->rpo_unit_id][$item->economic_code_id][$item->fiscal_year_id] = 0;
      }
      $this->history[$item->rpo_unit_id][$item->economic_code_id][$item->fiscal_year_id] += $item->amount;
    }

    // Load Ministry Allocations (The limit available to distribute)
    $allocData = \App\Models\MinistryAllocation::whereHas('master', function($query) {
        $query->where('rpo_unit_id', $this->parent_office_id)
              ->where('fiscal_year_id', $this->fiscal_year_id)
              ->where('budget_type_id', $this->budget_type_id);
    })->get();

    $this->ministryAllocations = $allocData->pluck('amount', 'economic_code_id')->toArray();

    // Load Current Distributions if exist
    $currentData = \App\Models\BudgetAllocation::whereIn('rpo_unit_id', $officeIds)
      ->whereIn('economic_code_id', $codeIds)
      ->where('fiscal_year_id', $this->fiscal_year_id)
      ->where('budget_type_id', $this->budget_type_id)
      ->get();

    // Load Child Office Demands (Budget Estimation) for pre-filling
    $childDemands = \App\Models\BudgetEstimation::whereIn('rpo_unit_id', $officeIds)
      ->whereIn('economic_code_id', $codeIds)
      ->where('fiscal_year_id', $this->fiscal_year_id)
      ->where('budget_type_id', $this->budget_type_id)
      ->get();

    $this->demands = [];
    foreach ($childDemands as $demand) {
        $this->demands[$demand->rpo_unit_id][$demand->economic_code_id] = $demand->amount_demand;
    }
      
    $this->distributions = [];
    foreach ($currentData as $item) {
      $this->distributions[$item->rpo_unit_id][$item->economic_code_id] = $item->amount;
    }
    
    // Auto-fill Logic: Use demand if exists and distribution is not yet set or is 0
    foreach ($this->childOffices as $child) {
        foreach ($this->economicCodes as $code) {
             $currentVal = $this->distributions[$child->id][$code->id] ?? 0;
             if ($currentVal == 0) {
                 $this->distributions[$child->id][$code->id] = $this->demands[$child->id][$code->id] ?? 0;
             }
        }
    }
  }

  public function updatedDistributions()
  {
    // This triggers re-render for totals and validation in blade
  }



  public function save()
  {
    $this->validate([
      'fiscal_year_id' => 'required',
      'parent_office_id' => 'required',
      'budget_type_id' => 'required',
    ]);

    $errorItems = [];
    foreach ($this->economicCodes as $code) {
      $totalDistributed = collect($this->distributions)->map(fn($o) => $o[$code->id] ?? 0)->sum();
      $maxAllowed = $this->ministryAllocations[$code->id] ?? 0;

      if ($maxAllowed > 0 && $totalDistributed > $maxAllowed) {
        $errorItems[] = $code->name . " (" . $code->code . ")";
      }
    }

    if (!empty($errorItems)) {
        $errorMessage = "<strong>" . __('Ministry Allocation exceeded for the following items:') . "</strong>";
        $errorMessage .= "<ul class='mt-2 mb-0 ps-3 text-start'>";
        $errorMessage .= "<li>" . implode("</li><li>", $errorItems) . "</li>";
        $errorMessage .= "</ul>";
        
        session()->flash('error', $errorMessage);
        return;
    }

    try {
      foreach ($this->distributions as $officeId => $codes) {
        foreach ($codes as $codeId => $amount) {
          \App\Models\BudgetAllocation::updateOrCreate(
            [
              'fiscal_year_id' => $this->fiscal_year_id,
              'rpo_unit_id' => $officeId,
              'economic_code_id' => $codeId,
              'budget_type_id' => $this->budget_type_id,
            ],
            [
              'amount' => $amount ?: 0,
            ]
          );
        }
      }
      session()->flash('message', __('Budget Distribution saved successfully.'));
    } catch (\Exception $e) {
      session()->flash('error', __('Error saving data: ') . $e->getMessage());
    }

    $this->loadData();
  }

  public function render()
  {
    return view('livewire.budget-distribution.budget-distribution-entry')
      ->extends('layouts.skot')
      ->section('content');
  }
}
