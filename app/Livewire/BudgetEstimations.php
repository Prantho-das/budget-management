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
  public $status = 'draft';

  public function mount()
  {
    // For now, assume we are working with the latest active Fiscal Year
    $fiscalYear = FiscalYear::where('status', true)->latest()->first();
    $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;

    // In a real app, this would be the logged-in user's office. 
    // For demo, we might need to select it or assume a default.
    // Let's assume the first RPO unit for now if not linked to user.
    $this->rpo_unit_id = RpoUnit::first()->id ?? null;

    $this->loadDemands();
  }

  public function loadDemands()
  {
    if (!$this->fiscal_year_id || !$this->rpo_unit_id) return;

    $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
      ->where('rpo_unit_id', $this->rpo_unit_id)
      ->get();

    foreach ($estimations as $estimation) {
      $this->demands[$estimation->economic_code_id] = $estimation->amount_demand;
    }

    // Infer status from one of the records (assuming all have same status per batch)
    if ($estimations->isNotEmpty()) {
      $this->status = $estimations->first()->status;
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

    foreach ($this->demands as $code_id => $amount) {
      // Only save if amount > 0 or record exists
      if ($amount > 0) {
        BudgetEstimation::updateOrCreate(
          [
            'fiscal_year_id' => $this->fiscal_year_id,
            'rpo_unit_id' => $this->rpo_unit_id,
            'economic_code_id' => $code_id,
          ],
          [
            'amount_demand' => $amount,
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
