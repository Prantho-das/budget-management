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
        return $this->hasMany(EconomicCode::class, 'parent_id');
    }
}
