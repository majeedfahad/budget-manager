<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Budget Manager Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the budget manager for the
    | messages attached to the exceptions it throws. You are free to change
    | them after publishing the package translations.
    |
    */

    'budget_not_allowed' => 'Cannot add budget to this model.',
    'budget_already_exists' => 'Budget already exists for this model.',
    'budget_missing' => 'No budget exists for this model.',
    'not_a_child_budget' => 'The provided budget is not a child of this budget.',
    'below_recorded_expenses' => 'Budget :amount is less than the total expenses already recorded.',
    'exceeds_parent_budget' => 'Budget :amount exceeds the amount allowed by the parent budget.',
    'below_allocated_amount' => 'Budget :amount is less than the amount already allocated to children.',
    'exceeds_remaining_allocated' => 'Budget :amount is greater than remaining allocated amount.',
    'exceeds_remaining_expensed' => 'Budget :amount is greater than remaining expensed amount.',

];
