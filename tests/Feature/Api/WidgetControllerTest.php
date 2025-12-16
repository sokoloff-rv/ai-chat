<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetControllerTest extends TestCase
{
    use RefreshDatabase;

    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-public-id-123',
            'name' => 'Тестовый бот',
            'user_instruction' => 'Ты полезный ассистент.',
        ]);
    }

    public function test_can_get_chat_config(): void
    {
        $response = $this->getJson("/api/widget/{$this->chat->public_id}/config");

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Тестовый бот',
            ]);
    }

    public function test_returns_404_for_invalid_chat(): void
    {
        $response = $this->getJson('/api/widget/invalid-id/config');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Чат-бот не найден',
            ]);
    }

    public function test_can_start_session(): void
    {
        $response = $this->postJson("/api/widget/{$this->chat->public_id}/session");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'chat_name',
            ]);

        $this->assertDatabaseCount('visitors', 1);
    }

    public function test_can_send_message(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => 'Привет!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'message_id',
            ]);

        $this->assertDatabaseCount('messages', 2);
    }

    public function test_can_get_history(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => 'Привет!',
        ]);

        $response = $this->getJson("/api/widget/{$this->chat->public_id}/history?session_id={$sessionId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'messages' => [
                    '*' => ['role', 'content', 'created_at'],
                ],
            ]);

        $this->assertCount(2, $response->json('messages'));
    }

    public function test_validates_message_request(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(422);
    }

    public function test_returns_error_for_invalid_session(): void
    {
        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => 'invalid-session',
            'message' => 'Привет!',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Сессия не найдена',
            ]);
    }

    public function test_domain_restriction_works(): void
    {
        $this->chat->update(['allowed_domains' => "example.com\ntest.com"]);

        $response = $this->postJson(
            "/api/widget/{$this->chat->public_id}/session",
            [],
            ['Referer' => 'https://example.com/page']
        );
        $response->assertStatus(200);

        $response = $this->postJson(
            "/api/widget/{$this->chat->public_id}/session",
            [],
            ['Referer' => 'https://evil.com/page']
        );
        $response->assertStatus(403);
    }
}
