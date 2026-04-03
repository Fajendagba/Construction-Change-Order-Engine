<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $contractor;
    private User $owner;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contractor = User::factory()->contractor()->create();
        $this->owner      = User::factory()->owner()->create();
        $this->project    = Project::factory()->create();
    }

    public function test_can_list_audit_logs_for_change_order(): void
    {
        $changeOrder = ChangeOrder::factory()->create([
            'project_id'   => $this->project->id,
            'submitted_by' => $this->contractor->id,
        ]);

        AuditLog::create([
            'change_order_id' => $changeOrder->id,
            'user_id'         => $this->contractor->id,
            'action'          => 'created',
            'from_state'      => null,
            'to_state'        => 'draft',
            'metadata'        => null,
        ]);

        AuditLog::create([
            'change_order_id' => $changeOrder->id,
            'user_id'         => $this->contractor->id,
            'action'          => 'submitted',
            'from_state'      => 'draft',
            'to_state'        => 'submitted',
            'metadata'        => null,
        ]);

        $this->actingAs($this->contractor)
            ->getJson('/api/projects/' . $this->project->id . '/change-orders/' . $changeOrder->id . '/audit-logs')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'action', 'from_state', 'to_state', 'user', 'metadata', 'created_at'],
                ],
            ]);
    }

    public function test_state_transitions_create_audit_logs(): void
    {
        config(['queue.default' => 'sync', 'broadcasting.default' => 'log']);

        $changeOrder = ChangeOrder::factory()->create([
            'project_id'   => $this->project->id,
            'submitted_by' => $this->contractor->id,
            'state'        => ChangeOrderState::DRAFT,
        ]);

        $this->actingAs($this->contractor)
            ->patchJson(
                '/api/projects/' . $this->project->id . '/change-orders/' . $changeOrder->id . '/transition',
                ['target_state' => 'submitted'],
            )
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'change_order_id' => $changeOrder->id,
            'user_id'         => $this->contractor->id,
            'action'          => 'submitted',
            'from_state'      => 'draft',
            'to_state'        => 'submitted',
        ]);
    }
}
