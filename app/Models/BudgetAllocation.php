<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\EconomicCode;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BudgetAllocation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'fiscal_year_id',
        'budget_type_id',
        'rpo_unit_id',
        'economic_code_id',
        'amount',
        'remarks',
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
}
