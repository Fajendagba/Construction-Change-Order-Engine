<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\ProjectBudget\Models\BudgetLineItem;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ChangeOrderTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private User $contractor;
    private User $architect;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner      = User::factory()->owner()->create();
        $this->contractor = User::factory()->contractor()->create();
        $this->architect  = User::factory()->architect()->create();
        $this->project    = Project::factory()->create();
    }

    public function test_contractor_can_create_change_order(): void
    {
        Queue::fake();

        $this->actingAs($this->contractor)
            ->postJson('/api/projects/' . $this->project->id . '/change-orders', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.state', 'draft')
            ->assertJsonPath('data.title', 'Extra steel reinforcement')
            ->assertJsonPath('data.total_cost', 23000);
    }

    public function test_owner_cannot_create_change_order(): void
    {
        Queue::fake();

        $this->actingAs($this->owner)
            ->postJson('/api/projects/' . $this->project->id . '/change-orders', $this->validPayload())
            ->assertForbidden();
    }

    public function test_architect_cannot_create_change_order(): void
    {
        Queue::fake();

        $this->actingAs($this->architect)
            ->postJson('/api/projects/' . $this->project->id . '/change-orders', $this->validPayload())
            ->assertForbidden();
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actingAs($this->contractor)
            ->postJson('/api/projects/' . $this->project->id . '/change-orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'reason', 'cost_code', 'labor_cost', 'material_cost']);
    }

    public function test_can_list_change_orders_for_project(): void
    {
        ChangeOrder::factory()->count(3)->create([
            'project_id'   => $this->project->id,
            'submitted_by' => $this->contractor->id,
        ]);

        $this->actingAs($this->contractor)
            ->getJson('/api/projects/' . $this->project->id . '/change-orders')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_single_change_order_with_audit_logs(): void
    {
        $changeOrder = $this->createChangeOrder();

        AuditLog::create([
            'change_order_id' => $changeOrder->id,
            'user_id'         => $this->contractor->id,
            'action'          => 'created',
            'from_state'      => null,
            'to_state'        => 'draft',
            'metadata'        => null,
        ]);

        $this->actingAs($this->contractor)
            ->getJson('/api/projects/' . $this->project->id . '/change-orders/' . $changeOrder->id)
            ->assertOk()
            ->assertJsonPath('data.id', $changeOrder->id)
            ->assertJsonStructure(['data' => ['id', 'state', 'submitted_by', 'audit_logs']])
            ->assertJsonCount(1, 'data.audit_logs');
    }

    public function test_contractor_can_submit_draft_change_order(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::DRAFT]);

        $this->actingAs($this->contractor)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'submitted'])
            ->assertOk()
            ->assertJsonPath('data.state', 'submitted');
    }

    public function test_owner_can_move_to_under_review(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::SUBMITTED]);

        $this->actingAs($this->owner)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'under_review'])
            ->assertOk()
            ->assertJsonPath('data.state', 'under_review');
    }

    public function test_owner_can_approve_change_order(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::UNDER_REVIEW]);

        $this->actingAs($this->owner)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.state', 'approved');
    }

    public function test_owner_can_reject_change_order_with_reason(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::UNDER_REVIEW]);

        $this->actingAs($this->owner)
            ->patchJson($this->transitionUrl($changeOrder->id), [
                'target_state'     => 'rejected',
                'rejection_reason' => 'Cost exceeds approved budget variance.',
            ])
            ->assertOk()
            ->assertJsonPath('data.state', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Cost exceeds approved budget variance.');
    }

    public function test_contractor_cannot_approve_change_order(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::UNDER_REVIEW]);

        $this->actingAs($this->contractor)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'approved'])
            ->assertForbidden();
    }

    public function test_architect_cannot_transition_change_orders(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::DRAFT]);

        $this->actingAs($this->architect)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'submitted'])
            ->assertForbidden();
    }

    public function test_cannot_skip_states(): void
    {
        Queue::fake();

        // Draft -> Approved is not a valid state machine transition.
        // The policy allows the owner to approve, but the service rejects it with 422.
        $changeOrder = $this->createChangeOrder(['state' => ChangeOrderState::DRAFT]);

        $this->actingAs($this->owner)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'approved'])
            ->assertUnprocessable();
    }

    public function test_rejected_change_order_can_be_revised_to_draft(): void
    {
        Queue::fake();

        $changeOrder = $this->createChangeOrder([
            'state'            => ChangeOrderState::REJECTED,
            'reviewed_by'      => $this->owner->id,
            'rejection_reason' => 'Incomplete documentation.',
        ]);

        $this->actingAs($this->contractor)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'draft'])
            ->assertOk()
            ->assertJsonPath('data.state', 'draft');
    }

    public function test_approval_triggers_budget_update(): void
    {
        config(['queue.default' => 'sync', 'broadcasting.default' => 'log']);

        $lineItem = BudgetLineItem::factory()->create([
            'project_id'       => $this->project->id,
            'cost_code'        => '05-Metals',
            'original_amount'  => 100000.00,
            'approved_changes' => 0.00,
            'current_amount'   => 100000.00,
        ]);

        $changeOrder = $this->createChangeOrder([
            'state'         => ChangeOrderState::UNDER_REVIEW,
            'cost_code'     => '05-Metals',
            'labor_cost'    => 10000.00,
            'material_cost' => 5000.00,
            'total_cost'    => 15000.00,
        ]);

        $originalBudget = (float) $this->project->original_budget;

        $this->actingAs($this->owner)
            ->patchJson($this->transitionUrl($changeOrder->id), ['target_state' => 'approved'])
            ->assertOk();

        $lineItem->refresh();
        $this->project->refresh();

        $this->assertEqualsWithDelta(15000.00, (float) $lineItem->approved_changes, 0.01);
        $this->assertEqualsWithDelta(115000.00, (float) $lineItem->current_amount, 0.01);
        $this->assertEqualsWithDelta(15000.00, (float) $this->project->approved_changes_total, 0.01);
        $this->assertEqualsWithDelta($originalBudget + 15000.00, (float) $this->project->current_budget, 0.01);
    }

    private function createChangeOrder(array $overrides = []): ChangeOrder
    {
        return ChangeOrder::factory()->create(array_merge([
            'project_id'   => $this->project->id,
            'submitted_by' => $this->contractor->id,
        ], $overrides));
    }

    private function transitionUrl(string $changeOrderId): string
    {
        return '/api/projects/' . $this->project->id . '/change-orders/' . $changeOrderId . '/transition';
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'title'         => 'Extra steel reinforcement',
            'description'   => 'Additional steel beams required due to design change.',
            'reason'        => 'Structural engineer recommendation.',
            'cost_code'     => '05-Metals',
            'labor_cost'    => 15000,
            'material_cost' => 8000,
        ];
    }
}
