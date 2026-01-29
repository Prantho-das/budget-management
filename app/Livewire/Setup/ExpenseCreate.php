<?php

namespace App\Livewire\Setup;

use App\Models\BudgetAllocation;
use App\Models\BudgetType;
use App\Models\EconomicCode;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\User;
use Livewire\Component;

class ExpenseCreate extends Component
{
    public $selectedMonth;
    public $fiscal_year_id;
    public $rpo_unit_id;
    public $budget_type_id;
    public $expenseEntries = [];
    public $existingEntries = [];

    public function mount()
    {
        abort_if(auth()->user()->cannot('create-expenses'), 403);

        $activeFyId = get_active_fiscal_year_id();
        $this->fiscal_year_id = $activeFyId;
        $this->selectedMonth = date('m');
        $this->rpo_unit_id = auth()->user()->rpo_unit_id;

        $defaultBudgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
        $this->budget_type_id = $defaultBudgetType ? $defaultBudgetType->id : '';

        $this->loadExistingEntries();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedMonth', 'fiscal_year_id', 'rpo_unit_id'])) {
            $this->loadExistingEntries();
        }
    }

    public function loadExistingEntries()
    {
        if ($this->selectedMonth && $this->fiscal_year_id && $this->rpo_unit_id) {
            $this->existingEntries = Expense::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->whereMonth('date', $this->selectedMonth)
                ->get()
                ->groupBy('economic_code_id')
                ->map(fn($group) => $group->sum('amount'))
                ->toArray();
        } else {
            $this->existingEntries = [];
        }
    }

    public function store()
    {
        abort_if(auth()->user()->cannot('create-expenses'), 403);

        $this->validate([
            'rpo_unit_id' => 'required',
            'fiscal_year_id' => 'required',
            'selectedMonth' => 'required',
        ]);

        if (empty($this->expenseEntries)) {
            session()->flash('error', __('Please enter at least one expense amount.'));
            return;
        }

        // Calculate Year based on Fiscal Year and Selected Month
        $fy = FiscalYear::find($this->fiscal_year_id);
        $year = date('Y');

        if ($fy) {
            $fyStart = \Carbon\Carbon::parse($fy->start_date);
            $selectedMonthInt = intval($this->selectedMonth);
            $fyStartMonth = $fyStart->month;

            if ($selectedMonthInt >= $fyStartMonth) {
                $year = $fyStart->year;
            } else {
                $year = $fyStart->year + 1;
            }
        }

        $expenseDate = $year . '-' . $this->selectedMonth . '-01';
        $autoCode = 'EXP-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        $hasApproverInOffice = User::where('rpo_unit_id', $this->rpo_unit_id)
            ->permission('approve-expenses')
            ->exists();

        $targetStatus = $hasApproverInOffice ? Expense::STATUS_DRAFT : Expense::STATUS_APPROVED;
        $approvedBy = $hasApproverInOffice ? null : auth()->id();
        $approvedAt = $hasApproverInOffice ? null : now();

        $hasEntry = false;
        foreach ($this->expenseEntries as $codeId => $entry) {
            $amount = floatval($entry['amount'] ?? 0);
            $desc = $entry['description'] ?? null;

            if ($amount > 0) {
                $hasEntry = true;

                Expense::create([
                    'code' => $autoCode . '-' . $codeId,
                    'amount' => $amount,
                    'description' => $desc,
                    'date' => $expenseDate,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'economic_code_id' => $codeId,
                    'budget_type_id' => $this->budget_type_id,
                    'status' => $targetStatus,
                    'created_by' => auth()->id(),
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                ]);
            }
        }

        if ($hasEntry) {
            session()->flash('message', 'Expenses Created Successfully.');
            return redirect()->route('setup.expenses');
        } else {
            session()->flash('error', __('No valid amounts entered.'));
        }
    }

    public function cancel()
    {
        return redirect()->route('setup.expenses');
    }

    public function render()
    {
        // Fetch Hierarchical Economic Codes (all 3 levels)
        $allCodes = EconomicCode::with(['children', 'parent'])->get();
        $orderedCodes = [];
        $roots = $allCodes->whereNull('parent_id')->sortBy('code');

        foreach ($roots as $root) {
            $orderedCodes[] = $root;
            $children = $allCodes->where('parent_id', $root->id)->sortBy('code');

            foreach ($children as $child) {
                $orderedCodes[] = $child;
                // Add grandchildren (third layer)
                $grandchildren = $allCodes->where('parent_id', $child->id)->sortBy('code');
                foreach ($grandchildren as $grandchild) {
                    $orderedCodes[] = $grandchild;
                }
            }
        }

        $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->get();

        return view('livewire.setup.expense-create', [
            'economicCodes' => $orderedCodes,
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
        ])->extends('layouts.skot')->section('content');
    }
}
