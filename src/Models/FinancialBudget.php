<?php

namespace Majeedfahad\BudgetManager\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Majeedfahad\BudgetManager\Contracts\Budgetable;
use Majeedfahad\BudgetManager\Contracts\Expensable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Majeedfahad\BudgetManager\Exceptions\BudgetNotAllowedException;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class FinancialBudget extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $guarded = [];
    protected $appends = ['remainingExpensedAmount'];

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

    public function getRemainingExpensedAmountAttribute()
    {
        return $this->budget - $this->getExpenses();
    }

    public function getRemainingAllocatedAmountAttribute()
    {
        return $this->budget - $this->getAllocatedAmount();
    }

    public function canAddChild(float $amount): bool
    {
        return $amount <= $this->remainingAllocatedAmount;
    }

    public function canAddExpense(float $amount): bool
    {
        return $amount <= $this->remainingExpensedAmount;
    }

    public function canUpdateChild(FinancialBudget $child, $budget): bool
    {
        $this->children->firstWhere('id', $child->id)->budget = 0;

        return $this->canAddChild($budget);
    }

    public function getChild(Budgetable $budgetable)
    {
        return $this->children
            ->where('budgetable_id', $budgetable->id)
            ->where('budgetable_type', get_class($budgetable))
            ->first();
    }

    public function updateBudget(float $budget): bool
    {
        if($budget < $this->getExpenses()) {
            throw new \Exception("New budget is lower than expensed");
        }

        if($this->parent && !$this->parent->canUpdateChild($this, $budget)) {
            throw new BudgetNotAllowedException("لا يمكن اضافة هذا المبلغ ($budget)");
        }

        return $this->update(['budget' => $budget]);
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

    public function getAllocatedAmount()
    {
        return $this->children->pluck('budget')->sum();
    }

    public function addChild(Budgetable $obj, $budget = 0)
    {
        if(!$this->canAddChild($budget)) {
            throw new BudgetNotAllowedException("Budget $budget is greater than remaining allocated amount.");
        }

        return $obj->financialBudget()->create([
            'budget' => $budget,
            'parent_id' => $this->id,
        ]);
    }

    public function addExpense(Expensable $obj, $amount = 0)
    {
        if(!$this->canAddExpense($amount)) {
            throw new BudgetNotAllowedException("Budget $amount is greater than remaining expensed amount");
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
