<?php

namespace App\Livewire\Setup;

use Livewire\Component;

use App\Models\MinistryBudgetMaster;
use Livewire\WithPagination;

class MinistryBudgetList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function delete($id)
    {
        $master = MinistryBudgetMaster::findOrFail($id);
        $master->allocations()->delete();
        $master->delete();
        session()->flash('message', __('Ministry Budget deleted successfully.'));
    }

    public function render()
    {
        $budgets = MinistryBudgetMaster::with(['fiscalYear', 'rpoUnit', 'budgetType'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.setup.ministry-budget-list', [
            'budgets' => $budgets
        ])
            ->extends('layouts.skot')
            ->section('content');
    }
}
