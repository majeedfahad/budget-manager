<?php

namespace Majeedfahad\BudgetManager\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BudgetNotAllowedException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct(
            $message ?? __('budget-manager::messages.budget_not_allowed'),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public function render(): JsonResponse
    {
        return new JsonResponse(
            ['message' => $this->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
