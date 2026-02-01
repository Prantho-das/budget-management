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
    public $isHq = false;
    public $expenseEntries = [];
    public $existingEntries = [];

    protected function rules()
    {
        return [
            'rpo_unit_id' => 'required',
            'fiscal_year_id' => 'required',
            'selectedMonth' => 'required',
        ];
    }

    public function mount()
    {
        abort_if(auth()->user()->cannot('create-expenses'), 403);

        // Set default month to previous month
        $prevMonthDate = now()->subMonth();
        $this->selectedMonth = $prevMonthDate->format('m');

        // Find fiscal year for the previous month
        $fyName = current_fiscal_year($prevMonthDate);
        $fy = FiscalYear::where('name', $fyName)->first();

        if ($fy) {
            $this->fiscal_year_id = $fy->id;
        } else {
            $this->fiscal_year_id = get_active_fiscal_year_id();
        }

        $user = auth()->user();
        $this->rpo_unit_id = $user->rpo_unit_id;

        // Determine if user is from Headquarters
        $userOffice = $user->office;
        $this->isHq = $userOffice && $userOffice->parent_id === null;

        $this->loadExistingEntries();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

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
        dd(request()->all());
        abort_if(auth()->user()->cannot('create-expenses'), 403);

        $this->validate();

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

                // Find Budget Type dynamically from allocation
                $allocation = BudgetAllocation::where([
                    'economic_code_id' => $codeId,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'fiscal_year_id' => $this->fiscal_year_id,
                ])->first();

                $entryBudgetTypeId = $allocation ? $allocation->budget_type_id : null;

                if (!$entryBudgetTypeId) {
                    $defaultType = BudgetType::where('status', true)->orderBy('order_priority')->first();
                    $entryBudgetTypeId = $defaultType ? $defaultType->id : null;
                }

                Expense::create([
                    'code' => $autoCode . '-' . $codeId,
                    'amount' => $amount,
                    'description' => $desc,
                    'date' => $expenseDate,
                    'rpo_unit_id' => $this->rpo_unit_id,
                    'fiscal_year_id' => $this->fiscal_year_id,
                    'economic_code_id' => $codeId,
                    'budget_type_id' => $entryBudgetTypeId,
                    'status' => $targetStatus,
                    'created_by' => auth()->id(),
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                ]);
            }
        }

        if ($hasEntry) {
            session()->flash('message', 'Expenses Created Successfully.');
           // return $this->redirect(route('setup.expenses'), navigate: true);
        } else {
            session()->flash('error', __('No valid amounts entered.'));
        }
    }

    public function cancel()
    {
        return $this->redirect(route('setup.expenses'), navigate: true);
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

        $offices = [];
        if ($this->isHq || auth()->user()->hasRole('Admin')) {
            $offices = RpoUnit::all();
        }

        return view('livewire.setup.expense-create', [
            'economicCodes' => $orderedCodes,
            'fiscalYears' => $fiscalYears,
            'offices' => $offices,
        ])->extends('layouts.skot')->section('content');
    }
}
