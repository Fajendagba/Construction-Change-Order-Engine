<?php

declare(strict_types=1);

namespace App\Domain\ChangeOrder\Models;

use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Database\Factories\ChangeOrderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property ChangeOrderState          $state
 * @property Carbon|null               $state_changed_at
 * @property Carbon|null               $reviewed_at
 */
final class ChangeOrder extends Model
{
    /** @use HasFactory<ChangeOrderFactory> */
    use HasFactory;
    use HasUlids;

    protected static function newFactory(): ChangeOrderFactory
    {
        return ChangeOrderFactory::new();
    }

    protected $table = 'change_orders';

    /** @var list<string> */
    protected $fillable = [
        'project_id',
        'submitted_by',
        'reviewed_by',
        'number',
        'title',
        'description',
        'reason',
        'cost_code',
        'labor_cost',
        'material_cost',
        'total_cost',
        'state',
        'state_changed_at',
        'reviewed_at',
        'rejection_reason',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'state'            => ChangeOrderState::class,
            'labor_cost'       => 'decimal:2',
            'material_cost'    => 'decimal:2',
            'total_cost'       => 'decimal:2',
            'state_changed_at' => 'datetime',
            'reviewed_at'      => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /** @return BelongsTo<User, $this> */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return HasMany<AuditLog, $this> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'change_order_id');
    }
}
