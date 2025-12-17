<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\RpoUnit;

class RpoUnits extends Component
{
  public $rpo_units, $name, $code, $type, $parent_id, $district, $rpo_unit_id;
  public $isOpen = false;

  // Robust delete application
  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    $this->rpo_units = RpoUnit::with('parent')->orderBy('id', 'desc')->get();
    // Get potential parents (excluding self if editing could be added later, but simpler for now)
    $idsToExclude = $this->rpo_unit_id ? [$this->rpo_unit_id] : [];
    $parents = RpoUnit::whereNotIn('id', $idsToExclude)->get();

    return view('livewire.setup.rpo-units', [
      'parents' => $parents
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
    $this->name = '';
    $this->code = '';
    $this->type = ''; // Default or empty
    $this->parent_id = null;
    $this->district = '';
    $this->rpo_unit_id = '';
  }

  public function store()
  {
    $this->validate([
      'name' => 'required',
      'code' => 'required|unique:rpo_units,code,' . $this->rpo_unit_id,
      'type' => 'required|in:ministry,headquarters,regional,divisional',
      'parent_id' => 'nullable|exists:rpo_units,id'
    ]);

    RpoUnit::updateOrCreate(['id' => $this->rpo_unit_id], [
      'name' => $this->name,
      'code' => $this->code,
      'type' => $this->type,
      'parent_id' => $this->parent_id ?: null, // Ensure null if empty
      'district' => $this->district,
      'status' => true
    ]);

    session()->flash(
      'message',
      $this->rpo_unit_id ? 'Office Updated Successfully.' : 'Office Created Successfully.'
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    $unit = RpoUnit::findOrFail($id);
    $this->rpo_unit_id = $id;
    $this->name = $unit->name;
    $this->code = $unit->code;
    $this->type = $unit->type;
    $this->parent_id = $unit->parent_id;
    $this->district = $unit->district;

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
    RpoUnit::find($id)->delete();
    session()->flash('message', 'Office Deleted Successfully.');
  }
}
