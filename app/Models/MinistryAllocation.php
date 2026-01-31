<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinistryAllocation extends Model
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

    public function economicCode()
    {
        return $this->belongsTo(EconomicCode::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
