<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    const STATUS_DRAFT = 'Draft';
    const STATUS_APPROVED = 'Approved';

    protected $fillable = [
        'code',
        'amount',
        'description',
        'date',
        'economic_code_id',
        'budget_type_id',
        'rpo_unit_id',
        'fiscal_year_id',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    public function office()
    {
        return $this->belongsTo(RpoUnit::class, 'rpo_unit_id');
    }

    public function economicCode()
    {
        return $this->belongsTo(EconomicCode::class, 'economic_code_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function budgetType()
    {
        return $this->belongsTo(BudgetType::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
