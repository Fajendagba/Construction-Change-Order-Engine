<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ProjectBudget\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $originalBudget = fake()->randomFloat(2, 500000, 50000000);

        return [
            'name'                   => fake()->company() . ' Tower',
            'description'            => fake()->sentence(),
            'original_budget'        => $originalBudget,
            'approved_changes_total' => 0,
            'current_budget'         => $originalBudget,
        ];
    }
}
