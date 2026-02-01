<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinistryAllocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function master()
    {
        return $this->belongsTo(MinistryBudgetMaster::class, 'ministry_budget_master_id');
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
