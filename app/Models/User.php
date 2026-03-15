<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\ChangeOrder\Models\ChangeOrder;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /** @return HasMany<ChangeOrder, $this> */
    public function submittedChangeOrders(): HasMany
    {
        return $this->hasMany(ChangeOrder::class, 'submitted_by');
    }

    /** @return HasMany<ChangeOrder, $this> */
    public function reviewedChangeOrders(): HasMany
    {
        return $this->hasMany(ChangeOrder::class, 'reviewed_by');
    }
}
