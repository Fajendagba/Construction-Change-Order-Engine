<?php

declare(strict_types=1);

namespace App\Domain\ProjectBudget\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BudgetLineItem extends Model
{
    use HasUlids;

    protected $table = 'budget_line_items';

    /** @var list<string> */
    protected $fillable = [
        'project_id',
        'cost_code',
        'description',
        'original_amount',
        'approved_changes',
        'current_amount',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'original_amount'  => 'decimal:2',
            'approved_changes' => 'decimal:2',
            'current_amount'   => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
