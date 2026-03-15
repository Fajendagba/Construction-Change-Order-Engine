<?php

declare(strict_types=1);

namespace App\Domain\ProjectBudget\Contracts;

interface BudgetRepositoryInterface
{
    public function findProjectById(string $projectId): ?object;

    public function findLineItemByProjectAndCostCode(string $projectId, string $costCode): ?object;

    public function updateProjectBudgetTotals(string $projectId, float $changeAmount): void;

    public function updateLineItemApprovedChanges(string $projectId, string $costCode, float $changeAmount): void;
}
