<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ProjectBudget\Models\BudgetLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetLineItem>
 */
final class BudgetLineItemFactory extends Factory
{
    protected $model = BudgetLineItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $costCodeMap = [
            '01-General'       => 'General Requirements',
            '02-Sitework'      => 'Sitework and Earthwork',
            '03-Concrete'      => 'Concrete Structure',
            '04-Masonry'       => 'Masonry Work',
            '05-Metals'        => 'Structural Steel and Metals',
            '06-Carpentry'     => 'Rough and Finish Carpentry',
            '07-Thermal'       => 'Thermal and Moisture Protection',
            '08-Doors-Windows' => 'Doors, Windows and Glazing',
            '09-Finishes'      => 'Interior Finishes',
            '15-Mechanical'    => 'Mechanical and Plumbing',
            '16-Electrical'    => 'Electrical Systems',
        ];

        $costCode       = fake()->randomElement(array_keys($costCodeMap));
        $originalAmount = fake()->randomFloat(2, 10000, 500000);

        return [
            'cost_code'        => $costCode,
            'description'      => $costCodeMap[$costCode],
            'original_amount'  => $originalAmount,
            'approved_changes' => 0,
            'current_amount'   => $originalAmount,
        ];
    }
}
