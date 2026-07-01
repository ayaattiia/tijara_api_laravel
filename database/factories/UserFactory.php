<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * CORRIGÉ — Aligné sur le vrai schéma Users (IdUser, FirstName, LastName, Role).
 * L'ancien factory utilisait 'name' qui n'existe pas dans la table Users.
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'FirstName'          => fake()->firstName(),
            'LastName'           => fake()->lastName(),
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'Telephone'          => fake()->phoneNumber(),
            'Address'            => fake()->address(),
            'Role'               => 'user',
            'Active'             => 1,
            'remember_token'     => Str::random(10),
            'CreatedAt'          => now(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['Role' => 'admin']);
    }

    public function vendor(): static
    {
        return $this->state(fn () => ['Role' => 'vendor']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
