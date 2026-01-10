<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;

class BudgetEstimation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fiscal_year_id',
        'budget_type_id',
        'rpo_unit_id',
        'economic_code_id',
        'amount_demand',
        'amount_approved',
        'status',
        'current_stage',
        'workflow_step_id',
        'target_office_id',
        'approval_log',
        'remarks',
        'approver_remarks',
        'batch_id',
        'revised_amount',
        'projection_1',
        'projection_2',
    ];

    protected $casts = [
        'approval_log' => 'json',
    ];

    public function budgetType()
    {
        return $this->belongsTo(BudgetType::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function office()
    {
        return $this->belongsTo(RpoUnit::class, 'rpo_unit_id');
    }

    public function economicCode()
    {
        return $this->belongsTo(EconomicCode::class);
    }

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function targetOffice()
    {
        return $this->belongsTo(RpoUnit::class, 'target_office_id');
    }
}
