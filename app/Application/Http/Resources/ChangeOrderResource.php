<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use App\Domain\ChangeOrder\Models\ChangeOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ChangeOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ChangeOrder $changeOrder */
        $changeOrder = $this->resource;

        return [
            'id'               => $changeOrder->id,
            'number'           => $changeOrder->number,
            'title'            => $changeOrder->title,
            'description'      => $changeOrder->description,
            'reason'           => $changeOrder->reason,
            'cost_code'        => $changeOrder->cost_code,
            'labor_cost'       => (float) $changeOrder->labor_cost,
            'material_cost'    => (float) $changeOrder->material_cost,
            'total_cost'       => (float) $changeOrder->total_cost,
            'state'            => $changeOrder->state->value,
            'submitted_by'     => $this->whenLoaded('submittedBy', fn () => $changeOrder->submittedBy !== null
                ? ['id' => $changeOrder->submittedBy->id, 'name' => $changeOrder->submittedBy->name]
                : null),
            'reviewed_by'      => $this->whenLoaded('reviewedBy', fn () => $changeOrder->reviewedBy !== null
                ? ['id' => $changeOrder->reviewedBy->id, 'name' => $changeOrder->reviewedBy->name]
                : null),
            'rejection_reason' => $changeOrder->rejection_reason,
            'state_changed_at' => $changeOrder->state_changed_at?->toIso8601String(),
            'created_at'       => $changeOrder->created_at?->toIso8601String(),
            'updated_at'       => $changeOrder->updated_at?->toIso8601String(),
        ];
    }
}
