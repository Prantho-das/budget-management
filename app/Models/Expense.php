<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'amount',
        'description',
        'date',
        'economic_code_id',
        'budget_type_id',
        'rpo_unit_id',
        'fiscal_year_id',
    ];

    public function budgetType()
    {
        return $this->belongsTo(BudgetType::class);
    }

    public function economicCode()
    {
        return $this->belongsTo(EconomicCode::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function office()
    {
        return $this->belongsTo(RpoUnit::class, 'rpo_unit_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }
}
