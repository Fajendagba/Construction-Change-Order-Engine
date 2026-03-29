<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum UserRole: string
{
    case OWNER      = 'owner';
    case CONTRACTOR = 'contractor';
    case ARCHITECT  = 'architect';
}
