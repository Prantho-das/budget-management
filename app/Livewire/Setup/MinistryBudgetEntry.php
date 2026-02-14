<?php

namespace App\Livewire\Setup;

use App\Models\BudgetType;
use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\MinistryAllocation;
use App\Models\MinistryBudgetMaster;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MinistryBudgetEntry extends Component
{
    public $fiscal_years;

    public $rpo_units; // Root units (Headquarters)

    public $child_units = []; // Dependent child units

    public $fiscal_year_id;

    public $head_unit_id; // Selected Headquarters

    public $rpo_unit_id; // Selected Target Unit (Child)

    public $budget_type_id;

    public $master_id;

    public $budget_data = []; // [economic_code_id => amount]

    public $original_budget_data = []; // [economic_code_id => amount] - only for comparison

    public $previous_revised_data = []; // [economic_code_id => amount] - aggregated previous revised

    public $remarks;

    public $submitted_unit_ids = []; // IDs of units that already have a budget for this FY

    public function mount($master_id = null)
    {
        $this->fiscal_years = FiscalYear::where('status', true)->get();
        // Load only Root Units (Headquarters) initially
        $this->rpo_units = RpoUnit::whereNull('parent_id')->where('status', true)->get();

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

        // Initialize budget_data with 0 for all economic codes
        $allCodes = EconomicCode::whereNotNull('parent_id')->get();
        foreach ($allCodes as $code) {
            if (! isset($this->budget_data[$code->id])) {
                $this->budget_data[$code->id] = 0;
            }
        }
    }

    public function updatedHeadUnitId($value)
    {
        $this->rpo_unit_id = null;
        $this->submitted_unit_ids = [];

        if ($value) {
            $this->child_units = RpoUnit::where('parent_id', $value)->where('status', true)->get();

            // Check for existing budgets for these children in the selected FY
            if ($this->fiscal_year_id) {
                $existingUnits = MinistryBudgetMaster::where('fiscal_year_id', $this->fiscal_year_id)
                    ->whereIn('rpo_unit_id', $this->child_units->pluck('id'))
                    ->whereHas('budgetType', function ($q) {
                        $q->where('code', 'original'); // Assuming we want to block if Original exists? Or any?
                        // User said "total data given then it will be disabled". Usually implies checking if an Original entry exists.
                        // If we are in 'Create' mode, we generally create 'Original'.
                        // Check if ANY master record exists for this unit+FY.
                    })
                    ->pluck('rpo_unit_id')
                    ->toArray();

                // If we are NOT editing an existing record, we disable these units.
                // If we WERE editing, $this->master_id would be set, but this method is triggered by USER interaction change.
                // Switching HQ means we are staring fresh context anyway for the child.

                $this->submitted_unit_ids = $existingUnits;
            }
        } else {
            $this->child_units = [];
        }

        $this->loadComparisonData();
    }

    public function loadMasterData($id)
    {
        $master = MinistryBudgetMaster::with(['allocations', 'rpoUnit'])->findOrFail($id);
        $this->master_id = $master->id;
        $this->fiscal_year_id = $master->fiscal_year_id;
        $this->budget_type_id = $master->budget_type_id;
        $this->remarks = $master->remarks;

        // Determine Head Unit and Child Unit
        $unit = $master->rpoUnit;
        if ($unit->parent_id) {
            // It's a Child Unit
            $this->head_unit_id = $unit->parent_id;
            $this->rpo_unit_id = $unit->id;
        } else {
            // It's a Root Unit (HQ)
            $this->head_unit_id = $unit->id;
            $this->rpo_unit_id = $unit->id; // Still set it so selection shows
        }

        // Initialize child units for the dropdown
        if ($this->head_unit_id) {
            $this->child_units = RpoUnit::where('parent_id', $this->head_unit_id)->where('status', true)->get();
        }

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
            'budget_data' => 'nullable|array',
            'budget_data.*' => 'nullable|numeric|min:0',
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

            if (! $this->master_id) {
                // Generate Batch No
                $prefix = $typeCode === 'original' ? 'ORG' : 'REV';
                $count = MinistryBudgetMaster::where([
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'budget_type_id' => $budgetType->id,
                ])->count();
                $batch_no = $prefix.'-'.($count + 1);

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
                if ($amount <= 0 && empty($amount)) {
                    continue;
                }

                MinistryAllocation::create([
                    'ministry_budget_master_id' => $master->id,
                    'economic_code_id' => $codeId,
                    'amount' => (float) $amount,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        session()->flash('message', __('Ministry Budget saved successfully.'));

        return $this->redirect(route('setup.ministry-budget-list'), navigate: true);
    }

    public function render()
    {
        $economic_codes = EconomicCode::whereNull('parent_id')
            ->with(['children.children'])
            ->orderBy('code', 'asc')
            ->get();

        return view('livewire.setup.ministry-budget-entry', [
            'economic_codes' => $economic_codes,
        ])
            ->extends('layouts.skot')
            ->section('content');
    }
}
