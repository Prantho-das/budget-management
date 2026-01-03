<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FiscalYear;
use App\Models\EconomicCode;
use App\Models\BudgetEstimation;
use App\Models\RpoUnit;
use App\Models\BudgetType;
use Illuminate\Support\Facades\DB;

class OfficeWiseBudget extends Component
{
    public $fiscal_year_id;
    public $budget_type_id;
    public $economic_code_id;

    public function mount()
    {
        $activeFy = FiscalYear::where('status', true)->first() ?? FiscalYear::orderBy('end_date', 'desc')->first();
        if ($activeFy) {
            $this->fiscal_year_id = $activeFy->id;
        }
        $bt = BudgetType::first();
        $this->budget_type_id = $bt ? $bt->id : null;
        $ec = EconomicCode::whereNotNull('parent_id')->first();
        $this->economic_code_id = $ec ? $ec->id : null;
    }

    public function render()
    {
        $currentFiscalYear = FiscalYear::find($this->fiscal_year_id);
        $prevYears = collect();
        if ($currentFiscalYear) {
            $prevYears = FiscalYear::where('end_date', '<', $currentFiscalYear->start_date)
                ->orderBy('end_date', 'desc')
                ->take(3)
                ->get()
                ->reverse()
                ->values();
        }

        $offices = RpoUnit::orderBy('code')->get();
        
        // Data per office for the selected budget type and economic code
        $data = [];
        foreach ($offices as $office) {
            $officeData = [
                'historical' => [],
                'demand' => 0,
                'approved' => 0,
                'released' => 0,
            ];

            // Historical Expenses
            foreach ($prevYears as $index => $py) {
                $query = DB::table('expenses')
                    ->where('rpo_unit_id', $office->id)
                    ->where('fiscal_year_id', $py->id)
                    ->where('budget_type_id', $this->budget_type_id);
                
                if ($this->economic_code_id) {
                    $query->where('economic_code_id', $this->economic_code_id);
                }
                
                $officeData['historical']["year_{$index}"] = $query->sum('amount');
            }

            // Current Demands (including approvals)
            $demandQuery = BudgetEstimation::where('rpo_unit_id', $office->id)
                ->where('fiscal_year_id', $this->fiscal_year_id)
                ->where('budget_type_id', $this->budget_type_id);
            
            if ($this->economic_code_id) {
                $demandQuery->where('economic_code_id', $this->economic_code_id);
            }

            $demandSum = $demandQuery->select(DB::raw('SUM(amount_demand) as demand'), DB::raw('SUM(amount_approved) as approved'))->first();
            $officeData['demand'] = $demandSum->demand ?? 0;
            $officeData['approved'] = $demandSum->approved ?? 0;

            // Current Released
            $releaseQuery = DB::table('budget_allocations')
                ->where('rpo_unit_id', $office->id)
                ->where('fiscal_year_id', $this->fiscal_year_id)
                ->where('budget_type_id', $this->budget_type_id);
            
            if ($this->economic_code_id) {
                $releaseQuery->where('economic_code_id', $this->economic_code_id);
            }
            
            $officeData['released'] = $releaseQuery->sum('amount');

            $data[$office->id] = $officeData;
        }

        return view('livewire.office-wise-budget', [
            'offices' => $offices,
            'prevYears' => $prevYears,
            'officeWiseData' => $data,
            'budgetTypes' => BudgetType::all(),
            'fiscalYears' => FiscalYear::orderBy('name', 'desc')->get(),
            'economicCodes' => EconomicCode::whereNotNull('parent_id')->orderBy('code')->get(),
            'selectedCode' => EconomicCode::find($this->economic_code_id),
        ])->extends('layouts.skot')->section('content');
    }
}
