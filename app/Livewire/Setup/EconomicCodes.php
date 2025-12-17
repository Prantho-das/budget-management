<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\EconomicCode;

class EconomicCodes extends Component
{
  public $codes, $name, $code, $description, $economic_code_id;
  public $isOpen = false;

  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    $this->codes = EconomicCode::orderBy('id', 'desc')->get();
    return view('livewire.setup.economic-codes')
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
    $this->description = '';
    $this->economic_code_id = '';
  }

  public function store()
  {
    $this->validate([
      'name' => 'required',
      'code' => 'required|unique:economic_codes,code,' . $this->economic_code_id,
    ]);

    EconomicCode::updateOrCreate(['id' => $this->economic_code_id], [
      'name' => $this->name,
      'code' => $this->code,
      'description' => $this->description
    ]);

    session()->flash(
      'message',
      $this->economic_code_id ? 'Economic Code Updated Successfully.' : 'Economic Code Created Successfully.'
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    $code = EconomicCode::findOrFail($id);
    $this->economic_code_id = $id;
    $this->name = $code->name;
    $this->code = $code->code;
    $this->description = $code->description;

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
    EconomicCode::find($id)->delete();
    session()->flash('message', 'Economic Code Deleted Successfully.');
  }
}
