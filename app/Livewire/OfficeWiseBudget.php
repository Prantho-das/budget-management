<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RpoUnit;
use App\Models\FiscalYear;
use App\Models\BudgetType;
use App\Models\EconomicCode;
use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class OfficeWiseBudget extends Component
{
    public $fiscal_year_id;
    public $budget_type_id;
    public $economic_code_id;
    public $selected_office_id;

    public function mount()
    {
        // Default to current active fiscal year using helper
        $this->fiscal_year_id = get_active_fiscal_year_id();

        // Default to a budget type (e.g. Revenue)
        $firstType = BudgetType::first();
        $this->budget_type_id = $firstType ? $firstType->id : null;
    }

    public function updateAmount($officeId, $amount, $field = 'demand')
    {
        if (!$this->economic_code_id) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('Please select an Economic Code to edit amounts.')]);
            return;
        }

        $amount = (float) $amount;
        
        $dbField = match($field) {
            'demand' => 'amount_demand',
            'revised' => 'revised_amount',
            'projection_1' => 'projection_1',
            'projection_2' => 'projection_2',
            default => 'amount_demand'
        };

        BudgetEstimation::updateOrCreate(
            [
                'target_office_id' => $officeId,
                'fiscal_year_id' => $this->fiscal_year_id,
                'budget_type_id' => $this->budget_type_id,
                'economic_code_id' => $this->economic_code_id
            ],
            [
                $dbField => $amount
            ]
        );

        $this->dispatch('alert', ['type' => 'success', 'message' => __('Amount updated.')]);
    }

    public function approve($officeId)
    {
        $workflow = new \App\Services\BudgetWorkflowService();
        
        $query = BudgetEstimation::where('target_office_id', $officeId)
            ->where('fiscal_year_id', $this->fiscal_year_id);
            
        if ($this->budget_type_id) {
            $query->where('budget_type_id', $this->budget_type_id);
        }

        // Get estimations that are NOT yet fully approved (or in a state needing approval)
        // Adjust logic based on exact flow. Assuming we approve pending items.
        // Or if this is 'Release' approval, maybe different.
        // For now, using standard approve which moves workflow forward.
        $estimations = $query->get();

        if ($estimations->isEmpty()) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => __('No budget found to approve.')]);
            return;
        }

        $workflow->approveBatch($estimations);
        
        $this->dispatch('alert', ['type' => 'success', 'message' => __('Budget Approved Successfully.')]);
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('release-budget'), 403);

        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
        
        // Hierarchical Economic Codes (matching BudgetEstimations.php)
        $allCodes = EconomicCode::with(['children', 'parent'])->get();
        $orderedCodes = [];
        $roots = $allCodes->whereNull('parent_id')->sortBy('code');
        foreach ($roots as $root) {
            $orderedCodes[] = $root;
            $children = $allCodes->where('parent_id', $root->id)->sortBy('code');
            foreach ($children as $child) {
                $orderedCodes[] = $child;
            }
        }
        $economicCodes = $orderedCodes;

        // Get Offices
        $officeQuery = RpoUnit::orderBy('code');
        if ($this->selected_office_id) {
            $officeQuery->where('id', $this->selected_office_id);
            $selectedOffice = RpoUnit::find($this->selected_office_id);
        } else {
            $selectedOffice = null;
        }
        $offices = $officeQuery->get();
        $allOffices = RpoUnit::orderBy('code')->get();

        // --- Logic Synchronization (Robust FY Detection) ---
        $selectedFy = FiscalYear::find($this->fiscal_year_id);
        
        // Full Previous Years (2 full years)
        $fullPrevYears = FiscalYear::where('end_date', '<', $selectedFy->start_date)
            ->orderBy('end_date', 'desc')
            ->take(2)
            ->get()
            ->reverse()
            ->values(); // [0 => FY-2, 1 => FY-1]

        // Partial Year Mapping (First 6 months: July-Dec)
        // Design: 
        // Col 3: FY-2 Full
        // Col 4: FY-1 Full
        // Col 5: FY-1 (First 6 Mo)
        // Col 6: Selected FY (First 6 Mo)
        
        $officeWiseData = [];

        foreach ($offices as $office) {
            $data = [
                'history_full_1' => 0, // FY-2
                'history_full_2' => 0, // FY-1
                'history_part_1' => 0, // FY-1 (6mo)
                'history_part_2' => 0, // FY (6mo)
                'demand' => 0,
                'approved' => 0,
                'revised' => 0,
                'projection_1' => 0,
                'projection_2' => 0,
                'released' => 0,
            ];

            // 1. Full Historical (FY-2, FY-1)
            foreach ($fullPrevYears as $idx => $fy) {
                $q = Expense::where('rpo_unit_id', $office->id)->where('fiscal_year_id', $fy->id);
                if ($this->budget_type_id) $q->where('budget_type_id', $this->budget_type_id);
                if ($this->economic_code_id) $q->where('economic_code_id', $this->economic_code_id);
                
                $field = $idx === 0 ? 'history_full_1' : 'history_full_2';
                $data[$field] = $q->sum('amount');
            }

            // 2. Partial Historical (6 Months)
            // FY-1 (First 6 mo)
            if ($fullPrevYears->count() >= 2) {
                $fy1 = $fullPrevYears[1]; // Latest full past year
                $q = Expense::where('rpo_unit_id', $office->id)->where('fiscal_year_id', $fy1->id)
                    ->whereMonth('date', '>=', 7); // July-Dec
                if ($this->budget_type_id) $q->where('budget_type_id', $this->budget_type_id);
                if ($this->economic_code_id) $q->where('economic_code_id', $this->economic_code_id);
                $data['history_part_1'] = $q->sum('amount');
            }

            // Selected FY (First 6 mo)
            $q = Expense::where('rpo_unit_id', $office->id)->where('fiscal_year_id', $this->fiscal_year_id)
                ->whereMonth('date', '>=', 7);
            if ($this->budget_type_id) $q->where('budget_type_id', $this->budget_type_id);
            if ($this->economic_code_id) $q->where('economic_code_id', $this->economic_code_id);
            $data['history_part_2'] = $q->sum('amount');

            // 3. Current Estimates
            $estQuery = BudgetEstimation::where('target_office_id', $office->id)->where('fiscal_year_id', $this->fiscal_year_id);
            if ($this->budget_type_id) $estQuery->where('budget_type_id', $this->budget_type_id);
            if ($this->economic_code_id) $estQuery->where('economic_code_id', $this->economic_code_id);
            
            $estimations = $estQuery->get();
            $data['demand'] = $estimations->sum('amount_demand');
            $data['approved'] = $estimations->sum('amount_approved');
            $data['revised'] = $estimations->sum('revised_amount');
            $data['projection_1'] = $estimations->sum('projection_1');
            $data['projection_2'] = $estimations->sum('projection_2');

            // 4. Released
            $allocQuery = BudgetAllocation::where('rpo_unit_id', $office->id)->where('fiscal_year_id', $this->fiscal_year_id);
            if ($this->budget_type_id) $allocQuery->where('budget_type_id', $this->budget_type_id);
            if ($this->economic_code_id) $allocQuery->where('economic_code_id', $this->economic_code_id);
            $data['released'] = $allocQuery->sum('amount');

            $officeWiseData[$office->id] = $data;
        }

        return view('livewire.office-wise-budget', [
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'economicCodes' => $economicCodes,
            'offices' => $offices,
            'allOffices' => $allOffices,
            'selectedOffice' => $selectedOffice,
            'selectedFy' => $selectedFy,
            'fullPrevYears' => $fullPrevYears,
            'officeWiseData' => $officeWiseData
        ])->extends('layouts.skot')->section('content');
    }
}
