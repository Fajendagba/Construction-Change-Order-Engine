<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Exceptions;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;

final class InvalidStateTransitionException extends \RuntimeException
{
    public function __construct(ChangeOrderState $from, ChangeOrderState $to)
    {
        parent::__construct(
            "Cannot transition change order from {$from->value} to {$to->value}"
        );
    }
}
