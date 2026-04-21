<?php

declare(strict_types=1);

namespace App\Application\Policies;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\Shared\Enums\UserRole;
use App\Models\User;

final class ChangeOrderPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::CONTRACTOR;
    }

    public function edit(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->role === UserRole::CONTRACTOR
            && $changeOrder->submitted_by === $user->id
            && $changeOrder->state === ChangeOrderState::DRAFT;
    }

    public function transition(User $user, ChangeOrder $changeOrder, string $targetState): bool
    {
        return match ($user->role) {
            UserRole::CONTRACTOR => in_array($targetState, ['submitted', 'draft'], strict: true),
            UserRole::OWNER      => in_array($targetState, ['under_review', 'approved', 'rejected'], strict: true),
            default              => false,
        };
    }
}
