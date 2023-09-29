<?php


use Exception;

class CannotAddBudgetException extends Exception
{
    public function __construct($message = "Cannot add budget to this model.")
    {
        parent::__construct($message);
    }
}
