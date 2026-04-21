<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class ChangeOrderUpdateTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private User $contractor;
    private Project $project;
    private ChangeOrder $draftChangeOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner      = User::factory()->owner()->create();
        $this->contractor = User::factory()->contractor()->create();
        $this->project    = Project::factory()->create();

        $this->draftChangeOrder = ChangeOrder::factory()->create([
            'project_id'    => $this->project->id,
            'submitted_by'  => $this->contractor->id,
            'state'         => ChangeOrderState::DRAFT,
            'labor_cost'    => 5000,
            'material_cost' => 3000,
            'total_cost'    => 8000,
        ]);
    }

    public function test_contractor_can_edit_draft_change_order(): void
    {
        $this->actingAs($this->contractor)
            ->patchJson($this->url($this->draftChangeOrder->id), [
                'title'       => 'Updated steel reinforcement',
                'description' => 'Updated scope due to revised drawings.',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated steel reinforcement')
            ->assertJsonPath('data.description', 'Updated scope due to revised drawings.')
            ->assertJsonPath('data.state', 'draft');
    }

    public function test_contractor_cannot_edit_submitted_change_order(): void
    {
        $submitted = ChangeOrder::factory()->create([
            'project_id'   => $this->project->id,
            'submitted_by' => $this->contractor->id,
            'state'        => ChangeOrderState::SUBMITTED,
        ]);

        $this->actingAs($this->contractor)
            ->patchJson($this->url($submitted->id), ['title' => 'Should be blocked'])
            ->assertForbidden();
    }

    public function test_contractor_cannot_edit_another_contractors_change_order(): void
    {
        $otherContractor = User::factory()->contractor()->create();

        $this->actingAs($otherContractor)
            ->patchJson($this->url($this->draftChangeOrder->id), ['title' => 'Hijacked title'])
            ->assertForbidden();
    }

    public function test_owner_cannot_edit_change_order(): void
    {
        $this->actingAs($this->owner)
            ->patchJson($this->url($this->draftChangeOrder->id), ['title' => 'Owner edit attempt'])
            ->assertForbidden();
    }

    public function test_edit_recalculates_total_cost(): void
    {
        $this->actingAs($this->contractor)
            ->patchJson($this->url($this->draftChangeOrder->id), [
                'material_cost' => 4000,
            ])
            ->assertOk()
            ->assertJsonPath('data.labor_cost', 5000)
            ->assertJsonPath('data.material_cost', 4000)
            ->assertJsonPath('data.total_cost', 9000);
    }

    private function url(string $changeOrderId): string
    {
        return '/api/projects/' . $this->project->id . '/change-orders/' . $changeOrderId;
    }
}
