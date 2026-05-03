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
    public $budgetAllocations = [];
    public $previousExpenses = [];
    public $officeName;
    public $fiscalYearName;
    public $totalBudget = 0;
    public $totalPrevious = 0;
    public $isDraftSaved = false;
    public $batchCode;
    public $submittedMonths = [];

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
        // Determine if user is from Headquarters
        $userOffice = $user->office;
        $this->isHq = $userOffice && $userOffice->parent_id === null;

        $this->rpo_unit_id = $user->rpo_unit_id;

        $this->loadExistingEntries();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        if (in_array($propertyName, ['selectedMonth', 'fiscal_year_id', 'rpo_unit_id'])) {
            $this->loadData();
        }
    }

    public function loadData()
    {
        if ($this->fiscal_year_id && $this->rpo_unit_id) {
            $this->submittedMonths = Expense::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->where('status', Expense::STATUS_APPROVED)
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('m'))
                ->unique()
                ->toArray();
        } else {
            $this->submittedMonths = [];
        }

        if ($this->selectedMonth && $this->fiscal_year_id && $this->rpo_unit_id) {
            // Set Names
            $this->officeName = RpoUnit::find($this->rpo_unit_id)?->name;
            $this->fiscalYearName = FiscalYear::find($this->fiscal_year_id)?->name;

            // Load Draft Entries to populate input boxes
            $draftExpenses = Expense::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->whereMonth('date', $this->selectedMonth)
                ->where('status', Expense::STATUS_DRAFT)
                ->get();

            if ($draftExpenses->count() > 0) {
                $this->isDraftSaved = true;
                
                // Reconstruct batchCode from the first entry's code
                // Code format: EXP-YYYYMMDD-RANDOM-CODEID
                $firstCode = $draftExpenses->first()->code;
                $parts = explode('-', $firstCode);
                if (count($parts) >= 3) {
                    $this->batchCode = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
                }

                foreach ($draftExpenses as $exp) {
                    $this->expenseEntries[$exp->economic_code_id] = [
                        'amount' => $exp->amount,
                        'description' => $exp->description
                    ];
                }
            } else {
                $this->isDraftSaved = false;
                $this->batchCode = null;
                $this->expenseEntries = [];
            }

            // Load sum of all expenses for summary/context
            $this->existingEntries = Expense::where('fiscal_year_id', $this->fiscal_year_id)
                ->where('rpo_unit_id', $this->rpo_unit_id)
                ->whereMonth('date', $this->selectedMonth)
                ->get()
                ->groupBy('economic_code_id')
                ->map(fn($group) => $group->sum('amount'))
                ->toArray();

            // Load Budget Allocations
            $allocations = BudgetAllocation::where([
                'rpo_unit_id' => $this->rpo_unit_id,
                'fiscal_year_id' => $this->fiscal_year_id,
            ])->get();

            $this->budgetAllocations = $allocations->groupBy('economic_code_id')
                ->map(fn($group) => $group->sum('amount'))
                ->toArray();

            $this->totalBudget = $allocations->sum('amount');

            // Load Previous Expenses (before selected month)
            $prevExpenses = Expense::where([
                'rpo_unit_id' => $this->rpo_unit_id,
                'fiscal_year_id' => $this->fiscal_year_id,
            ])
                ->where('date', '<', date('Y') . '-' . $this->selectedMonth . '-01')
                ->get();

            $this->previousExpenses = $prevExpenses->groupBy('economic_code_id')
                ->map(fn($group) => $group->sum('amount'))
                ->toArray();

            $this->totalPrevious = $prevExpenses->sum('amount');
        } else {
            $this->existingEntries = [];
            $this->budgetAllocations = [];
            $this->previousExpenses = [];
            $this->officeName = null;
            $this->fiscalYearName = null;
            $this->totalBudget = 0;
            $this->totalPrevious = 0;
        }
    }

    public function loadExistingEntries()
    {
        $this->loadData();
    }

    public function store()
    {
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

        if (!$this->batchCode) {
            $this->batchCode = 'EXP-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        }

        $targetStatus = Expense::STATUS_DRAFT; 
        $approvedBy = null;
        $approvedAt = null;

        $hasEntry = false;
        foreach ($this->expenseEntries as $codeId => $entry) {
            $amount = floatval($entry['amount'] ?? 0);
            $desc = $entry['description'] ?? null;

            if ($amount > 0) {
                // Safety check: ensure only leaf nodes get entries
                $code = EconomicCode::find($codeId);
                if (!$code || $code->children()->count() > 0) {
                    continue;
                }
                
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

                Expense::updateOrCreate(
                    ['code' => $this->batchCode . '-' . $codeId],
                    [
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
                    ]
                );
            } else {
                // If amount is set to 0, remove this specific entry from the draft batch
                Expense::where('code', $this->batchCode . '-' . $codeId)->delete();
            }
        }

        if ($hasEntry) {
            $this->isDraftSaved = true;
            session()->flash('message', __('Expenses saved as draft. Please click Submit to finalize.'));
        } else {
            session()->flash('error', __('No valid amounts entered.'));
        }
    }

    public function submitFinal()
    {
        abort_if(!$this->isDraftSaved, 403);

        $expenses = Expense::where('code', 'like', $this->batchCode . '-%')->get();

        foreach ($expenses as $expense) {
            $hasApproverInOffice = User::where('rpo_unit_id', $this->rpo_unit_id)
                ->permission('approve-expenses')
                ->exists();

            $targetStatus = $hasApproverInOffice ? Expense::STATUS_DRAFT : Expense::STATUS_APPROVED;
            $approvedBy = $hasApproverInOffice ? null : auth()->id();
            $approvedAt = $hasApproverInOffice ? null : now();

            $expense->update([
                'status' => $targetStatus,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ]);
        }

        session()->flash('message', __('Expenses Submitted Successfully.'));
        return $this->redirect(route('setup.expenses'), navigate: true);
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

        $completedFiscalYears = [];
        if ($this->rpo_unit_id) {
            $approvedDates = Expense::where('rpo_unit_id', $this->rpo_unit_id)
                ->where('status', Expense::STATUS_APPROVED)
                ->get(['fiscal_year_id', 'date']);
                
            $fyMonths = [];
            foreach ($approvedDates as $exp) {
                $month = \Carbon\Carbon::parse($exp->date)->format('m');
                $fyMonths[$exp->fiscal_year_id][$month] = true;
            }
            
            foreach ($fyMonths as $fyId => $months) {
                if (count($months) >= 12) {
                    $completedFiscalYears[] = $fyId;
                }
            }
        }

        // Only current + previous FY
        $allFiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        $today = now();
        $currentFY = $allFiscalYears->first(fn($fy) => $today->between($fy->start_date, $fy->end_date));

        $fiscalYears = collect();
        if ($currentFY) {
            $fiscalYears->push($currentFY);
            $previousFY = $allFiscalYears
                ->filter(fn($fy) => $fy->end_date < $currentFY->start_date)
                ->sortByDesc('end_date')
                ->first();
            if ($previousFY) {
                $fiscalYears->push($previousFY);
            }
        } else {
            // fallback: latest 2
            $fiscalYears = $allFiscalYears->take(2);
        }

        // Months ordered by selected FY start month
        $allMonths = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
        $fyMonths = $allMonths; // default
        if ($this->fiscal_year_id) {
            $selectedFY = $allFiscalYears->find($this->fiscal_year_id);
            if ($selectedFY) {
                $startMonth = (int) \Carbon\Carbon::parse($selectedFY->start_date)->format('m');
                $orderedMonths = [];
                for ($i = 0; $i < 12; $i++) {
                    $m = (($startMonth - 1 + $i) % 12) + 1;
                    $key = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $orderedMonths[$key] = $allMonths[$key];
                }
                $fyMonths = $orderedMonths;
            }
        }

        $offices = [];
        if ($this->isHq || auth()->user()->hasRole('Admin')) {
            $offices = RpoUnit::whereNotNull('parent_id')->get();
        }

        return view('livewire.setup.expense-create', [
            'economicCodes' => $orderedCodes,
            'fiscalYears' => $fiscalYears,
            'fyMonths' => $fyMonths,
            'offices' => $offices,
            'completedFiscalYears' => $completedFiscalYears,
        ])->layout('layouts.skot');
    }
}
