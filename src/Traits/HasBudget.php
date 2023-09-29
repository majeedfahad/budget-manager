<?php

namespace Majeedfahad\BudgetManager\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\BudgetManager\Models\FinancialBudget;

trait HasBudget
{
    public function financialBudget(): MorphOne
    {
        return $this->morphOne(FinancialBudget::class, 'budgetable');
    }

    public function getFinancialBudgetName(): string|null
    {
        return $this->name ?? null;
    }
}
