<?php

namespace Majeedfahad\BudgetManager\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Budgetable {
    public function budget(): MorphOne;
}
