<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\RpoUnit;

class RpoUnits extends Component
{
  public $rpo_units, $name, $code, $parent_id, $district, $rpo_unit_id;
  public $isOpen = false;

  // Robust delete application
  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    abort_if(auth()->user()->cannot('view-offices'), 403);
    $this->rpo_units = RpoUnit::with('parent')->orderBy('id', 'desc')->get();
    // Get potential parents
    $idsToExclude = $this->rpo_unit_id ? [$this->rpo_unit_id] : [];
    $parents = RpoUnit::whereNotIn('id', $idsToExclude)->get();

    return view('livewire.setup.rpo-units', [
      'parents' => $parents
    ])->extends('layouts.skot')->section('content');
  }

  public function create()
  {
    abort_if(auth()->user()->cannot('create-offices'), 403);
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
    $this->parent_id = null;
    $this->district = '';
    $this->rpo_unit_id = '';
  }

  public function store()
  {
    if ($this->rpo_unit_id) {
        abort_if(auth()->user()->cannot('edit-offices'), 403);
    } else {
        abort_if(auth()->user()->cannot('create-offices'), 403);
    }

    $this->validate([
      'name' => 'required',
      'code' => 'required|unique:rpo_units,code,' . $this->rpo_unit_id,
      'parent_id' => 'nullable|exists:rpo_units,id'
    ]);

    RpoUnit::updateOrCreate(['id' => $this->rpo_unit_id], [
      'name' => $this->name,
      'code' => $this->code,
      'parent_id' => $this->parent_id ?: null,
      'district' => $this->district,
      'status' => true
    ]);

    session()->flash(
      'message',
      $this->rpo_unit_id ? __('Office Updated Successfully.') : __('Office Created Successfully.')
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    abort_if(auth()->user()->cannot('edit-offices'), 403);
    $unit = RpoUnit::findOrFail($id);
    $this->rpo_unit_id = $id;
    $this->name = $unit->name;
    $this->code = $unit->code;
    $this->parent_id = $unit->parent_id;
    $this->district = $unit->district;

    $this->openModal();
  }

  public function delete($id)
  {
    abort_if(auth()->user()->cannot('delete-offices'), 403);
    $this->dispatch('delete-confirmation', $id);
  }

  public function deleteConfirmed($id)
  {
    abort_if(auth()->user()->cannot('delete-offices'), 403);
    if (is_array($id)) {
      $id = $id['id'] ?? $id[0];
    }
    RpoUnit::find($id)->delete();
    session()->flash('message', __('Office Deleted Successfully.'));
  }
}
