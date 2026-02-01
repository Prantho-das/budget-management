<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinistryBudgetMaster extends Model
{
    protected $guarded = [];

    public function allocations()
    {
        return $this->hasMany(MinistryAllocation::class, 'ministry_budget_master_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function rpoUnit()
    {
        return $this->belongsTo(RpoUnit::class);
    }

    public function budgetType()
    {
        return $this->belongsTo(BudgetType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
