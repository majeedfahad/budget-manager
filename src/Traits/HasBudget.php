<?php

namespace Majeedfahad\BudgetManager\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\BudgetManager\Models\Budget;

trait HasBudget
{
    public function budget(): MorphOne
    {
        return $this->morphOne(Budget::class, 'budgetable');
    }
}
