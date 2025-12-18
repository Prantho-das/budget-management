<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetEstimation;
use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;

class BudgetEstimations extends Component
{
  public $fiscal_year_id;
  public $rpo_unit_id;
  public $demands = []; // [economic_code_id => amount]
  public $previousDemands = []; // [economic_code_id => amount]
  public $budget_type = 'Main Budget';
  public $status = 'draft';

  public function mount()
  {
    // For now, assume we are working with the latest active Fiscal Year
    $fiscalYear = FiscalYear::where('status', true)->latest()->first();
    $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;

    // Use the logged-in user's office. 
    $this->rpo_unit_id = Auth::user()->rpo_unit_id;

    $this->loadDemands();
  }

  public function loadDemands()
  {
    $this->demands = [];
    if (!$this->fiscal_year_id || !$this->rpo_unit_id) return;

    $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);

    // Current Year Demands
    $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
      ->where('rpo_unit_id', $this->rpo_unit_id)
      ->where('budget_type', $this->budget_type)
      ->get();

    foreach ($estimations as $estimation) {
      $this->demands[$estimation->economic_code_id] = $estimation->amount_demand;
    }

    // Previous Year Demands
    $previousFiscalYear = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
      ->orderBy('end_date', 'desc')
      ->first();

    if ($previousFiscalYear) {
      $prevEstimations = BudgetEstimation::where('fiscal_year_id', $previousFiscalYear->id)
        ->where('rpo_unit_id', $this->rpo_unit_id)
        ->get();

      foreach ($prevEstimations as $estimation) {
        $this->previousDemands[$estimation->economic_code_id] = $estimation->amount_demand;
      }
    }

    // Infer status from one of the records (assuming all have same status per batch)
    if ($estimations->isNotEmpty()) {
      $this->status = $estimations->first()->status;
    } else {
      $this->status = 'draft';
    }
  }

  public function saveDraft()
  {
    $this->save('draft');
    session()->flash('message', 'Budget Draft Saved Successfully.');
  }

  public function submit()
  {
    $this->save('submitted');
    session()->flash('message', 'Budget Submitted Successfully.');
  }

  public function save($status)
  {
    if (!$this->fiscal_year_id || !$this->rpo_unit_id) return;

    // Check if currently editable
    if ($this->status !== 'draft' && $this->status !== 'rejected') {
      session()->flash('error', 'Budget already submitted or approved and cannot be edited.');
      return;
    }

    foreach ($this->demands as $code_id => $amount) {
      if ($amount > 0 || BudgetEstimation::where([
        'fiscal_year_id' => $this->fiscal_year_id,
        'rpo_unit_id' => $this->rpo_unit_id,
        'economic_code_id' => $code_id,
      ])->exists()) {
        BudgetEstimation::updateOrCreate(
          [
            'fiscal_year_id' => $this->fiscal_year_id,
            'budget_type'    => $this->budget_type,
            'rpo_unit_id'    => $this->rpo_unit_id,
            'economic_code_id' => $code_id,
          ],
          [
            'amount_demand' => $amount ?: 0,
            'status' => $status,
          ]
        );
      }
    }
    $this->status = $status;
  }

  public function render()
  {
    $economicCodes = EconomicCode::all();
    $fiscalYear = FiscalYear::find($this->fiscal_year_id);
    $office = RpoUnit::find($this->rpo_unit_id);

    return view('livewire.budget-estimations', [
      'economicCodes' => $economicCodes,
      'currentFiscalYear' => $fiscalYear,
      'currentOffice' => $office,
    ])->extends('layouts.skot')->section('content');
  }
}
