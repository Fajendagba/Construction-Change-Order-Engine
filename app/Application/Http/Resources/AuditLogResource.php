<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use App\Domain\AuditLog\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AuditLog $log */
        $log = $this->resource;

        return [
            'id'         => $log->id,
            'action'     => $log->action,
            'from_state' => $log->from_state,
            'to_state'   => $log->to_state,
            'user'       => $this->whenLoaded('user', fn () => $log->user !== null
                ? ['id' => $log->user->id, 'name' => $log->user->name]
                : null),
            'metadata'   => $log->metadata,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }
}
