<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visitor>
 */
class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'session_id' => Str::uuid(),
            'user_agent' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'referrer' => fake()->optional()->url(),
        ];
    }
}
