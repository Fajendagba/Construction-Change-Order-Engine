<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Contracts;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;

interface ChangeOrderRepositoryInterface
{
    public function findById(string $id): ?object;

    /**
     * @return list<object>
     */
    public function findByProject(string $projectId): array;

    public function save(object $changeOrder): void;

    public function getNextNumberForProject(string $projectId): int;

    public function countByProjectAndState(string $projectId, ChangeOrderState $state): int;
}
