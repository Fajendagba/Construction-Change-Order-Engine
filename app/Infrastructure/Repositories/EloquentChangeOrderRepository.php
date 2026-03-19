<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\ChangeOrder\Contracts\ChangeOrderRepositoryInterface;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;

final class EloquentChangeOrderRepository implements ChangeOrderRepositoryInterface
{
    public function findById(string $id): ?object
    {
        return ChangeOrder::find($id);
    }

    /**
     * @return list<object>
     */
    public function findByProject(string $projectId): array
    {
        return array_values(
            ChangeOrder::where('project_id', $projectId)
                ->orderBy('number')
                ->get()
                ->all()
        );
    }

    public function save(object $changeOrder): void
    {
        if ($changeOrder instanceof ChangeOrder) {
            $changeOrder->save();
            return;
        }

        /** @var \stdClass $changeOrder */
        $attributes         = get_object_vars($changeOrder);
        $model              = ChangeOrder::create($attributes);
        $changeOrder->id    = $model->id;
    }

    public function getNextNumberForProject(string $projectId): int
    {
        return (ChangeOrder::where('project_id', $projectId)->max('number') ?? 0) + 1;
    }

    public function countByProjectAndState(string $projectId, ChangeOrderState $state): int
    {
        return ChangeOrder::where('project_id', $projectId)
            ->where('state', $state->value)
            ->count();
    }
}
