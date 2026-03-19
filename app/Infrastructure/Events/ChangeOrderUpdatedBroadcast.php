<?php

declare(strict_types=1);

namespace App\Infrastructure\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

final class ChangeOrderUpdatedBroadcast implements ShouldBroadcast
{
    /** @param array<string, mixed> $details */
    public function __construct(
        public readonly string $changeOrderId,
        public readonly string $projectId,
        public readonly string $newState,
        public readonly array $details,
    ) {
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('project.' . $this->projectId)];
    }

    public function broadcastAs(): string
    {
        return 'change-order.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'change_order_id' => $this->changeOrderId,
            'new_state'       => $this->newState,
            'details'         => $this->details,
        ];
    }
}
