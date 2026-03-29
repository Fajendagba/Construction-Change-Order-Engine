<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use App\Domain\ProjectBudget\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Project $project */
        $project = $this->resource;

        return [
            'id'                     => $project->id,
            'name'                   => $project->name,
            'description'            => $project->description,
            'original_budget'        => (float) $project->original_budget,
            'approved_changes_total' => (float) $project->approved_changes_total,
            'current_budget'         => (float) $project->current_budget,
            'budget_line_items'      => $this->whenLoaded('budgetLineItems', fn () => $project->budgetLineItems->map(
                fn ($item) => [
                    'cost_code'        => $item->cost_code,
                    'description'      => $item->description,
                    'original_amount'  => (float) $item->original_amount,
                    'approved_changes' => (float) $item->approved_changes,
                    'current_amount'   => (float) $item->current_amount,
                ]
            )),
            'change_orders_count'    => $this->whenCounted('changeOrders'),
            'created_at'             => $project->created_at?->toIso8601String(),
            'updated_at'             => $project->updated_at?->toIso8601String(),
        ];
    }
}
