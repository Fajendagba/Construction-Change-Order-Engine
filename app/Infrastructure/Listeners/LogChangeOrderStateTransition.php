<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ChangeOrder\Events\ChangeOrderRejected;
use App\Domain\ChangeOrder\Events\ChangeOrderSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

final class LogChangeOrderStateTransition implements ShouldQueue
{
    public string $queue = 'audit';

    public function handle(ChangeOrderSubmitted|ChangeOrderApproved|ChangeOrderRejected $event): void
    {
        if ($event instanceof ChangeOrderSubmitted) {
            $this->logSubmitted($event);
        } elseif ($event instanceof ChangeOrderApproved) {
            $this->logApproved($event);
        } else {
            $this->logRejected($event);
        }
    }

    private function logSubmitted(ChangeOrderSubmitted $event): void
    {
        AuditLog::create([
            'change_order_id' => $event->changeOrderId,
            'user_id'         => $event->submittedBy,
            'action'          => 'submitted',
            'from_state'      => 'draft',
            'to_state'        => 'submitted',
            'metadata'        => null,
        ]);
    }

    private function logApproved(ChangeOrderApproved $event): void
    {
        AuditLog::create([
            'change_order_id' => $event->changeOrderId,
            'user_id'         => $event->reviewedBy,
            'action'          => 'approved',
            'from_state'      => 'under_review',
            'to_state'        => 'approved',
            'metadata'        => ['total_cost' => $event->totalCost, 'cost_code' => $event->costCode],
        ]);
    }

    private function logRejected(ChangeOrderRejected $event): void
    {
        AuditLog::create([
            'change_order_id' => $event->changeOrderId,
            'user_id'         => $event->reviewedBy,
            'action'          => 'rejected',
            'from_state'      => 'under_review',
            'to_state'        => 'rejected',
            'metadata'        => ['reason' => $event->reason],
        ]);
    }
}
