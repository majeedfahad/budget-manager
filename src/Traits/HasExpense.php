<?php

namespace Majeedfahad\BudgetManager\Traits;

use Majeedfahad\BudgetManager\Models\Expense;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasExpense
{
    public function expense(): MorphOne
    {
        return $this->morphOne(Expense::class, 'expensable');
    }
}
