<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
        'description',
        'date',
        'expense_category_id',
        'rpo_unit_id',
        'fiscal_year_id',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function office()
    {
        return $this->belongsTo(RpoUnit::class, 'rpo_unit_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }
}
