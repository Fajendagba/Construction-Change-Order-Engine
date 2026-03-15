<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ChangeOrder;

use App\Domain\ChangeOrder\Contracts\ChangeOrderRepositoryInterface;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ChangeOrder\Events\ChangeOrderRejected;
use App\Domain\ChangeOrder\Events\ChangeOrderSubmitted;
use App\Domain\ChangeOrder\Exceptions\InvalidStateTransitionException;
use App\Domain\ChangeOrder\Services\ChangeOrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ChangeOrderServiceTest extends TestCase
{
    /** @var ChangeOrderRepositoryInterface&MockObject */
    private ChangeOrderRepositoryInterface $repository;
    private ChangeOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ChangeOrderRepositoryInterface::class);
        $this->service    = new ChangeOrderService($this->repository);
    }

    public function test_create_calculates_total_cost(): void
    {
        $saved = null;

        $this->repository->method('getNextNumberForProject')->willReturn(1);
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (object $co) use (&$saved): void {
                $saved = $co;
            });

        $this->service->create('project-1', 'user-1', [
            'title'         => 'Test CO',
            'description'   => 'Description',
            'reason'        => 'Reason',
            'cost_code'     => '03-Concrete',
            'labor_cost'    => 5000,
            'material_cost' => 3000,
        ]);

        $this->assertNotNull($saved);
        $this->assertSame(8000.0, $saved->total_cost);
    }

    public function test_create_sets_draft_state(): void
    {
        $this->repository->method('getNextNumberForProject')->willReturn(1);

        $changeOrder = $this->service->create('project-1', 'user-1', [
            'title'         => 'Test CO',
            'description'   => 'Description',
            'reason'        => 'Reason',
            'cost_code'     => '03-Concrete',
            'labor_cost'    => 1000,
            'material_cost' => 500,
        ]);

        $this->assertSame(ChangeOrderState::DRAFT->value, $changeOrder->state);
    }

    public function test_create_gets_next_number(): void
    {
        $this->repository->method('getNextNumberForProject')->willReturn(5);

        $changeOrder = $this->service->create('project-1', 'user-1', [
            'title'         => 'Test CO',
            'description'   => 'Description',
            'reason'        => 'Reason',
            'cost_code'     => '03-Concrete',
            'labor_cost'    => 1000,
            'material_cost' => 500,
        ]);

        $this->assertSame(5, $changeOrder->number);
    }

    public function test_transition_from_draft_to_submitted_succeeds(): void
    {
        $changeOrder               = new \stdClass();
        $changeOrder->id           = 'co-1';
        $changeOrder->project_id   = 'project-1';
        $changeOrder->submitted_by = 'user-1';
        $changeOrder->state        = 'draft';
        $changeOrder->total_cost   = 8000.0;
        $changeOrder->cost_code    = '03-Concrete';

        $this->repository->method('findById')->willReturn($changeOrder);

        $result = $this->service->transitionState('co-1', ChangeOrderState::SUBMITTED, 'user-1');

        $this->assertInstanceOf(ChangeOrderSubmitted::class, $result['event']);
    }

    public function test_transition_from_under_review_to_approved_succeeds(): void
    {
        $changeOrder               = new \stdClass();
        $changeOrder->id           = 'co-1';
        $changeOrder->project_id   = 'project-1';
        $changeOrder->submitted_by = 'user-1';
        $changeOrder->state        = 'under_review';
        $changeOrder->total_cost   = 12500.0;
        $changeOrder->cost_code    = '16-Electrical';

        $this->repository->method('findById')->willReturn($changeOrder);

        $result = $this->service->transitionState('co-1', ChangeOrderState::APPROVED, 'owner-1');

        $this->assertInstanceOf(ChangeOrderApproved::class, $result['event']);
        /** @var ChangeOrderApproved $event */
        $event = $result['event'];
        $this->assertSame(12500.0, $event->totalCost);
        $this->assertSame('16-Electrical', $event->costCode);
    }

    public function test_transition_from_under_review_to_rejected_includes_reason(): void
    {
        $changeOrder               = new \stdClass();
        $changeOrder->id           = 'co-1';
        $changeOrder->project_id   = 'project-1';
        $changeOrder->submitted_by = 'user-1';
        $changeOrder->state        = 'under_review';
        $changeOrder->total_cost   = 5000.0;
        $changeOrder->cost_code    = '09-Finishes';

        $this->repository->method('findById')->willReturn($changeOrder);

        $result = $this->service->transitionState(
            'co-1',
            ChangeOrderState::REJECTED,
            'owner-1',
            'Out of scope for this phase.'
        );

        $this->assertInstanceOf(ChangeOrderRejected::class, $result['event']);
        /** @var ChangeOrderRejected $event */
        $event = $result['event'];
        $this->assertSame('Out of scope for this phase.', $event->reason);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $changeOrder               = new \stdClass();
        $changeOrder->id           = 'co-1';
        $changeOrder->project_id   = 'project-1';
        $changeOrder->submitted_by = 'user-1';
        $changeOrder->state        = 'draft';
        $changeOrder->total_cost   = 5000.0;
        $changeOrder->cost_code    = '03-Concrete';

        $this->repository->method('findById')->willReturn($changeOrder);

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Cannot transition change order from draft to approved');

        $this->service->transitionState('co-1', ChangeOrderState::APPROVED, 'owner-1');
    }

    public function test_nonexistent_change_order_throws_exception(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->transitionState('no-such-id', ChangeOrderState::SUBMITTED, 'user-1');
    }
}
