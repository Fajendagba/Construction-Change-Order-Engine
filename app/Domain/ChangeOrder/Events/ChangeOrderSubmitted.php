<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Events;

final class ChangeOrderSubmitted
{
    public function __construct(
        public readonly string $changeOrderId,
        public readonly string $projectId,
        public readonly string $submittedBy,
    ) {
    }
}
