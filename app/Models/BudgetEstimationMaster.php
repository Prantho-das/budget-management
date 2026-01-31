<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetEstimationMaster extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function allocations()
    {
        return $this->hasMany(BudgetEstimation::class, 'budget_estimation_master_id');
    }

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
