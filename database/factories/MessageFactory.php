<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'visitor_id' => Visitor::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
        ];
    }

    /**
     * Сообщение от пользователя.
     */
    public function userMessage(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'user',
        ]);
    }

    /**
     * Сообщение от ассистента.
     */
    public function assistantMessage(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'assistant',
        ]);
    }
}
