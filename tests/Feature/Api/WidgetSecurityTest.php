<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WidgetSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-security-123',
            'name' => 'Тестовый бот безопасности',
            'user_instruction' => 'Ты полезный ассистент.',
        ]);
    }

    public function test_rate_limiting_prevents_too_many_session_requests(): void
    {
        RateLimiter::clear('session:127.0.0.1');

        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson("/api/widget/{$this->chat->public_id}/session");
            $response->assertStatus(200);
        }

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $response->assertStatus(429);
    }

    public function test_rate_limiting_prevents_too_many_messages(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        RateLimiter::clear('message:' . $sessionId);

        for ($i = 0; $i < 20; $i++) {
            $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
                'session_id' => $sessionId,
                'message' => "Сообщение {$i}",
            ]);
            $response->assertStatus(200);
        }

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => 'Слишком много сообщений',
        ]);
        $response->assertStatus(429);
    }

    public function test_sanitizes_xss_in_message(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => '<script>alert("XSS")</script>Привет',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Привет',
        ]);
    }

    public function test_validates_uuid_session_id(): void
    {
        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => 'not-a-valid-uuid',
            'message' => 'Привет',
        ]);

        $response->assertStatus(422);
    }

    public function test_validates_message_length(): void
    {
        $sessionResponse = $this->postJson("/api/widget/{$this->chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => str_repeat('a', 2001),
        ]);

        $response->assertStatus(422);
    }

    public function test_trims_long_user_agent(): void
    {
        $longUserAgent = str_repeat('Mozilla/5.0 ', 100);

        $response = $this->withHeaders([
            'User-Agent' => $longUserAgent,
        ])->postJson("/api/widget/{$this->chat->public_id}/session");

        $response->assertStatus(200);

        $visitor = Visitor::first();
        $this->assertLessThanOrEqual(500, strlen($visitor->user_agent));
    }

    public function test_trims_long_referrer(): void
    {
        $longReferrer = 'https://example.com/' . str_repeat('path/', 100);

        $response = $this->withHeaders([
            'Referer' => $longReferrer,
        ])->postJson("/api/widget/{$this->chat->public_id}/session");

        $response->assertStatus(200);

        $visitor = Visitor::first();
        $this->assertLessThanOrEqual(500, strlen($visitor->referrer));
    }

    public function test_domain_restriction_with_subdomain(): void
    {
        $this->chat->update(['allowed_domains' => ['example.com']]);

        $response = $this->postJson(
            "/api/widget/{$this->chat->public_id}/session",
            [],
            ['Referer' => 'https://sub.example.com/page']
        );

        $response->assertStatus(200);
    }

    public function test_domain_restriction_blocks_wrong_domain(): void
    {
        $this->chat->update(['allowed_domains' => ['example.com']]);

        $response = $this->postJson(
            "/api/widget/{$this->chat->public_id}/session",
            [],
            ['Referer' => 'https://evil.com/page']
        );

        $response->assertStatus(403);
    }

    public function test_domain_restriction_blocks_missing_referer(): void
    {
        $this->chat->update(['allowed_domains' => ['example.com']]);

        $response = $this->postJson("/api/widget/{$this->chat->public_id}/session");

        $response->assertStatus(403);
    }

    public function test_escapes_html_in_config_response(): void
    {
        $this->chat->update(['name' => '<script>alert("XSS")</script>Bot']);

        $response = $this->getJson("/api/widget/{$this->chat->public_id}/config");

        $response->assertStatus(200);
        $name = $response->json('name');

        $this->assertStringNotContainsString('<script>', $name);
        $this->assertStringContainsString('&lt;script&gt;', $name);
    }
}
