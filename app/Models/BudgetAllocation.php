<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\EconomicCode;

class BudgetAllocation extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'budget_type',
        'rpo_unit_id',
        'economic_code_id',
        'amount',
        'remarks',
    ];

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
