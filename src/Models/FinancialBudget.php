<?php

namespace Majeedfahad\BudgetManager\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Majeedfahad\BudgetManager\Contracts\Budgetable;
use Majeedfahad\BudgetManager\Contracts\Expensable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialBudget extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['remainingAmount'];

    public function budgetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function financialExpenses(): HasMany
    {
        return $this->hasMany(FinancialExpense::class, 'financial_budget_id');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->budget - $this->getExpenses();
    }

    public function updateBudget(float $budget): bool
    {
        if($budget < $this->getExpenses()) {
            throw new \Exception("New budget is lower than expensed");
        }

        return $this->update(['budget' => $budget]);
    }

    public function canAddExpense(float $amount): bool
    {
        return $amount <= $this->remainingAmount;
    }

    public function getExpenses()
    {
        $total = 0;

        // We get the expense of children first and assign it to the total
        if($this->children) {
            foreach ($this->children as $child) {
                $total += $child->getExpenses();
            }
        }

        if(! $this->financialExpenses->isEmpty()) {
            return $this->financialExpenses->pluck('amount')->sum();
        }

        return $total;
    }

    public function addChildren(Budgetable $obj, $budget = 0)
    {
        if(!$this->canAddExpense($budget)) {
            throw new \Exception("لا يمكن اضافة هذا المبلغ ($budget)");
        }

        return $obj->financialBudget()->create([
            'budget' => $budget,
            'parent_id' => $this->id,
        ]);
    }

    public function addExpense(Expensable $obj, $amount = 0)
    {
        if(!$this->canAddExpense($amount)) {
            throw new \Exception("لا يمكن اضافة هذا المبلغ ($amount)");
        }

        return $obj->expense()->create([
            'amount' => $amount,
            'financial_budget_id' => $this->id,
        ]);
    }

    public function getPercentage()
    {
        $parentAmount = $this->parent->budget;
        $percentage = $this->budget / $parentAmount * 100;

        return round($percentage, 2);
    }
}
