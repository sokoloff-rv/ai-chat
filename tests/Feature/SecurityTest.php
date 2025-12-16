<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_public_id_is_protected_from_sql_injection(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $maliciousId = "' OR '1'='1";

        $result = Chat::where('public_id', $maliciousId)->first();

        $this->assertNull($result);
    }

    public function test_visitor_session_id_is_protected_from_sql_injection(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-sql-chat',
            'name' => 'Test Chat',
            'allowed_domains' => null,
        ]);

        $sessionResponse = $this->postJson("/api/widget/{$chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $maliciousSessionId = "'; DROP TABLE visitors; --";

        $response = $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $maliciousSessionId,
            'message' => 'Test',
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseCount('visitors', 1);
    }

    public function test_html_tags_are_stripped_from_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-xss-chat',
            'name' => 'Test Chat',
            'allowed_domains' => null,
        ]);

        $sessionResponse = $this->postJson("/api/widget/{$chat->public_id}/session");
        $sessionResponse->assertStatus(200);

        $sessionId = $sessionResponse->json('session_id');
        $this->assertNotNull($sessionId);

        $response = $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => '<img src=x onerror=alert(1)>Hello<script>alert("XSS")</script>',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Hello',
        ]);

        $this->assertDatabaseMissing('messages', [
            'content' => '<script>',
        ]);
    }

    public function test_chat_name_is_escaped_in_responses(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-id',
            'name' => '<script>alert("XSS")</script>Honest Bot',
        ]);

        $response = $this->getJson("/api/widget/{$chat->public_id}/config");

        $response->assertStatus(200);

        $name = $response->json('name');
        $this->assertStringNotContainsString('<script>', $name);
        $this->assertStringContainsString('&lt;script&gt;', $name);
    }

    public function test_message_length_is_enforced(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $sessionResponse = $this->postJson("/api/widget/{$chat->public_id}/session");
        $sessionId = $sessionResponse->json('session_id');

        $longMessage = str_repeat('A', 2001);

        $response = $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => $longMessage,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_access_other_users_chat_data(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat1 = Chat::factory()->create(['user_id' => $user1->id]);
        $chat2 = Chat::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);

        $response = $this->get(route('chats.show', $chat2));
        $response->assertStatus(403);

        $response = $this->get(route('chats.edit', $chat2));
        $response->assertStatus(403);

        $response = $this->put(route('chats.update', $chat2), ['name' => 'Hacked']);
        $response->assertStatus(403);

        $response = $this->delete(route('chats.destroy', $chat2));
        $response->assertStatus(403);
    }
}
