<?php

namespace App\Livewire\Setup;

use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\MinistryAllocation;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MinistryBudgetEntry extends Component
{
    public $fiscal_years;

    public $rpo_units;

    public $economic_codes;

    public $fiscal_year_id;

    public $rpo_unit_id;

    public $budget_data = []; // [economic_code_id => amount]
    public $budget_type = '';

    public function mount()
    {
        $this->fiscal_years = FiscalYear::where('status', true)->get();
        // Headquarters are root units (no parent)
        $this->rpo_units = RpoUnit::whereNull('parent_id')->where('status', true)->get();
        // Load economic codes with children for tree structure
        // Assuming 3 layers: Parent -> Child -> Sub-Child
        $this->economic_codes = EconomicCode::whereNull('parent_id')
            ->with(['children.children'])
            ->get();

        // Set default fiscal year using helper
        $currentFYName = current_fiscal_year();
        $activeFY = $this->fiscal_years->firstWhere('name', $currentFYName);

        if ($activeFY) {
            $this->fiscal_year_id = $activeFY->id;
        } elseif ($this->fiscal_years->isNotEmpty()) {
            $this->fiscal_year_id = $this->fiscal_years->first()->id;
        }
    }

    public function updatedFiscalYearId()
    {
        $this->loadExistingData();
    }

    public function updatedRpoUnitId()
    {
        $this->loadExistingData();
    }

    public function loadExistingData()
    {
        $this->budget_data = [];
        $this->budget_type = '';

        if ($this->fiscal_year_id && $this->rpo_unit_id) {
            // First check for 'revised' budget
            $allocations = MinistryAllocation::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->where('budget_type', 'revised')
                ->get();

            if ($allocations->isNotEmpty()) {
                $this->budget_type = 'revised';
            }

            // If no revised budget, check for 'original'
            if ($allocations->isEmpty()) {
                $allocations = MinistryAllocation::where('fiscal_year_id', $this->fiscal_year_id)
                    ->where('rpo_unit_id', $this->rpo_unit_id)
                    ->where('budget_type', 'original')
                    ->get();

                if ($allocations->isNotEmpty()) {
                    $this->budget_type = 'original';
                }
            }

            foreach ($allocations as $allocation) {
                // Ensure amount is formatted properly, e.g. no trailing zeros if integer
                $this->budget_data[$allocation->economic_code_id] = (float) $allocation->amount;
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
        
        // Determine Budget Type
        // Check if ANY allocation exists for this FY and Unit.
        $exists = MinistryAllocation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->rpo_unit_id)
            ->exists();

        $type = $exists ? 'revised' : 'original';

        foreach ($this->budget_data as $codeId => $amount) {
            // Only save if amount is numeric and >= 0.
            // Null or empty strings might be passed, so we check.
            if (! is_numeric($amount) && $amount !== null) {
                continue;
            }

            // Treat empty/null as 0 or delete?
            // For now, let's update or create. If 0, we keep it as 0 record.

            MinistryAllocation::updateOrCreate(
                [
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'economic_code_id' => $codeId,
                    'budget_type' => $type, // Include budget_type in the lookup
                ],
                [
                    'amount' => (float) $amount,
                    'created_by' => Auth::id(),
                ]
            );
        }

        session()->flash('message', __('Ministry Budget saved successfully as ' . ucfirst($type)));
    }

    public function render()
    {
        return view('livewire.setup.ministry-budget-entry')
            ->extends('layouts.skot')
            ->section('content');
    }
}
