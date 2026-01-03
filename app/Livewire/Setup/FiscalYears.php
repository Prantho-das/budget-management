<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use App\Models\FiscalYear;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FiscalYears extends Component
{
    public $fiscal_years, $name, $fiscal_year_id;
    public $status = true;
    public $isOpen = false;

    public function render()
    {
        abort_if(auth()->user()->cannot('view-fiscal-years'), 403);
        $this->fiscal_years = FiscalYear::orderBy('id', 'desc')->get();
        return view('livewire.setup.fiscal-years')
            ->extends('layouts.skot')
            ->section('content');
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('create-fiscal-years'), 403);
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
        $this->status = true;
        $this->fiscal_year_id = '';
    }

    public function store()
    {
        $isUpdate = !empty($this->fiscal_year_id);

        if ($isUpdate) {
            abort_if(auth()->user()->cannot('edit-fiscal-years'), 403);
        } else {
            abort_if(auth()->user()->cannot('create-fiscal-years'), 403);
        }

        // Full validation on both create and edit
        $this->validate([
            'name' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}$/',
                Rule::unique('fiscal_years', 'name')->ignore($this->fiscal_year_id), // ignores current on edit
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^(\d{4})-(\d{2})$/', $value, $matches)) {
                        return; // already caught by regex
                    }

                    $startYear = (int)$matches[1];
                    $shortEndYear = (int)$matches[2];

                    if ($startYear < 1990 || $startYear > 2100) {
                        $fail('The start year must be between 1990 and 2100.');
                        return;
                    }

                    $expectedEndYear = $startYear + 1;
                    $expectedShort = $expectedEndYear % 100;

                    if ($shortEndYear !== $expectedShort) {
                        $fail("Fiscal year must be consecutive. Correct format: {$startYear}-" . sprintf('%02d', $expectedShort));
                    }
                },
            ],
            'status' => 'boolean',
        ]);

        // Extract years from name
        preg_match('/^(\d{4})-(\d{2})$/', $this->name, $matches);
        $startYear = (int)$matches[1];
        $endYear = $startYear + 1;

        // Bangladesh fiscal year: July 1 to June 30
        $start_date = Carbon::create($startYear, 7, 1)->format('Y-m-d');  // e.g., 2025-07-01
        $end_date   = Carbon::create($endYear, 6, 30)->format('Y-m-d');   // e.g., 2026-06-30

        FiscalYear::updateOrCreate(
            ['id' => $this->fiscal_year_id],
            [
                'name'       => $this->name,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'status'     => $this->status,
            ]
        );

        session()->flash(
            'message',
            $isUpdate
                ? __('Fiscal Year Updated Successfully.')
                : __('Fiscal Year Created Successfully.')
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(auth()->user()->cannot('edit-fiscal-years'), 403);

        $fiscalYear = FiscalYear::findOrFail($id);

        $this->fiscal_year_id = $id;
        $this->name = $fiscalYear->name;
        $this->status = (bool) $fiscalYear->status;

        $this->openModal();
    }

    protected $listeners = ['deleteConfirmed'];

    public function delete($id)
    {
        abort_if(auth()->user()->cannot('delete-fiscal-years'), 403);
        $this->dispatch('delete-confirmation', $id);
    }

    public function deleteConfirmed($id)
    {
        abort_if(auth()->user()->cannot('delete-fiscal-years'), 403);

        if (is_array($id)) {
            $id = $id['id'] ?? $id[0] ?? null;
        }

        if ($id) {
            FiscalYear::find($id)?->delete();
            session()->flash('message', __('Fiscal Year Deleted Successfully.'));
        }
    }
}