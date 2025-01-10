<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AuthType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lastname' => fake()->lastName(),
            'firstname' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(60),
            'uid' => fake()->unique()->uuid(),
            'phone' => fake()->unique()->phoneNumber(),
            'address' => fake()->address(),
            'account_status' => AccountStatus::ENABLED->value,
            'auth_type' => AuthType::EMAIL_PASSWORD->value
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's account status should be disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => AccountStatus::DISABLED->value,
        ]);
    }

    /**
     * Indicate the model's preferences locale which should be set.
     */
    public function withLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => ['locale' => $locale],
        ]);
    }

    /**
     * Indicate the model's email which should be set.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Indicate the model's authenticated with phone number.
     */
    public function authWithPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
            'auth_type' => AuthType::PHONE->value
        ]);
    }

    /**
     * Indicate the model's authenticated with google.
     */
    public function authWithGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'auth_type' => AuthType::GOOGLE->value
        ]);
    }
}
