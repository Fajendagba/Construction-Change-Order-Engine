<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\ProjectBudget\Contracts\BudgetRepositoryInterface;
use App\Domain\ProjectBudget\Models\BudgetLineItem;
use App\Domain\ProjectBudget\Models\Project;
use Illuminate\Support\Facades\DB;

final class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function findProjectById(string $projectId): ?object
    {
        return Project::find($projectId);
    }

    public function findLineItemByProjectAndCostCode(string $projectId, string $costCode): ?object
    {
        return BudgetLineItem::where('project_id', $projectId)
            ->where('cost_code', $costCode)
            ->first();
    }

    public function updateProjectBudgetTotals(string $projectId, float $changeAmount): void
    {
        DB::transaction(function () use ($projectId, $changeAmount): void {
            /** @var Project|null $project */
            $project = Project::query()->lockForUpdate()->find($projectId);

            if ($project === null) {
                return;
            }

            $approvedTotal                   = $project->approved_changes_total + $changeAmount;
            $project->approved_changes_total = $approvedTotal;
            $project->current_budget         = $project->original_budget + $approvedTotal;
            $project->save();
        });
    }

    public function updateLineItemApprovedChanges(string $projectId, string $costCode, float $changeAmount): void
    {
        DB::transaction(function () use ($projectId, $costCode, $changeAmount): void {
            /** @var BudgetLineItem|null $lineItem */
            $lineItem = BudgetLineItem::where('project_id', $projectId)
                ->where('cost_code', $costCode)
                ->lockForUpdate()
                ->first();

            if ($lineItem === null) {
                BudgetLineItem::create([
                    'project_id'       => $projectId,
                    'cost_code'        => $costCode,
                    'description'      => $costCode,
                    'original_amount'  => 0,
                    'approved_changes' => $changeAmount,
                    'current_amount'   => $changeAmount,
                ]);
                return;
            }

            $approved                   = $lineItem->approved_changes + $changeAmount;
            $lineItem->approved_changes = $approved;
            $lineItem->current_amount   = $lineItem->original_amount + $approved;
            $lineItem->save();
        });
    }
}
