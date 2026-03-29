<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeOrder>
 */
final class ChangeOrderFactory extends Factory
{
    protected $model = ChangeOrder::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $costCodes = [
            '01-General', '02-Sitework', '03-Concrete', '04-Masonry', '05-Metals',
            '06-Carpentry', '07-Thermal', '08-Doors-Windows', '09-Finishes',
            '15-Mechanical', '16-Electrical',
        ];

        $laborCost    = fake()->randomFloat(2, 1000, 50000);
        $materialCost = fake()->randomFloat(2, 500, 30000);

        return [
            'number'        => 1,
            'title'         => fake()->sentence(4),
            'description'   => fake()->paragraph(),
            'reason'        => fake()->sentence(),
            'cost_code'     => fake()->randomElement($costCodes),
            'labor_cost'    => $laborCost,
            'material_cost' => $materialCost,
            'total_cost'    => $laborCost + $materialCost,
            'state'         => ChangeOrderState::DRAFT,
        ];
    }
}
