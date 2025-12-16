<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    protected $model = Chat::class;

    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => Str::uuid(),
            'name' => fake()->words(3, true),
            'user_id' => User::factory(),
            'user_instruction' => fake()->optional()->paragraph(),
            'allowed_domains' => null,
        ];
    }

    /**
     * Чат с пользовательской инструкцией.
     */
    public function withInstruction(string $instruction): static
    {
        return $this->state(fn(array $attributes) => [
            'user_instruction' => $instruction,
        ]);
    }

    /**
     * Чат с разрешенными доменами.
     */
    public function withAllowedDomains(array $domains): static
    {
        return $this->state(fn(array $attributes) => [
            'allowed_domains' => json_encode($domains),
        ]);
    }
}
