<?php

declare(strict_types=1);

namespace App\Application\Policies;

use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\Shared\Enums\UserRole;
use App\Models\User;

final class ChangeOrderPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::CONTRACTOR;
    }

    public function transition(User $user, ChangeOrder $changeOrder, string $targetState): bool
    {
        return match ($user->role) {
            UserRole::CONTRACTOR => $targetState === 'submitted',
            UserRole::OWNER      => in_array($targetState, ['under_review', 'approved', 'rejected'], strict: true),
            default              => false,
        };
    }
}
