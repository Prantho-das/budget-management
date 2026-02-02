<?php

namespace App\Livewire\BudgetDistribution;

use Livewire\Component;
use App\Models\RpoUnit;
use App\Models\BudgetEstimation;
use App\Models\FiscalYear;

class BudgetDistributionList extends Component
{
  public $fiscal_year_id;

  public function mount()
  {
    $this->fiscal_year_id = get_active_fiscal_year_id();
  }

  public function render()
  {
    $parentOffices = RpoUnit::whereNull('parent_id')
      ->orderBy('code')
      ->get();

    $data = $parentOffices->map(function ($office) {
      $allChildIds = $this->getAllChildIds($office);
      $totalDemand = BudgetEstimation::whereIn('rpo_unit_id', $allChildIds)
        ->where('fiscal_year_id', $this->fiscal_year_id)
        ->sum('amount_demand');

      return [
        'id' => $office->id,
        'code' => $office->code,
        'name' => $office->name,
        'sub_office_count' => count($allChildIds) - 1, // Exclude self
        'total_demand' => $totalDemand,
      ];
    });

    return view('livewire.budget-distribution.budget-distribution-list', [
      'offices' => $data,
      'fiscalYears' => FiscalYear::orderBy('name', 'desc')->get(),
      'selectedFy' => FiscalYear::find($this->fiscal_year_id),
    ])->extends('layouts.skot')->section('content');
  }

  private function getAllChildIds($office)
  {
    $ids = [$office->id];
    foreach ($office->children as $child) {
      $ids = array_merge($ids, $this->getAllChildIds($child));
    }
    return $ids;
  }
}
