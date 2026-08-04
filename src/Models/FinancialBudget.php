<?php

namespace Majeedfahad\BudgetManager\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Majeedfahad\BudgetManager\Contracts\Budgetable;
use Majeedfahad\BudgetManager\Contracts\Expensable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Majeedfahad\BudgetManager\Exceptions\BudgetNotAllowedException;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class FinancialBudget extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'parent_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->currency ??= config('budget-manager.default_currency');
        });
    }

    public function budgetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function financialExpenses(): HasMany
    {
        return $this->hasMany(FinancialExpense::class, 'financial_budget_id');
    }

    public function getRemainingExpensedAmountAttribute(): float
    {
        return (float) $this->amount - $this->getExpenses();
    }

    public function getRemainingAllocatedAmountAttribute(): float
    {
        return (float) $this->amount - $this->getAllocatedAmount();
    }

    public function canAddChild(float $amount): bool
    {
        return $amount <= $this->remainingAllocatedAmount;
    }

    public function canAddExpense(float $amount): bool
    {
        return $amount <= $this->remainingExpensedAmount;
    }

    public function canUpdateChild(FinancialBudget $child, float $budget): bool
    {
        $allocatedExcludingChild = $this->getAllocatedAmount() - (float) $child->amount;

        return $budget <= (float) $this->amount - $allocatedExcludingChild;
    }

    public function getChild(Budgetable $budgetable): ?self
    {
        return $this->children
            ->where('budgetable_id', $budgetable->id)
            ->where('budgetable_type', get_class($budgetable))
            ->first();
    }

    public function updateBudget(float $budget): bool
    {
        if ($budget < $this->getExpenses()) {
            throw new BudgetNotAllowedException("Budget $budget is less than the total expenses already recorded.");
        }

        if ($this->parent && !$this->parent->canUpdateChild($this, $budget)) {
            throw new BudgetNotAllowedException("Budget $budget exceeds the amount allowed by the parent budget.");
        }

        if ($budget < $this->getAllocatedAmount()) {
            throw new BudgetNotAllowedException("Budget $budget is less than the amount already allocated to children.");
        }

        return $this->update(['amount' => $budget]);
    }

    public function getExpenses(): float
    {
        return (float) $this->financialExpenses->sum('amount')
            + $this->children->sum(fn (self $child) => $child->getExpenses());
    }

    public function getAllocatedAmount(): float
    {
        return (float) $this->children->sum('amount');
    }

    public function addChild(Budgetable $obj, float $budget = 0): FinancialBudget
    {
        if (!$this->canAddChild($budget)) {
            throw new BudgetNotAllowedException("Budget $budget is greater than remaining allocated amount.");
        }

        return DB::transaction(fn () => $obj->financialBudget()->create([
            'amount' => $budget,
            'parent_id' => $this->id,
        ]));
    }

    public function addExpense(Expensable $obj, float $amount = 0): FinancialExpense
    {
        if (!$this->canAddExpense($amount)) {
            throw new BudgetNotAllowedException("Budget $amount is greater than remaining expensed amount");
        }

        return DB::transaction(fn () => $obj->expense()->create([
            'amount' => $amount,
            'financial_budget_id' => $this->id,
        ]));
    }

    public function getPercentage(): float
    {
        $parentAmount = (float) ($this->parent?->amount ?? 0);

        if ($parentAmount == 0) {
            return 0;
        }

        return round((float) $this->amount / $parentAmount * 100, 2);
    }
}
