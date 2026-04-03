<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\ProjectBudget\Models\BudgetLineItem;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_projects(): void
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects')
            ->assertOk();

        $returnedIds = collect($response->json('data'))->pluck('id');
        $this->assertContains($project1->id, $returnedIds->all());
        $this->assertContains($project2->id, $returnedIds->all());
    }

    public function test_can_show_project_with_budget_and_change_orders(): void
    {
        $project = Project::factory()->create();
        BudgetLineItem::factory()->count(3)->create(['project_id' => $project->id]);

        $this->actingAs($this->user)
            ->getJson('/api/projects/' . $project->id)
            ->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'original_budget',
                    'approved_changes_total',
                    'current_budget',
                    'budget_line_items',
                    'change_orders_count',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonCount(3, 'data.budget_line_items');
    }

    public function test_unauthenticated_user_cannot_access_projects(): void
    {
        $this->getJson('/api/projects')
            ->assertUnauthorized();
    }
}
