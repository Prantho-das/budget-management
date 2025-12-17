<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\ExpenseCategory;

class ExpenseCategories extends Component
{
  public $categories, $name, $code, $category_id;
  public $isOpen = false;

  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    $this->categories = ExpenseCategory::orderBy('id', 'desc')->get();
    return view('livewire.setup.expense-categories')
      ->extends('layouts.skot')
      ->section('content');
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
    $this->name = '';
    $this->code = '';
    $this->category_id = '';
  }

  public function store()
  {
    $this->validate([
      'name' => 'required',
      'code' => 'required|unique:expense_categories,code,' . $this->category_id,
    ]);

    ExpenseCategory::updateOrCreate(['id' => $this->category_id], [
      'name' => $this->name,
      'code' => $this->code
    ]);

    session()->flash(
      'message',
      $this->category_id ? 'Category Updated Successfully.' : 'Category Created Successfully.'
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    $category = ExpenseCategory::findOrFail($id);
    $this->category_id = $id;
    $this->name = $category->name;
    $this->code = $category->code;

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
    ExpenseCategory::find($id)->delete();
    session()->flash('message', 'Category Deleted Successfully.');
  }
}
