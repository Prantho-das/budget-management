<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetEstimation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year_id',
        'rpo_unit_id',
        'economic_code_id',
        'amount_demand',
        'amount_approved',
        'status',
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
