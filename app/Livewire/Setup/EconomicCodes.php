<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\EconomicCode;

class EconomicCodes extends Component
{
  public $codes, $name, $code, $description, $economic_code_id, $parent_id;
  public $selectedParentId, $selectedSubHeadId, $isUsed = false;
  public $isOpen = false;
  public $search = '';

  public function updatedCode($value)
  {
    $length = strlen($value);

    if ($length === 2) {
      $this->selectedParentId = '';
      $this->selectedSubHeadId = '';
    } elseif ($length === 4) {
      $parentCode = substr($value, 0, 2);
      $parent = EconomicCode::where('code', $parentCode)->first();

      if ($parent) {
        $this->selectedParentId = $parent->id;
        $this->selectedSubHeadId = '';
      }
    } elseif ($length === 6) {
      $subHeadCode = substr($value, 0, 4);
      $subHead = EconomicCode::where('code', $subHeadCode)->first();

      if ($subHead) {
        $this->selectedParentId = $subHead->parent_id;
        // We need to ensure the subHead list is updated in the view, 
        // which happens in render() based on selectedParentId.
        // Livewire should handle the binding if the value matches an option.
        $this->selectedSubHeadId = $subHead->id;
      }
    }

    $this->dispatch('select2-reinit');
  }

  protected $listeners = ['deleteConfirmed'];

  public function render()
  {
    abort_if(auth()->user()->cannot('view-economic-codes'), 403);

    // Hierarchical fetch with live search
    $this->codes = EconomicCode::whereNull('parent_id')
      ->with(['children' => function ($query) {
        $query->orderBy('code', 'asc')->with(['children' => function ($q) {
          $q->orderBy('code', 'asc');
        }]);
      }])
      ->when($this->search, function ($query) {
        $query->where(function ($q) {
          $term = '%' . $this->search . '%';
          $q->where('name', 'like', $term)
            ->orWhere('code', 'like', $term)
            ->orWhereHas('children', function ($subQ) use ($term) {
              $subQ->where('name', 'like', $term)
                ->orWhere('code', 'like', $term)
                ->orWhereHas('children', function ($projectQ) use ($term) {
                  $projectQ->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term);
                });
            });
        });
      })
      ->orderBy('code', 'asc')
      ->get();

    $rootCodes = EconomicCode::whereNull('parent_id')
      ->when($this->economic_code_id, function ($query) {
        return $query->where('id', '!=', $this->economic_code_id);
      })
      ->orderBy('code', 'asc')
      ->get();

    $subHeadCodes = collect();
    if ($this->selectedParentId) {
      $subHeadCodes = EconomicCode::where('parent_id', $this->selectedParentId)
        ->when($this->economic_code_id, function ($query) {
          return $query->where('id', '!=', $this->economic_code_id);
        })
        ->orderBy('code', 'asc')
        ->get();
    }

    return view('livewire.setup.economic-codes', [
      'rootCodes' => $rootCodes,
      'subHeadCodes' => $subHeadCodes,
    ])
      ->extends('layouts.skot')
      ->section('content');
  }

  public function updatedSelectedParentId($value)
  {
    $this->selectedSubHeadId = '';
  }

  public function create()
  {
    abort_if(auth()->user()->cannot('create-economic-codes'), 403);
    $this->resetInputFields();
    $this->openModal();
  }

  public function openModal()
  {
    $this->isOpen = true;
    $this->dispatch('select2-reinit');
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
    $this->parent_id = '';
    $this->selectedParentId = '';
    $this->selectedSubHeadId = '';
    $this->isUsed = false;
  }

  public function store()
  {
    if ($this->economic_code_id) {
      abort_if(auth()->user()->cannot('edit-economic-codes'), 403);
    } else {
      abort_if(auth()->user()->cannot('create-economic-codes'), 403);
    }

    $validationRules = [
      'name' => 'required',
    ];

    if (!$this->isUsed) {
      $validationRules['code'] = 'required|unique:economic_codes,code,' . $this->economic_code_id;
      $validationRules['selectedParentId'] = 'nullable|exists:economic_codes,id';
      $validationRules['selectedSubHeadId'] = 'nullable|exists:economic_codes,id';
    }

    $this->validate($validationRules);

    // Custom Hierarchy Validation
    if (!$this->isUsed) {
      $length = strlen($this->code);
      if ($length > 2 && empty($this->selectedParentId)) {
        $this->addError('selectedParentId', __('First Stage is required for codes longer than 2 digits.'));
        return;
      }
      if ($length > 4 && empty($this->selectedSubHeadId)) {
        $this->addError('selectedSubHeadId', __('Second Stage is required for codes longer than 4 digits.'));
        return;
      }
    }

    $data = [
      'name' => $this->name,
      'description' => $this->description,
    ];

    if (!$this->isUsed) {
      $data['code'] = $this->code;
      $data['parent_id'] = $this->selectedSubHeadId ?: ($this->selectedParentId ?: null);
    }

    EconomicCode::updateOrCreate(['id' => $this->economic_code_id], $data);

    session()->flash(
      'message',
      $this->economic_code_id ? __('Economic Code Updated Successfully.') : __('Economic Code Created Successfully.')
    );

    $this->closeModal();
    $this->resetInputFields();
  }

  public function edit($id)
  {
    abort_if(auth()->user()->cannot('edit-economic-codes'), 403);
    $code = EconomicCode::with('parent.parent')->findOrFail($id);
    $this->economic_code_id = $id;
    $this->name = $code->name;
    $this->code = $code->code;
    $this->description = $code->description;
    $this->isUsed = $code->isUsed();

    if ($code->parent) {
      if ($code->parent->parent_id) {
        $this->selectedParentId = $code->parent->parent_id;
        $this->selectedSubHeadId = $code->parent_id;
      } else {
        $this->selectedParentId = $code->parent_id;
        $this->selectedSubHeadId = '';
      }
    } else {
      $this->selectedParentId = '';
      $this->selectedSubHeadId = '';
    }

    $this->openModal();
    $this->dispatch('select2-reinit');
  }

  public function delete($id)
  {
    abort_if(auth()->user()->cannot('delete-economic-codes'), 403);
    $this->dispatch('delete-confirmation', $id);
  }

  public function deleteConfirmed($id)
  {
    abort_if(auth()->user()->cannot('delete-economic-codes'), 403);
    if (is_array($id)) {
      $id = $id['id'] ?? $id[0];
    }

    $code = EconomicCode::find($id);
    if ($code && $code->isUsed()) {
      session()->flash('error', __('This Economic Code is in use and cannot be deleted.'));
      return;
    }

    if ($code) {
      $code->delete();
      session()->flash('message', __('Economic Code Deleted Successfully.'));
    }
  }
}
