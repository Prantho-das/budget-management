<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FiscalYear;
use App\Models\EconomicCode;
use App\Models\BudgetEstimation;
use App\Models\RpoUnit;
use App\Models\BudgetType;
use Illuminate\Support\Facades\DB;

class BudgetRelease extends Component
{
    public $fiscal_year_id;
    public $budget_type_id;
    public $rpo_unit_id; // Filter by office if needed
    public $release_amounts = [];
    public $status = 'pending';

    public $ministryBudgetSummary = [];
    public $validationErrors = [];

    public function mount()
    {
        $activeFy = FiscalYear::where('status', true)->first() ?? FiscalYear::orderBy('end_date', 'desc')->first();
        if ($activeFy) {
            $this->fiscal_year_id = $activeFy->id;
        }
        $bt = BudgetType::first();
        $this->budget_type_id = $bt ? $bt->id : null;
        
        $this->loadMinistryBudgetSummary();
    }

    public function loadMinistryBudgetSummary()
    {
        if (!$this->fiscal_year_id || !$this->budget_type_id) {
            $this->ministryBudgetSummary = [];
            return;
        }

        $validator = new \App\Services\MinistryBudgetValidationService();
        // Passing null for rpoUnitId gets global summary for HQ release
        $this->ministryBudgetSummary = $validator->getMinistryBudgetSummary(
            $this->fiscal_year_id,
            null, 
            $this->budget_type_id
        );
    }
    
    public function updatedFiscalYearId()
    {
        $this->loadMinistryBudgetSummary();
    }

    public function updatedBudgetTypeId()
    {
        $this->loadMinistryBudgetSummary();
    }

    public function render()
    {
        $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);
        
        $prevYears = collect();
        if ($currentFiscalYear) {
            // Fetch previous 3 years for historical columns
            $prevYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
                ->orderBy('end_date', 'desc')
                ->take(3)
                ->get()
                ->reverse()
                ->values();
        }

        $economicCodes = EconomicCode::orderBy('code')->get();
        
        // Aggregate data for HQ
        // For each economic code, get:
        // 1. Historical expenses (aggregate across all units)
        // 2. Current year DEMANDS (aggregate across all units)
        // 3. Current year APPROVED (aggregate across all units)
        
        $historicalData = [];
        foreach ($prevYears as $index => $py) {
            $data = DB::table('expenses')
                ->where('fiscal_year_id', $py->id)
                ->where('budget_type_id', $this->budget_type_id)
                ->select('economic_code_id', DB::raw('SUM(amount) as total'))
                ->groupBy('economic_code_id')
                ->pluck('total', 'economic_code_id');
            $historicalData["year_{$index}"] = $data;
        }

        $currentDemands = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('budget_type_id', $this->budget_type_id)
            ->select('economic_code_id', 
                DB::raw('SUM(amount_demand) as demand'),
                DB::raw('SUM(amount_approved) as approved')
            )
            ->groupBy('economic_code_id')
            ->get()
            ->keyBy('economic_code_id');

        $currentReleased = \App\Models\BudgetAllocation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('budget_type_id', $this->budget_type_id)
            ->select('economic_code_id', DB::raw('SUM(amount) as released'))
            ->groupBy('economic_code_id')
            ->pluck('released', 'economic_code_id');

        // Logic to aggregate child data into parents for display
        foreach ($economicCodes as $code) {
            if ($code->parent_id == null) {
                $childIds = EconomicCode::where('parent_id', $code->id)->pluck('id')->toArray();
                if (!empty($childIds)) {
                    // Aggregate Historical
                    foreach ($prevYears as $index => $py) {
                        $childTotal = 0;
                        foreach($childIds as $cid) {
                            $childTotal += ($historicalData["year_{$index}"][$cid] ?? 0);
                        }
                        if ($childTotal > 0) {
                            $historicalData["year_{$index}"][$code->id] = ($historicalData["year_{$index}"][$code->id] ?? 0) + $childTotal;
                        }
                    }
                    
                    // Aggregate Demand/Approved
                    $childDemand = 0;
                    $childApproved = 0;
                    foreach($childIds as $cid) {
                        $childDemand += ($currentDemands[$cid]->demand ?? 0);
                        $childApproved += ($currentDemands[$cid]->approved ?? 0);
                    }
                    
                    if ($childDemand > 0 || $childApproved > 0) {
                        if (!isset($currentDemands[$code->id])) {
                            $currentDemands[$code->id] = (object)['demand' => 0, 'approved' => 0];
                        }
                        $currentDemands[$code->id]->demand += $childDemand;
                        $currentDemands[$code->id]->approved += $childApproved;
                    }

                    // Aggregate Released
                    $childReleased = 0;
                    foreach($childIds as $cid) {
                        $childReleased += ($currentReleased[$cid] ?? 0);
                    }
                    if ($childReleased > 0) {
                        $currentReleased[$code->id] = ($currentReleased[$code->id] ?? 0) + $childReleased;
                    }
                }
            }
        }

        return view('livewire.budget-release', [
            'economicCodes' => $economicCodes,
            'prevYears' => $prevYears,
            'historicalData' => $historicalData,
            'currentDemands' => $currentDemands,
            'currentReleased' => $currentReleased,
            'budgetTypes' => BudgetType::all(),
            'fiscalYears' => FiscalYear::orderBy('name', 'desc')->get(),
            'ministryBudgetSummary' => $this->ministryBudgetSummary,
        ])->extends('layouts.skot')->section('content');
    }

    public function releaseToOffice($officeId, $economicCodeId, $amount)
    {
        $validator = new \App\Services\MinistryBudgetValidationService();
        
        $validation = $validator->validateRelease(
            $this->fiscal_year_id,
            $officeId,
            $this->budget_type_id,
            $economicCodeId,
            (float) $amount
        );
        
        if (!$validation['valid']) {
            $this->validationErrors[$officeId . '_' . $economicCodeId] = $validation['message'];
            session()->flash('error', $validation['message']);
            return;
        }
        
        // Logic to actually release would go here
        // For now just success message as the method name suggests intention
         session()->flash('success', 'Amount validated successfully. Ready for release.');
    }
}
