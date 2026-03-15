<?php

declare(strict_types=1);

namespace App\Domain\ProjectBudget\Models;

use App\Domain\ChangeOrder\Models\ChangeOrder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Project extends Model
{
    use HasUlids;

    protected $table = 'projects';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'original_budget',
        'approved_changes_total',
        'current_budget',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'original_budget'        => 'decimal:2',
            'approved_changes_total' => 'decimal:2',
            'current_budget'         => 'decimal:2',
        ];
    }

    /** @return HasMany<ChangeOrder, $this> */
    public function changeOrders(): HasMany
    {
        return $this->hasMany(ChangeOrder::class, 'project_id');
    }

    /** @return HasMany<BudgetLineItem, $this> */
    public function budgetLineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class, 'project_id');
    }
}
