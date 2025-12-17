<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RpoUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'parent_id',
        'district',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(RpoUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RpoUnit::class, 'parent_id');
    }
}
