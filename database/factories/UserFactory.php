<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Shared\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected static ?string $password = null;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => UserRole::CONTRACTOR,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::OWNER,
        ]);
    }

    public function contractor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CONTRACTOR,
        ]);
    }

    public function architect(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ARCHITECT,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
