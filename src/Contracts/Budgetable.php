<?php

namespace Majeedfahad\BudgetManager\Contracts;

use Majeedfahad\BudgetManager\Models\Budget;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Budgetable {
    public function budget(): MorphOne;

    public function addBudget(float $amount): Budget;

    public function updateBudget(float $amount): Budget;
}
