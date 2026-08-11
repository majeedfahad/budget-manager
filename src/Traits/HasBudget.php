<?php

namespace Majeedfahad\BudgetManager\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\BudgetManager\Models\Budget;

/**
 * Trait HasBudget
 * 
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasBudget
{
    public function budget(): MorphOne
    {
        return $this->morphOne(Budget::class, 'budgetable');
    }

    public function addBudget(float $amount): Budget
    {
        if ($this->budget) {
            throw new \Exception('Budget already exists for this model.');
        }

        return $this->budget()->create([
            'amount' => $amount,
        ]);
    }

    public function updateBudget(float $amount): Budget
    {
        if (!$this->budget) {
            throw new \Exception('No budget exists for this model.');
        }

        $this->budget->updateBudget($amount);

        return $this->budget;
    }
}
