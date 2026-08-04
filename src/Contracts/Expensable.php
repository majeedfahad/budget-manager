<?php

namespace Majeedfahad\BudgetManager\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Expensable {
    public function expense(): MorphOne;
}
