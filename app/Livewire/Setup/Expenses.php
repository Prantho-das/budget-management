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

  public $code, $amount, $description, $date, $rpo_unit_id, $fiscal_year_id, $economic_code_id, $budget_type_id;
  public $totalReleased = 0;
  public $availableBalance = 0;
  public $isOpen = false;
  public $expense_id; // Removed $expenses as it's now handled by pagination in render

  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    abort_if(auth()->user()->cannot('view-expenses'), 403);
    $expenses = Expense::with(['office', 'fiscalYear', 'economicCode'])
      ->when(!auth()->user()->can('view-all-offices-data'), function ($query) {
        $query->where('rpo_unit_id', auth()->user()->rpo_unit_id);
      })
      ->orderBy('id', 'desc')
      ->paginate(10);
    //$categories = ExpenseCategory::all();
    $offices = RpoUnit::all();
    $fiscalYears = FiscalYear::orderBy('name', 'desc')->get();
    $economicCodes = EconomicCode::all();
    $budgetTypes = \App\Models\BudgetType::all();

    return view('livewire.setup.expenses', [
      'expenses' => $expenses,
      'offices' => $offices,
      'fiscalYears' => $fiscalYears,
      'economicCodes' => $economicCodes,
      'budgetTypes' => $budgetTypes
    ])->extends('layouts.skot')->section('content');
  }

  public function updated($propertyName)
  {
    if (in_array($propertyName, ['economic_code_id', 'rpo_unit_id', 'fiscal_year_id'])) {
      $this->calculateBalance();
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
  }

  private function resetInputFields()
  {
    $this->code = 'EXP-' . strtoupper(uniqid());
    $this->amount = '';
    $this->description = '';
    $this->date = date('Y-m-d');
    $this->rpo_unit_id = auth()->user()->rpo_unit_id;
    $activeFy = FiscalYear::where('status', true)->first();
    $this->fiscal_year_id = $activeFy ? $activeFy->id : '';
    $this->economic_code_id = '';
    $this->budget_type_id = '';
    $this->expense_id = '';
    $this->availableBalance = 0;
    $this->totalReleased = 0;

    if ($this->rpo_unit_id && $this->fiscal_year_id) {
        $this->calculateBalance();
    }
  }

  public function store()
  {
    if ($this->expense_id) {
      abort_if(auth()->user()->cannot('edit-expenses'), 403);
    } else {
      abort_if(auth()->user()->cannot('create-expenses'), 403);
    }

    $this->calculateBalance();

    $this->validate([
      'code' => 'required|unique:expenses,code,' . $this->expense_id,
      'amount' => 'required|numeric|min:0.01|max:' . ($this->availableBalance + ($this->expense_id ? Expense::find($this->expense_id)->amount : 0)),
      'date' => 'required|date',
      'rpo_unit_id' => 'required',
      'fiscal_year_id' => 'required',
      'economic_code_id' => 'required',
    ]);

    Expense::updateOrCreate(['id' => $this->expense_id], [
      'code' => $this->code,
      'amount' => $this->amount,
      'description' => $this->description,
      'date' => $this->date,
      'rpo_unit_id' => $this->rpo_unit_id,
      'fiscal_year_id' => $this->fiscal_year_id,
      'economic_code_id' => $this->economic_code_id,
      'budget_type_id' => $this->budget_type_id,
    ]);

    session()->flash(
      'message',
      $this->expense_id ? __('Expense Updated Successfully.') : __('Expense Created Successfully.')
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    abort_if(auth()->user()->cannot('edit-expenses'), 403);
    $expense = Expense::findOrFail($id);
    $this->expense_id = $id;
    $this->code = $expense->code;
    $this->amount = $expense->amount;
    $this->description = $expense->description;
    $this->date = $expense->date;
    $this->economic_code_id = $expense->economic_code_id;
    $this->budget_type_id = $expense->budget_type_id;
    $this->rpo_unit_id = $expense->rpo_unit_id;
    $this->fiscal_year_id = $expense->fiscal_year_id;

    $this->calculateBalance();
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
