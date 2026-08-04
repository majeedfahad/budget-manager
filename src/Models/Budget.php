<?php

namespace Majeedfahad\BudgetManager\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Majeedfahad\BudgetManager\Contracts\Budgetable;
use Majeedfahad\BudgetManager\Contracts\Expensable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Majeedfahad\BudgetManager\Exceptions\BudgetNotAllowedException;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Budget extends Model
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

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'budget_id');
    }

    public function remainingExpensedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->amount - $this->getExpenses(),
        );
    }

    public function remainingAllocatedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->amount - $this->getAllocatedAmount(),
        );
    }

    public function canAddChild(float $amount): bool
    {
        return $amount <= $this->remaining_allocated_amount;
    }

    public function canAddExpense(float $amount): bool
    {
        return $amount <= $this->remaining_expensed_amount;
    }

    public function canUpdateChild(Budget $child, float $budget): bool
    {
        $allocatedExcludingChild = $this->getAllocatedAmount() - (float) $child->amount;

        return $budget <= (float) $this->amount - $allocatedExcludingChild;
    }

    public function getChild(Budgetable $budgetable): ?self
    {
        return $this->children()
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
        $budgetIds = $this->descendantsAndSelf()->pluck('id');

        return (float) Expense::whereIn('budget_id', $budgetIds)->sum('amount');
    }

    public function getAllocatedAmount(): float
    {
        return (float) $this->children()->sum('amount');
    }

    public function addChild(Budgetable $obj, float $budget = 0): Budget
    {
        return DB::transaction(function () use ($obj, $budget) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if (!$locked->canAddChild($budget)) {
                throw new BudgetNotAllowedException("Budget $budget is greater than remaining allocated amount.");
            }

            return $obj->budget()->create([
                'amount' => $budget,
                'parent_id' => $locked->id,
            ]);
        });
    }

    public function addExpense(Expensable $obj, float $amount = 0): Expense
    {
        return DB::transaction(function () use ($obj, $amount) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if (!$locked->canAddExpense($amount)) {
                throw new BudgetNotAllowedException("Budget $amount is greater than remaining expensed amount");
            }

            return $obj->expense()->create([
                'amount' => $amount,
                'budget_id' => $locked->id,
            ]);
        });
    }

    public function getPercentage(): float
    {
        $parentAmount = (float) ($this->parent?->amount ?? 0);

        if ($parentAmount == 0) {
            return 0.0;
        }

        return round((float) $this->amount / $parentAmount * 100, 2);
    }
}
