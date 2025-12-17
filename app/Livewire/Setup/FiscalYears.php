<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\FiscalYear;

class FiscalYears extends Component
{
    public $fiscal_years, $name, $start_date, $end_date, $fiscal_year_id;
    public $status = true; // Default active
    public $isOpen = false;

    public function render()
    {
        $this->fiscal_years = FiscalYear::orderBy('id', 'desc')->get();
        return view('livewire.setup.fiscal-years')
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
        $this->start_date = '';
        $this->end_date = '';
        $this->status = true;
        $this->fiscal_year_id = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'boolean'
        ]);

        FiscalYear::updateOrCreate(['id' => $this->fiscal_year_id], [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status
        ]);

        session()->flash(
            'message',
            $this->fiscal_year_id ? 'Fiscal Year Updated Successfully.' : 'Fiscal Year Created Successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $fiscalYear = FiscalYear::findOrFail($id);
        $this->fiscal_year_id = $id;
        $this->name = $fiscalYear->name;
        $this->start_date = $fiscalYear->start_date;
        $this->end_date = $fiscalYear->end_date;
        $this->status = (bool) $fiscalYear->status;

        $this->openModal();
    }

    protected $listeners = ['deleteConfirmed'];

    public function delete($id)
    {
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {

        if (is_array($id)) {
            $id = $id['id'] ?? $id[0];
        }
        FiscalYear::find($id)->delete();
        session()->flash('message', 'Fiscal Year Deleted Successfully.');
    }
}
