<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserOfficeTransfer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'from_office_id',
        'to_office_id',
        'transfer_date',
        'remarks',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromOffice()
    {
        return $this->belongsTo(RpoUnit::class, 'from_office_id');
    }

    public function toOffice()
    {
        return $this->belongsTo(RpoUnit::class, 'to_office_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
