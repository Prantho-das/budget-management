<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\RpoUnit;
use App\Models\FiscalYear;

use App\Models\EconomicCode;
use App\Models\BudgetType;
use App\Models\BudgetAllocation;

class Expenses extends Component
{
  use WithPagination;

    public $code, $date, $rpo_unit_id, $fiscal_year_id, $economic_code_id, $budget_type_id; 
    // New properties for bulk entry
    public $selectedMonth;
    public $expenseEntries = []; // [code_id => ['amount' => 0, 'description' => '']]
    
    public $expense_id;
    public $isOpen = 0;
    
    public $filter_fiscal_year_id;

  public $totalReleased = 0;
  public $availableBalance = 0;

  protected $listeners = ['deleteConfirmed'];

  public function mount()
  {
      $activeFy = FiscalYear::where('status', true)->first();
      $this->filter_fiscal_year_id = $activeFy ? $activeFy->id : '';
      $this->fiscal_year_id = $activeFy ? $activeFy->id : '';
      $this->selectedMonth = date('m'); // Default to current month number
      $this->rpo_unit_id = auth()->user()->rpo_unit_id; // Set default RPO unit for new entries
      
      $defaultBudgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
      $this->budget_type_id = $defaultBudgetType ? $defaultBudgetType->id : '';
  }

  public function updatedFilterFiscalYearId()
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
      ->when($this->filter_fiscal_year_id, function($query) {
          $query->where('fiscal_year_id', $this->filter_fiscal_year_id);
      })
      ->when(!auth()->user()->can('view-all-offices-data'), function ($query) {
        $query->where('rpo_unit_id', auth()->user()->rpo_unit_id);
      })
      ->orderBy('id', 'desc')
      ->paginate(10);

    $offices = RpoUnit::all();
    $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
    $budgetTypes = BudgetType::where('status', true)->get();

    return view('livewire.setup.expenses', [
      'expenses' => $expenses,
      'offices' => $offices,
      'fiscalYears' => $fiscalYears,
      'economicCodes' => $orderedCodes, // Hierarchical list
      'budgetTypes' => $budgetTypes
    ])->extends('layouts.skot')->section('content');
  }

  public function updated($propertyName)
  {
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
    $activeFy = FiscalYear::where('status', true)->first();
    $this->fiscal_year_id = $activeFy ? $activeFy->id : '';
    
    $defaultBudgetType = BudgetType::where('status', true)->orderBy('order_priority')->first();
    $this->budget_type_id = $defaultBudgetType ? $defaultBudgetType->id : '';
    
    $this->totalReleased = 0;
    $this->availableBalance = 0;
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

    $hasEntry = false;
    
    // Calculate Year based on Fiscal Year and Selected Month
    $fy = FiscalYear::find($this->fiscal_year_id);
    $year = date('Y'); // Fallback
    
    if ($fy) {
        $fyStart = \Carbon\Carbon::parse($fy->start_date);
        
        // Example: FY 2024-2025 (July 24 - June 25)
        // If Selected Month >= FY Start Month (7) -> Use Start Year (2024)
        // If Selected Month < FY Start Month (7) -> Use End Year (2025) which is Start Year + 1 (usually)
        // Wait, fiscal year might span differently.
        // Safer: 
        // If Month is 7,8,9,10,11,12 -> it must be Start Year
        // If Month is 1,2,3,4,5,6 -> it must be End Year (Start Year + 1)
        // This assumes typical July-June FY. 
        // General logic:
        
        $selectedMonthInt = intval($this->selectedMonth);
        $fyStartMonth = $fyStart->month;
        
        if ($selectedMonthInt >= $fyStartMonth) {
             $year = $fyStart->year;
        } else {
             // Basic assumption: If selected month is earlier in year than start month, it's the next year
             $year = $fyStart->year + 1;
        }
    }
    
    $expenseDate = $year . '-' . $this->selectedMonth . '-01'; 
    
    // Auto-generate Batch/Voucher Code
    // Format: EXP-{Ymd}-{Random6}
    $autoCode = 'EXP-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

    foreach ($this->expenseEntries as $codeId => $entry) {
        $amount = floatval($entry['amount'] ?? 0);
        $desc = $entry['description'] ?? null;

        if ($amount > 0) {
            $hasEntry = true;

            Expense::create([
                'code' => $autoCode . '-' . $codeId, // Append Code ID to ensure uniqueness per line item
                'amount' => $amount,
                'description' => $desc,
                'date' => $expenseDate,
                'rpo_unit_id' => $this->rpo_unit_id,
                'fiscal_year_id' => $this->fiscal_year_id,
                'economic_code_id' => $codeId,
                'budget_type_id' => $this->budget_type_id 
            ]);
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
    $this->expense_id = $id;
    $this->code = $expense->code;
    // Edit mode is tricky with bulk view because existing expenses are individual rows.
    // For now, Edit might just open the SINGLE entry view OR we repurpose Bulk for creating NEW only.
    // Given complexity, let's make Create = Bulk, Edit = Single (or disable Edit for now/keep old).
    // The user asked for "expense add page" (Create).
    // Let's keep Edit logic simple for single item manipulation if needed, or redirect.
    // Re-implementing single edit within this component might be confusing if the view is table-based.
    // For this step, I will focus on CREATE as requested.
    
    // Populate single entry into the array for consistency?
    $this->selectedMonth = date('Y-m', strtotime($expense->date));
    $this->rpo_unit_id = $expense->rpo_unit_id;
    $this->fiscal_year_id = $expense->fiscal_year_id;
    $this->budget_type_id = $expense->budget_type_id;
    $this->expenseEntries[$expense->economic_code_id] = [
        'amount' => $expense->amount,
        'description' => $expense->description
    ];

    $this->openModal();
  }

  public function delete($id)
  {
    abort_if(auth()->user()->cannot('delete-expenses'), 403);
    $this->dispatch('delete-confirmation', $id);
  }

  public function deleteConfirmed($id)
  {
    abort_if(auth()->user()->cannot('delete-expenses'), 403);
    if (is_array($id)) {
      $id = $id['id'] ?? $id[0];
    }
    Expense::find($id)->delete();
    session()->flash('message', __('Expense Deleted Successfully.'));
  }
}
