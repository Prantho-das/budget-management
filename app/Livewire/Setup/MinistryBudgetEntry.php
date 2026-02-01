<?php

namespace App\Livewire\Setup;

use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\MinistryAllocation;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use App\Models\MinistryBudgetMaster;
use App\Models\BudgetType;
use Illuminate\Support\Facades\DB;

class MinistryBudgetEntry extends Component
{
    public $fiscal_years;
    public $rpo_units;
    public $economic_codes;

    public $fiscal_year_id;
    public $rpo_unit_id;
    public $budget_type_id;
    public $master_id;

    public $budget_data = []; // [economic_code_id => amount]
    public $original_budget_data = []; // [economic_code_id => amount] - only for comparison
    public $previous_revised_data = []; // [economic_code_id => amount] - aggregated previous revised
    public $remarks;

    public function mount($master_id = null)
    {
        $this->fiscal_years = FiscalYear::where('status', true)->get();
        $this->rpo_units = RpoUnit::whereNull('parent_id')->where('status', true)->get();
        $this->economic_codes = EconomicCode::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->orderBy('code', 'asc')->with(['children' => function ($q) {
                    $q->orderBy('code', 'asc');
                }]);
            }])
            ->orderBy('code', 'asc')
            ->get();

        if ($master_id) {
            $this->loadMasterData($master_id);
        } else {
            // Set default fiscal year using helper
            $currentFYName = current_fiscal_year();
            $activeFY = $this->fiscal_years->firstWhere('name', $currentFYName);
            if ($activeFY) {
                $this->fiscal_year_id = $activeFY->id;
            }
        }
    }

    public function loadMasterData($id)
    {
        $master = MinistryBudgetMaster::with('allocations')->findOrFail($id);
        $this->master_id = $master->id;
        $this->fiscal_year_id = $master->fiscal_year_id;
        $this->rpo_unit_id = $master->rpo_unit_id;
        $this->budget_type_id = $master->budget_type_id;
        $this->remarks = $master->remarks;

        foreach ($master->allocations as $allocation) {
            $this->budget_data[$allocation->economic_code_id] = (float) $allocation->amount;
        }

        $this->loadComparisonData();
    }

    public function updatedFiscalYearId()
    {
        $this->loadComparisonData();
    }

    public function updatedRpoUnitId()
    {
        $this->loadComparisonData();
    }

    public function loadComparisonData()
    {
        $this->original_budget_data = [];
        $this->previous_revised_data = [];

        if ($this->fiscal_year_id && $this->rpo_unit_id) {
            // Load Original
            $originalMaster = MinistryBudgetMaster::where([
                'fiscal_year_id' => $this->fiscal_year_id,
                'rpo_unit_id' => $this->rpo_unit_id,
            ])->whereHas('budgetType', function ($q) {
                $q->where('code', 'original');
            })->with('allocations')->first();

            if ($originalMaster) {
                foreach ($originalMaster->allocations as $allocation) {
                    $this->original_budget_data[$allocation->economic_code_id] = (float) $allocation->amount;
                }
            }

            // Load Previous Revised (all revised batches EXCEPT current one if editing)
            $query = MinistryBudgetMaster::where([
                'fiscal_year_id' => $this->fiscal_year_id,
                'rpo_unit_id' => $this->rpo_unit_id,
            ])->whereHas('budgetType', function ($q) {
                $q->where('code', 'revised');
            });

            if ($this->master_id) {
                $query->where('id', '<', $this->master_id); // Only batches BEFORE this one if editing, or all if new? 
                // user said "previous revised budget". 
            }

            $revisedMasters = $query->with('allocations')->get();

            foreach ($revisedMasters as $rm) {
                foreach ($rm->allocations as $allocation) {
                    $this->previous_revised_data[$allocation->economic_code_id] =
                        ($this->previous_revised_data[$allocation->economic_code_id] ?? 0) + (float) $allocation->amount;
                }
            }
        }
    }

    public function save()
    {
        $this->validate([
            'fiscal_year_id' => 'required',
            'rpo_unit_id' => 'required',
            'budget_data' => 'array',
        ]);

        DB::transaction(function () {
            // Determine Budget Type
            $originalExists = MinistryBudgetMaster::where([
                'fiscal_year_id' => $this->fiscal_year_id,
                'rpo_unit_id' => $this->rpo_unit_id,
            ])->whereHas('budgetType', function ($q) {
                $q->where('code', 'original');
            })->exists();

            $typeCode = $originalExists ? 'revised' : 'original';
            $budgetType = BudgetType::where('code', $typeCode)->first();

            if (!$this->master_id) {
                // Generate Batch No
                $prefix = $typeCode === 'original' ? 'ORG' : 'REV';
                $count = MinistryBudgetMaster::where([
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'budget_type_id' => $budgetType->id
                ])->count();
                $batch_no = $prefix . '-' . ($count + 1);

                $master = MinistryBudgetMaster::create([
                    'batch_no' => $batch_no,
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'budget_type_id' => $budgetType->id,
                    'total_amount' => array_sum($this->budget_data),
                    'remarks' => $this->remarks,
                    'created_by' => Auth::id(),
                ]);
            } else {
                $master = MinistryBudgetMaster::find($this->master_id);
                $master->update([
                    'total_amount' => array_sum($this->budget_data),
                    'remarks' => $this->remarks,
                ]);
                // Delete existing allocations to re-save (full submission)
                $master->allocations()->delete();
            }

            foreach ($this->budget_data as $codeId => $amount) {
                if ($amount <= 0 && empty($amount)) continue;

                MinistryAllocation::create([
                    'ministry_budget_master_id' => $master->id,
                    'economic_code_id' => $codeId,
                    'amount' => (float) $amount,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        session()->flash('message', __('Ministry Budget saved successfully.'));
        return redirect()->route('setup.ministry-budget-list');
    }

    public function render()
    {
        return view('livewire.setup.ministry-budget-entry')
            ->extends('layouts.skot')
            ->section('content');
    }
}
