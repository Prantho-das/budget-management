<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\RpoUnit;
use App\Models\FiscalYear;

class Expenses extends Component
{
  public $expenses, $code, $amount, $description, $date, $expense_category_id, $rpo_unit_id, $fiscal_year_id, $expense_id;
  public $isOpen = false;

  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    $this->expenses = Expense::with(['category', 'office', 'fiscalYear'])->orderBy('id', 'desc')->get();
    $categories = ExpenseCategory::all();
    $offices = RpoUnit::all();
    $fiscalYears = FiscalYear::all(); // Should logically filter by active, but fetching all for now

    return view('livewire.setup.expenses', [
      'categories' => $categories,
      'offices' => $offices,
      'fiscalYears' => $fiscalYears
    ])->extends('layouts.skot')->section('content');
  }

  public function create()
  {
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
    $this->code = '';
    $this->amount = '';
    $this->description = '';
    $this->date = date('Y-m-d');
    $this->expense_category_id = '';
    $this->rpo_unit_id = '';
    $this->fiscal_year_id = '';
    $this->expense_id = '';
  }

  public function store()
  {
    $this->validate([
      'code' => 'required|unique:expenses,code,' . $this->expense_id,
      'amount' => 'required|numeric',
      'date' => 'required|date',
      'expense_category_id' => 'required|exists:expense_categories,id',
      'rpo_unit_id' => 'required|exists:rpo_units,id',
      'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
    ]);

    Expense::updateOrCreate(['id' => $this->expense_id], [
      'code' => $this->code,
      'amount' => $this->amount,
      'description' => $this->description,
      'date' => $this->date,
      'expense_category_id' => $this->expense_category_id,
      'rpo_unit_id' => $this->rpo_unit_id,
      'fiscal_year_id' => $this->fiscal_year_id,
    ]);

    session()->flash(
      'message',
      $this->expense_id ? 'Expense Updated Successfully.' : 'Expense Created Successfully.'
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    $expense = Expense::findOrFail($id);
    $this->expense_id = $id;
    $this->code = $expense->code;
    $this->amount = $expense->amount;
    $this->description = $expense->description;
    $this->date = $expense->date;
    $this->expense_category_id = $expense->expense_category_id;
    $this->rpo_unit_id = $expense->rpo_unit_id;
    $this->fiscal_year_id = $expense->fiscal_year_id;

    $this->openModal();
  }

  public function delete($id)
  {
    $this->dispatch('delete-confirmation', $id);
  }

  public function deleteConfirmed($id)
  {
    if (is_array($id)) {
      $id = $id['id'] ?? $id[0];
    }
    Expense::find($id)->delete();
    session()->flash('message', 'Expense Deleted Successfully.');
  }
}
