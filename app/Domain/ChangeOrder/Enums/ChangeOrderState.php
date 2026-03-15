<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Enums;

enum ChangeOrderState: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * @return array<int, ChangeOrderState>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT       => [self::SUBMITTED],
            self::SUBMITTED   => [self::UNDER_REVIEW],
            self::UNDER_REVIEW => [self::APPROVED, self::REJECTED],
            self::APPROVED    => [],
            self::REJECTED    => [self::DRAFT],
        };
    }

    public function canTransitionTo(ChangeOrderState $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    public function isFinal(): bool
    {
        return $this === self::APPROVED;
    }
}
