<?php

declare(strict_types=1);

namespace App\Domain\AuditLog\Models;

use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuditLog extends Model
{
    use HasUlids;

    protected $table = 'audit_logs';

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'change_order_id',
        'user_id',
        'action',
        'from_state',
        'to_state',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ChangeOrder, $this> */
    public function changeOrder(): BelongsTo
    {
        return $this->belongsTo(ChangeOrder::class, 'change_order_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
