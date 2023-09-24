<?php

namespace Majeedfahad\BudgetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialExpense extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function expensable()
    {
        return $this->morphTo();
    }

    public function budget()
    {
        return $this->belongsTo(FinancialBudget::class, 'financial_budget_id');
    }
}
