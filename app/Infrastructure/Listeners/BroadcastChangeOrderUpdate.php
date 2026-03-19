<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ChangeOrder\Events\ChangeOrderRejected;
use App\Domain\ChangeOrder\Events\ChangeOrderSubmitted;
use App\Infrastructure\Events\ChangeOrderUpdatedBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

final class BroadcastChangeOrderUpdate implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(ChangeOrderSubmitted|ChangeOrderApproved|ChangeOrderRejected $event): void
    {
        if ($event instanceof ChangeOrderSubmitted) {
            $this->broadcastSubmitted($event);
        } elseif ($event instanceof ChangeOrderApproved) {
            $this->broadcastApproved($event);
        } else {
            $this->broadcastRejected($event);
        }
    }

    private function broadcastSubmitted(ChangeOrderSubmitted $event): void
    {
        broadcast(new ChangeOrderUpdatedBroadcast(
            changeOrderId: $event->changeOrderId,
            projectId:     $event->projectId,
            newState:      'submitted',
            details:       ['submitted_by' => $event->submittedBy],
        ))->toOthers();
    }

    private function broadcastApproved(ChangeOrderApproved $event): void
    {
        broadcast(new ChangeOrderUpdatedBroadcast(
            changeOrderId: $event->changeOrderId,
            projectId:     $event->projectId,
            newState:      'approved',
            details:       ['reviewed_by' => $event->reviewedBy, 'total_cost' => $event->totalCost],
        ))->toOthers();
    }

    private function broadcastRejected(ChangeOrderRejected $event): void
    {
        broadcast(new ChangeOrderUpdatedBroadcast(
            changeOrderId: $event->changeOrderId,
            projectId:     $event->projectId,
            newState:      'rejected',
            details:       ['reviewed_by' => $event->reviewedBy, 'reason' => $event->reason],
        ))->toOthers();
    }
}
