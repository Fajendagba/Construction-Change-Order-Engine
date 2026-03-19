<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ProjectBudget\Services\BudgetRecalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpdateBudgetOnChangeOrderApproval implements ShouldQueue
{
    public string $queue = 'budget-updates';

    public function __construct(
        private readonly BudgetRecalculationService $budgetRecalculationService,
    ) {
    }

    public function handle(ChangeOrderApproved $event): void
    {
        $this->budgetRecalculationService->applyApprovedChange(
            $event->projectId,
            $event->costCode,
            $event->totalCost,
        );
    }
}
