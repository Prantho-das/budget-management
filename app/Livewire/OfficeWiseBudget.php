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

        $fiscalYears = FiscalYear::orderBy('id', 'desc')->get();
        $budgetTypes = BudgetType::all();
        $economicCodes = EconomicCode::orderBy('code')->get();

        // Get Offices
        $officeQuery = RpoUnit::orderBy('code');
        if ($this->selected_office_id) {
            $officeQuery->where('id', $this->selected_office_id);
            $selectedOffice = RpoUnit::find($this->selected_office_id);
        } else {
            $selectedOffice = null;
        }
        $offices = $officeQuery->get();
        
        // For filter dropdown, get all
        $allOffices = RpoUnit::orderBy('code')->get();

        // Determine Previous Years based on selected
        $selectedFy = FiscalYear::find($this->fiscal_year_id);
        $prevYears = [];
        if ($selectedFy) {
             $parts = explode('-', $selectedFy->name);
             if (count($parts) == 2) {
                 $startYear = (int)$parts[0];
                 for ($i = 2; $i >= 1; $i--) {
                     $pStart = $startYear - $i;
                     $pEnd = $pStart + 1;
                     $pName = $pStart . '-' . substr($pEnd, -2);
                     $prevYears[] = $pName;
                 }
             }
        }
        
        // Structure: [office_id => ['historical' => [year => val], 'demand' => val, 'approved' => val, 'released' => val]]
        $officeWiseData = [];

        foreach ($offices as $office) {
            $data = [
                'historical' => [],
                'demand' => 0,
                'approved' => 0,
                'released' => 0,
                'revised' => 0,
                'projection_1' => 0,
                'projection_2' => 0
            ];

            // 1. Historical Expenses
            foreach ($prevYears as $idx => $pName) {
                // Find FY ID by name
                $pFY = $fiscalYears->where('name', $pName)->first();
                if ($pFY) {
                    $query = Expense::where('rpo_unit_id', $office->id)
                                    ->where('fiscal_year_id', $pFY->id);
                    
                    if ($this->budget_type_id) {
                        $query->where('budget_type_id', $this->budget_type_id);
                    }
                    if ($this->economic_code_id) {
                        $query->where('economic_code_id', $this->economic_code_id);
                    }
                    
                    $data['historical']["year_{$idx}"] = $query->sum('amount');
                } else {
                    $data['historical']["year_{$idx}"] = 0;
                }
            }

            // 2. Current Estimates (Demand) & Approved
            if ($this->fiscal_year_id) {
                $estQuery = BudgetEstimation::where('target_office_id', $office->id)
                    ->where('fiscal_year_id', $this->fiscal_year_id);
                
                 if ($this->budget_type_id) {
                    $estQuery->where('budget_type_id', $this->budget_type_id);
                }
                if ($this->economic_code_id) {
                    $estQuery->where('economic_code_id', $this->economic_code_id);
                }
                
                $estimations = $estQuery->get();
                $data['demand'] = $estimations->sum('amount_demand');
                $data['approved'] = $estimations->sum('amount_approved');
                $data['revised'] = $estimations->sum('revised_amount');
                $data['projection_1'] = $estimations->sum('projection_1');
                $data['projection_2'] = $estimations->sum('projection_2');
            }

            // 3. Released (Allocation)
            if ($this->fiscal_year_id) {
               $allocQuery = BudgetAllocation::where('rpo_unit_id', $office->id)
                   ->where('fiscal_year_id', $this->fiscal_year_id);
                
                if ($this->budget_type_id) {
                    $allocQuery->where('budget_type_id', $this->budget_type_id);
                }
                if ($this->economic_code_id) {
                    $allocQuery->where('economic_code_id', $this->economic_code_id);
                }
                
                $data['released'] = $allocQuery->sum('amount');
            }

            $officeWiseData[$office->id] = $data;
        }

        return view('livewire.office-wise-budget', [
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'economicCodes' => $economicCodes,
            'offices' => $offices,
            'allOffices' => $allOffices,
            'selectedOffice' => $selectedOffice,
            'prevYears' => $prevYears,
            'officeWiseData' => $officeWiseData
        ])->extends('layouts.skot')->section('content');
    }
}
