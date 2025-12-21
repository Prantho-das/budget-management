<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = ['name', 'required_permission', 'order', 'office_level', 'is_active'];
}
