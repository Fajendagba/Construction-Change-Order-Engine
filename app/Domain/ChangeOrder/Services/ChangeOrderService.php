<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Services;

use App\Domain\ChangeOrder\Contracts\ChangeOrderRepositoryInterface;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ChangeOrder\Events\ChangeOrderRejected;
use App\Domain\ChangeOrder\Events\ChangeOrderSubmitted;
use App\Domain\ChangeOrder\Exceptions\InvalidStateTransitionException;

final class ChangeOrderService
{
    public function __construct(
        private readonly ChangeOrderRepositoryInterface $changeOrderRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(string $projectId, string $userId, array $data): object
    {
        $laborCost    = (float) $data['labor_cost'];
        $materialCost = (float) $data['material_cost'];

        $changeOrder                   = new \stdClass();
        $changeOrder->project_id       = $projectId;
        $changeOrder->submitted_by     = $userId;
        $changeOrder->reviewed_by      = null;
        $changeOrder->number           = $this->changeOrderRepository->getNextNumberForProject($projectId);
        $changeOrder->title            = (string) $data['title'];
        $changeOrder->description      = (string) $data['description'];
        $changeOrder->reason           = (string) $data['reason'];
        $changeOrder->cost_code        = (string) $data['cost_code'];
        $changeOrder->labor_cost       = $laborCost;
        $changeOrder->material_cost    = $materialCost;
        $changeOrder->total_cost       = $laborCost + $materialCost;
        $changeOrder->state            = ChangeOrderState::DRAFT->value;
        $changeOrder->rejection_reason = null;
        $changeOrder->state_changed_at = null;
        $changeOrder->reviewed_at      = null;

        $this->changeOrderRepository->save($changeOrder);

        return $changeOrder;
    }

    /**
     * @return array{changeOrder: object, event: ChangeOrderSubmitted|ChangeOrderApproved|ChangeOrderRejected|null}
     */
    public function transitionState(
        string $changeOrderId,
        ChangeOrderState $targetState,
        string $userId,
        ?string $rejectionReason = null,
    ): array {
        $changeOrder = $this->changeOrderRepository->findById($changeOrderId);

        if ($changeOrder === null) {
            throw new \InvalidArgumentException(
                "Change order {$changeOrderId} not found."
            );
        }

        /** @var \stdClass $changeOrder */

        /** @var string|ChangeOrderState $rawState */
        $rawState     = $changeOrder->state;
        $currentState = $rawState instanceof ChangeOrderState
            ? $rawState
            : ChangeOrderState::from($rawState);

        if (!$currentState->canTransitionTo($targetState)) {
            throw new InvalidStateTransitionException($currentState, $targetState);
        }

        $changeOrder->state            = $targetState->value;
        $changeOrder->state_changed_at = new \DateTimeImmutable();

        if ($targetState === ChangeOrderState::APPROVED || $targetState === ChangeOrderState::REJECTED) {
            $changeOrder->reviewed_by = $userId;
            $changeOrder->reviewed_at = new \DateTimeImmutable();
        }

        if ($targetState === ChangeOrderState::REJECTED) {
            $changeOrder->rejection_reason = $rejectionReason;
        }

        $this->changeOrderRepository->save($changeOrder);

        /** @var string $id */
        $id = $changeOrder->id;
        /** @var string $projectId */
        $projectId = $changeOrder->project_id;
        /** @var string $submittedBy */
        $submittedBy = $changeOrder->submitted_by;
        /** @var string $costCode */
        $costCode = $changeOrder->cost_code;

        $event = match ($targetState) {
            ChangeOrderState::SUBMITTED => new ChangeOrderSubmitted(
                changeOrderId: $id,
                projectId:     $projectId,
                submittedBy:   $submittedBy,
            ),
            ChangeOrderState::APPROVED => new ChangeOrderApproved(
                changeOrderId: $id,
                projectId:     $projectId,
                reviewedBy:    $userId,
                totalCost:     (float) $changeOrder->total_cost,
                costCode:      $costCode,
            ),
            ChangeOrderState::REJECTED => new ChangeOrderRejected(
                changeOrderId: $id,
                projectId:     $projectId,
                reviewedBy:    $userId,
                reason:        $rejectionReason ?? '',
            ),
            default => null,
        };

        return ['changeOrder' => $changeOrder, 'event' => $event];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $changeOrderId, array $data): object
    {
        $changeOrder = $this->changeOrderRepository->findById($changeOrderId);

        if ($changeOrder === null) {
            throw new \InvalidArgumentException(
                "Change order {$changeOrderId} not found."
            );
        }

        /** @var \stdClass $changeOrder */

        /** @var string|ChangeOrderState $rawState */
        $rawState     = $changeOrder->state;
        $currentState = $rawState instanceof ChangeOrderState
            ? $rawState
            : ChangeOrderState::from($rawState);

        if ($currentState !== ChangeOrderState::DRAFT) {
            throw new InvalidStateTransitionException($currentState, ChangeOrderState::DRAFT);
        }

        if (array_key_exists('title', $data)) {
            $changeOrder->title = (string) $data['title'];
        }

        if (array_key_exists('description', $data)) {
            $changeOrder->description = (string) $data['description'];
        }

        if (array_key_exists('reason', $data)) {
            $changeOrder->reason = (string) $data['reason'];
        }

        if (array_key_exists('cost_code', $data)) {
            $changeOrder->cost_code = (string) $data['cost_code'];
        }

        if (array_key_exists('labor_cost', $data) || array_key_exists('material_cost', $data)) {
            $laborCost              = (float) ($data['labor_cost'] ?? $changeOrder->labor_cost);
            $materialCost           = (float) ($data['material_cost'] ?? $changeOrder->material_cost);
            $changeOrder->labor_cost    = $laborCost;
            $changeOrder->material_cost = $materialCost;
            $changeOrder->total_cost    = $laborCost + $materialCost;
        }

        $this->changeOrderRepository->save($changeOrder);

        return $changeOrder;
    }
}
