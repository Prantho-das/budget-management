<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;

class EconomicCode extends Model

{
    use HasFactory, LogsActivity;

    protected $fillable = ['code', 'name', 'description', 'parent_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function parent()
    {
        return $this->belongsTo(EconomicCode::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(EconomicCode::class, 'parent_id')->orderBy('code', 'asc');
    }

    public function budgetEstimations()
    {
        return $this->hasMany(BudgetEstimation::class);
    }

    public function budgetAllocations()
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function isUsed()
    {
        return $this->children()->exists() ||
            $this->budgetEstimations()->exists() ||
            $this->budgetAllocations()->exists() ||
            $this->expenses()->exists();
    }
}
