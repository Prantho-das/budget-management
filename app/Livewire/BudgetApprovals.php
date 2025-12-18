<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetApprovals extends Component
{
    public $fiscal_year_id;
    public $selected_office_id;
    public $selected_budget_type;
    public $childOffices = [];
    public $demands = []; // [economic_code_id => amount]
    public $remarks = []; // [unique_key => text]
    public $viewMode = 'inbox'; // 'inbox' or 'detail'

    public function mount()
    {
        $fiscalYear = FiscalYear::where('status', true)->latest()->first();
        $this->fiscal_year_id = $fiscalYear ? $fiscalYear->id : null;
        $this->loadChildSubmissions();
    }

    public function loadChildSubmissions()
    {
        if (!$this->fiscal_year_id) return;

        $userOfficeId = Auth::user()->rpo_unit_id;

        // Find children of this office
        $children = RpoUnit::where('parent_id', $userOfficeId)->get();

        $this->childOffices = [];
        foreach ($children as $office) {
            $submissions = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $office->id)
                ->where('status', '!=', 'draft')
                ->select('budget_type', 'status', DB::raw('SUM(amount_demand) as total_demand'))
                ->groupBy('budget_type', 'status')
                ->get();

            foreach ($submissions as $sub) {
                $this->childOffices[] = [
                    'id' => $office->id,
                    'name' => $office->name,
                    'code' => $office->code,
                    'budget_type' => $sub->budget_type,
                    'total_demand' => $sub->total_demand,
                    'status' => $sub->status,
                ];
            }
        }
    }

    public function viewDetails($officeId, $budgetType)
    {
        $this->selected_office_id = $officeId;
        $this->selected_budget_type = $budgetType;
        $this->viewMode = 'detail';
        $this->loadDemands();
    }

    public function loadDemands()
    {
        $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $this->selected_office_id)
            ->where('budget_type', $this->selected_budget_type)
            ->with('economicCode')
            ->get();

        $this->demands = [];
        foreach ($estimations as $est) {
            $this->demands[$est->economic_code_id] = [
                'id' => $est->id,
                'code' => $est->economicCode->code,
                'name' => $est->economicCode->name,
                'demand' => $est->amount_demand,
                'approved' => $est->amount_approved ?? $est->amount_demand,
                'status' => $est->status,
                'remarks' => $est->remarks,
            ];
        }
    }

    public function backToInbox()
    {
        $this->viewMode = 'inbox';
        $this->loadChildSubmissions();
    }

    public function approve($officeId, $budgetType)
    {
        $userOfficeType = Auth::user()->office->type ?? 'office';
        $newStatus = 'approved';

        if ($userOfficeType === 'district') {
            $newStatus = 'district_approved';
        } elseif ($userOfficeType === 'headquarters') {
            $newStatus = 'hq_approved';
        }

        BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $officeId)
            ->where('budget_type', $budgetType)
            ->update([
                'status' => $newStatus,
                'amount_approved' => DB::raw('COALESCE(amount_approved, amount_demand)')
            ]);

        // PHASE 4: Allocation Records Creation (if HQ approves)
        if ($newStatus === 'hq_approved') {
            $estimations = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $officeId)
                ->where('budget_type', $budgetType)
                ->get();

            foreach ($estimations as $est) {
                BudgetAllocation::updateOrCreate(
                    [
                        'fiscal_year_id' => $est->fiscal_year_id,
                        'budget_type'    => $est->budget_type,
                        'rpo_unit_id' => $est->rpo_unit_id,
                        'economic_code_id' => $est->economic_code_id,
                    ],
                    [
                        'amount' => $est->amount_approved ?? $est->amount_demand,
                        'remarks' => 'Finalized and allocated by HQ (' . $budgetType . ')'
                    ]
                );
            }
        }

        session()->flash('message', 'Budget Approved Successfully.');
        $this->backToInbox();
    }

    public function reject($officeId, $budgetType)
    {
        $key = $officeId . '_' . str_replace(' ', '_', $budgetType);
        $this->validate([
            "remarks.$key" => 'required|min:5'
        ], [
            "remarks.$key.required" => 'Please provide a reason for rejection.'
        ]);

        BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id)
            ->where('rpo_unit_id', $officeId)
            ->where('budget_type', $budgetType)
            ->update([
                'status' => 'rejected',
                'remarks' => $this->remarks[$key]
            ]);

        session()->flash('message', 'Budget Rejected.');
        $this->backToInbox();
    }

    public function updateAdjustment($estimationId, $amount)
    {
        $est = BudgetEstimation::find($estimationId);
        // Prevent adjustments if already hq_approved
        if ($est && $est->status !== 'hq_approved' && $est->status !== 'approved') {
            $est->update(['amount_approved' => $amount]);
        }
    }

    public function render()
    {
        return view('livewire.budget-approvals', [
            'office' => $this->selected_office_id ? RpoUnit::find($this->selected_office_id) : null
        ])->extends('layouts.skot')->section('content');
    }
}
