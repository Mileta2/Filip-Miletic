<?php

namespace Database\Factories;

use App\Enums\StudyLevel;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Student,
            'must_change_password' => false,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => UserRole::SuperAdmin]);
    }

    public function professor(): static
    {
        return $this->state(fn () => ['role' => UserRole::Professor])
            ->afterCreating(fn (User $user) => $user->professorProfile()->create([
                'academic_title' => 'Docent',
                'department' => 'Informacione tehnologije',
            ]));
    }

    public function student(StudyLevel $level = StudyLevel::Undergraduate): static
    {
        return $this->state(fn () => ['role' => UserRole::Student])
            ->afterCreating(fn (User $user) => $user->studentProfile()->create([
                'index_number' => fake()->unique()->numerify('IT ####/###'),
                'study_level' => $level,
            ]));
    }
}
