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
use Livewire\WithPagination;

class Expenses extends Component
{
    use WithPagination;

    public $code;

    public $date;

    public $rpo_unit_id;

    public $fiscal_year_id;

    public $economic_code_id;

    public $budget_type_id;

    // New properties for bulk entry
    public $selectedMonth;

    public $expenseEntries = []; // [code_id => ['amount' => 0, 'description' => '']]

    public $existingEntries = []; // [code_id => amount] for context

    public $expense_id;

    public $isOpen = 0;

    public $filter_fiscal_year_id;

    public $filter_month;

    public $totalReleased = 0;

    public $availableBalance = 0;

    protected $listeners = ['deleteConfirmed'];

    public function mount()
    {
        $activeFyId = get_active_fiscal_year_id();
        $this->filter_fiscal_year_id = $activeFyId;
        $this->fiscal_year_id = $activeFyId;
        $this->selectedMonth = date('m'); // Default to current month number
        $this->rpo_unit_id = auth()->user()->rpo_unit_id; // Set default RPO unit for new entries

        $defaultBudgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
        $this->budget_type_id = $defaultBudgetType ? $defaultBudgetType->id : '';

        $this->loadExistingEntries();
    }

    public function updatedFilterFiscalYearId()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('view-expenses'), 403);
        // Fetch Hierarchical Economic Codes
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

        $expenses = Expense::with(['office', 'fiscalYear', 'economicCode'])
            ->when($this->filter_fiscal_year_id, function ($query) {
                $query->where('fiscal_year_id', $this->filter_fiscal_year_id);
            })
            ->when($this->filter_month, function ($query) {
                $query->whereMonth('date', $this->filter_month);
            })
            ->when(! auth()->user()->can('view-all-offices-data'), function ($query) {
                $query->where('rpo_unit_id', auth()->user()->rpo_unit_id);
            })
            ->orderBy('date', 'desc')
            ->paginate(50); // Increased for better grouping view

        $groupedExpenses = $expenses->getCollection()->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->date)->format('F Y');
        });

        $monthlyTotals = Expense::selectRaw('DATE_FORMAT(date, "%M %Y") as month_year, SUM(amount) as total')
            ->when($this->filter_fiscal_year_id, function ($query) {
                $query->where('fiscal_year_id', $this->filter_fiscal_year_id);
            })
            ->when(! auth()->user()->can('view-all-offices-data'), function ($query) {
                $query->where('rpo_unit_id', auth()->user()->rpo_unit_id);
            })
            ->groupBy('month_year')
            ->pluck('total', 'month_year');

        $offices = RpoUnit::all();
        $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->get();

        return view('livewire.setup.expenses', [
            'expenses' => $expenses,
            'groupedExpenses' => $groupedExpenses,
            'monthlyTotals' => $monthlyTotals,
            'offices' => $offices,
            'fiscalYears' => $fiscalYears,
            'economicCodes' => $orderedCodes, // Hierarchical list
            'budgetTypes' => $budgetTypes,
        ])->layout('layouts.skot');
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

    public function calculateBalance()
    {
        if ($this->economic_code_id && $this->rpo_unit_id && $this->fiscal_year_id) {
            $released = BudgetAllocation::where([
                'economic_code_id' => $this->economic_code_id,
                'rpo_unit_id' => $this->rpo_unit_id,
                'fiscal_year_id' => $this->fiscal_year_id,
            ])->sum('amount');

            $spent = Expense::where([
                'economic_code_id' => $this->economic_code_id,
                'rpo_unit_id' => $this->rpo_unit_id,
                'fiscal_year_id' => $this->fiscal_year_id,
            ])->when($this->expense_id, fn($q) => $q->where('id', '!=', $this->expense_id))
                ->sum('amount');

            $this->totalReleased = $released;
            $this->availableBalance = $released - $spent;
        } else {
            $this->totalReleased = 0;
            $this->availableBalance = 0;
        }
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('create-expenses'), 403);
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->code = '';
        $this->date = '';
        $this->expense_id = '';
        $this->expenseEntries = [];
        $this->selectedMonth = date('m');
        $this->rpo_unit_id = auth()->user()->rpo_unit_id;
        $this->fiscal_year_id = get_active_fiscal_year_id();

        $defaultBudgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
        $this->budget_type_id = $defaultBudgetType ? $defaultBudgetType->id : '';

        $this->totalReleased = 0;
        $this->availableBalance = 0;

        $this->loadExistingEntries();
    }

    public function store()
    {
        if ($this->expense_id) {
            abort_if(auth()->user()->cannot('edit-expenses'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-expenses'), 403);
        }

        $this->validate([
            'rpo_unit_id' => 'required',
            'fiscal_year_id' => 'required',
            'selectedMonth' => 'required',
        ]);

        if (empty($this->expenseEntries)) {
            session()->flash('error', __('Please enter at least one expense amount.'));

            return;
        }

        if ($this->expense_id) {
            $expense = Expense::findOrFail($this->expense_id);

            // Ownership check again just in case
            if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
                abort(403);
            }

            $entry = $this->expenseEntries[$expense->economic_code_id] ?? null;
            if ($entry && floatval($entry['amount'] ?? 0) > 0) {
                $expense->update([
                    'amount' => floatval($entry['amount'] ?? 0),
                    'description' => $entry['description'] ?? null,
                ]);
                $hasEntry = true;
            }
        } else {
            // Calculate Year based on Fiscal Year and Selected Month
            $fy = FiscalYear::find($this->fiscal_year_id);
            $year = date('Y'); // Fallback

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

            // Auto-generate Batch/Voucher Code
            $autoCode = 'EXP-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

            $hasApproverInOffice = User::where('rpo_unit_id', $this->rpo_unit_id)
                ->permission('approve-expenses')
                ->exists();

            $targetStatus = $hasApproverInOffice ? Expense::STATUS_DRAFT : Expense::STATUS_APPROVED;
            $approvedBy = $hasApproverInOffice ? null : auth()->id();
            $approvedAt = $hasApproverInOffice ? null : now();

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
        }

        if ($hasEntry) {
            session()->flash('message', $this->expense_id ? 'Expenses Updated Successfully.' : 'Expenses Created Successfully.');
            $this->closeModal();
            $this->resetInputFields();
        } else {
            session()->flash('error', __('No valid amounts entered.'));
        }
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-expenses'), 403);
        $expense = Expense::findOrFail($id);

        if ($expense->status === Expense::STATUS_APPROVED) {
            session()->flash('error', __('Approved expenses cannot be edited.'));

            return;
        }

        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
            session()->flash('error', __('You can only edit expenses created by yourself.'));

            return;
        }

        $this->expense_id = $id;
        $this->code = $expense->code;
        // Edit mode is tricky with bulk view because existing expenses are individual rows.
        // For now, Edit might just open the SINGLE entry view OR we repurpose Bulk for creating NEW only.
        // Given complexity, let's make Create = Bulk, Edit = Single (or disable Edit for now/keep old).
        // The user asked for "expense add page" (Create).
        // Let's keep Edit logic simple for single item manipulation if needed, or redirect.
        // Re-implementing single edit within this component might be confusing if the view is table-based.
        // For this step, I will focus on CREATE as requested.

        // Populate single entry into the array for consistency
        $this->selectedMonth = date('m', strtotime($expense->date));
        $this->rpo_unit_id = $expense->rpo_unit_id;
        $this->fiscal_year_id = $expense->fiscal_year_id;
        $this->budget_type_id = $expense->budget_type_id;
        $this->expenseEntries[$expense->economic_code_id] = [
            'amount' => $expense->amount,
            'description' => $expense->description,
        ];

        $this->openModal();
    }

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-expenses'), 403);
        $expense = Expense::findOrFail($id);

        if ($expense->status === Expense::STATUS_APPROVED) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('Approved expenses cannot be deleted.')]);

            return;
        }

        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('You can only delete expenses created by yourself.')]);

            return;
        }

        $this->dispatch('delete-confirmation', $id);
    }

    public function approve($id)
    {
        $expense = Expense::findOrFail($id);

        // Permission check: Must have approve-expenses permission.
        // Additionally, unless they have view-all-offices-data, they must be in the same office.
        if (! auth()->user()->can('approve-expenses') && ! auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        if (! auth()->user()->can('view-all-offices-data') && auth()->user()->rpo_unit_id !== $expense->rpo_unit_id) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('You can only approve expenses within your own office.')]);

            return;
        }

        if ($expense->status === Expense::STATUS_APPROVED) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => __('Already approved.')]);

            return;
        }

        $expense->update([
            'status' => Expense::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->loadExistingEntries();
        $this->dispatch('alert', ['type' => 'success', 'message' => __('Expense Approved Successfully.')]);
    }

    public function deleteConfirmed($id)
    {
        abort_if(auth()->user()->cannot('delete-expenses'), 403);
        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }

        $expense = Expense::findOrFail($id);

        if ($expense->status === Expense::STATUS_APPROVED) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('Approved expenses cannot be deleted.')]);

            return;
        }

        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('You can only delete expenses created by yourself.')]);

            return;
        }

        $expense->delete();
        $this->loadExistingEntries();
        $this->dispatch('alert', ['type' => 'success', 'message' => __('Expense Deleted Successfully.')]);
    }
}
