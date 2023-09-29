<?php

namespace Majeedfahad\BudgetManager\Traits;

use Majeedfahad\BudgetManager\Models\FinancialExpense;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasExpense
{
    public function expense(): MorphOne
    {
        return $this->morphOne(FinancialExpense::class, 'expensable');
    }
}
