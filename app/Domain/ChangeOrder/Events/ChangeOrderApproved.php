<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Events;

final class ChangeOrderApproved
{
    public function __construct(
        public readonly string $changeOrderId,
        public readonly string $projectId,
        public readonly string $reviewedBy,
        public readonly float $totalCost,
        public readonly string $costCode,
    ) {
    }
}
