<?php

declare(strict_types=1);

namespace App\Domain\ProjectBudget\Services;

use App\Domain\ProjectBudget\Contracts\BudgetRepositoryInterface;

final class BudgetRecalculationService
{
    public function __construct(
        private readonly BudgetRepositoryInterface $budgetRepository,
    ) {
    }

    public function applyApprovedChange(string $projectId, string $costCode, float $amount): void
    {
        $this->budgetRepository->updateLineItemApprovedChanges($projectId, $costCode, $amount);
        $this->budgetRepository->updateProjectBudgetTotals($projectId, $amount);
    }
}
