<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetEstimation;
use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetType;
use App\Services\BudgetWorkflowService;

class BudgetEstimations extends Component
{
  public $fiscal_year_id;
  public $rpo_unit_id;
  public $demands = []; // [economic_code_id => amount]
  public $previousDemands = []; // [economic_code_id => amount]
  public $budget_type_id;
  public $status = 'draft';
  public $current_stage = 'Draft';

  public function mount()
  {
    abort_if(auth()->user()->cannot('view-budget-estimations'), 403);
    $fiscalYear = FiscalYear::where('status', true)->latest()->first();
    $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;

    $this->rpo_unit_id = Auth::user()->rpo_unit_id;

    $this->budget_type_id = BudgetType::where('status', true)->orderBy('order_priority')->first()?->id;

    $this->loadDemands();
  }

  public function updatedBudgetTypeId()
  {
      $this->loadDemands();
  }

  public function loadDemands()
  {
    $this->demands = [];
    if (!$this->fiscal_year_id || !$this->rpo_unit_id || !$this->budget_type_id) return;

    $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);

    // Current Year Demands
    $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
      ->where('rpo_unit_id', $this->rpo_unit_id)
      ->where('budget_type_id', $this->budget_type_id)
      ->get();

    foreach ($estimations as $estimation) {
      $this->demands[$estimation->economic_code_id] = $estimation->amount_demand;
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

    if ($estimations->isNotEmpty()) {
      $this->status = $estimations->first()->status;
      $this->current_stage = $estimations->first()->current_stage;
    } else {
      $this->status = 'draft';
      $this->current_stage = 'Draft';
    }
  }

  public function saveDraft()
  {
    abort_if(auth()->user()->cannot('create-budget-estimations'), 403);
    $this->persist('draft');
    session()->flash('message', __('Budget Draft Saved Successfully.'));
  }

  public function submitForApproval()
  {
    abort_if(auth()->user()->cannot('submit-budget-estimations'), 403);
    $this->persist('submitted');
    
    $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
        ->where('rpo_unit_id', $this->rpo_unit_id)
        ->where('budget_type_id', $this->budget_type_id)
        ->get();
    
    $workflow = new BudgetWorkflowService();
    foreach($estimations as $estimation) {
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
      if ($amount > 0 || BudgetEstimation::where([
        'fiscal_year_id' => $this->fiscal_year_id,
        'rpo_unit_id' => $this->rpo_unit_id,
        'economic_code_id' => $code_id,
        'budget_type_id' => $this->budget_type_id,
      ])->exists()) {
        BudgetEstimation::updateOrCreate(
          [
            'fiscal_year_id' => $this->fiscal_year_id,
            'budget_type_id' => $this->budget_type_id,
            'rpo_unit_id'    => $this->rpo_unit_id,
            'economic_code_id' => $code_id,
          ],
          [
            'amount_demand' => $amount ?: 0,
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
    $economicCodes = EconomicCode::all();
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
      'budgetTypes' => $availableTypes
    ])->extends('layouts.skot')->section('content');
  }
}
