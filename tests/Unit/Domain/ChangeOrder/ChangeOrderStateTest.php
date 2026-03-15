<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ChangeOrder;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use PHPUnit\Framework\TestCase;

final class ChangeOrderStateTest extends TestCase
{
    public function test_draft_can_transition_to_submitted(): void
    {
        $this->assertTrue(ChangeOrderState::DRAFT->canTransitionTo(ChangeOrderState::SUBMITTED));
    }

    public function test_draft_cannot_transition_to_approved(): void
    {
        $this->assertFalse(ChangeOrderState::DRAFT->canTransitionTo(ChangeOrderState::APPROVED));
    }

    public function test_draft_cannot_transition_to_under_review(): void
    {
        $this->assertFalse(ChangeOrderState::DRAFT->canTransitionTo(ChangeOrderState::UNDER_REVIEW));
    }

    public function test_submitted_can_transition_to_under_review(): void
    {
        $this->assertTrue(ChangeOrderState::SUBMITTED->canTransitionTo(ChangeOrderState::UNDER_REVIEW));
    }

    public function test_submitted_cannot_transition_to_approved(): void
    {
        $this->assertFalse(ChangeOrderState::SUBMITTED->canTransitionTo(ChangeOrderState::APPROVED));
    }

    public function test_under_review_can_transition_to_approved(): void
    {
        $this->assertTrue(ChangeOrderState::UNDER_REVIEW->canTransitionTo(ChangeOrderState::APPROVED));
    }

    public function test_under_review_can_transition_to_rejected(): void
    {
        $this->assertTrue(ChangeOrderState::UNDER_REVIEW->canTransitionTo(ChangeOrderState::REJECTED));
    }

    public function test_under_review_cannot_transition_to_draft(): void
    {
        $this->assertFalse(ChangeOrderState::UNDER_REVIEW->canTransitionTo(ChangeOrderState::DRAFT));
    }

    public function test_approved_cannot_transition_to_anything(): void
    {
        foreach (ChangeOrderState::cases() as $state) {
            $this->assertFalse(
                ChangeOrderState::APPROVED->canTransitionTo($state),
                "APPROVED should not transition to {$state->value}"
            );
        }
    }

    public function test_rejected_can_transition_to_draft(): void
    {
        $this->assertTrue(ChangeOrderState::REJECTED->canTransitionTo(ChangeOrderState::DRAFT));
    }

    public function test_approved_is_final(): void
    {
        $this->assertTrue(ChangeOrderState::APPROVED->isFinal());
    }

    public function test_rejected_is_not_final(): void
    {
        $this->assertFalse(ChangeOrderState::REJECTED->isFinal());
    }
}
