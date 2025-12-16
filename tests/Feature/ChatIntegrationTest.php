<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_chat_workflow(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('chats.store'), [
            'name' => 'Мой первый бот',
            'user_instruction' => 'Ты помощник по продажам',
            'allowed_domains' => ['example.com', 'test.com'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chats', [
            'name' => 'Мой первый бот',
            'user_id' => $user->id,
        ]);

        $chat = Chat::where('name', 'Мой первый бот')->first();

        $response = $this->getJson("/api/widget/{$chat->public_id}/config");
        $response->assertStatus(200)
            ->assertJsonStructure(['name', 'welcome_message', 'allowed_domains']);

        $sessionResponse = $this->withHeaders([
            'Referer' => 'https://example.com/page',
        ])->postJson("/api/widget/{$chat->public_id}/session");

        $sessionResponse->assertStatus(200);
        $sessionId = $sessionResponse->json('session_id');

        $messageResponse = $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $sessionId,
            'message' => 'Привет, расскажи о товаре',
        ]);

        $messageResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'message_id']);

        $historyResponse = $this->getJson("/api/widget/{$chat->public_id}/history?session_id={$sessionId}");
        $historyResponse->assertStatus(200);

        $messages = $historyResponse->json('messages');
        $this->assertCount(2, $messages);

        $updateResponse = $this->actingAs($user)
            ->put(route('chats.update', $chat), [
                'name' => 'Обновлённый бот',
                'user_instruction' => 'Новая инструкция',
                'allowed_domains' => ['newdomain.com'],
            ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'name' => 'Обновлённый бот',
        ]);

        $deleteResponse = $this->actingAs($user)
            ->delete(route('chats.destroy', $chat));

        $deleteResponse->assertRedirect(route('chats.index'));
        $this->assertDatabaseMissing('chats', ['id' => $chat->id]);

        $this->assertDatabaseMissing('visitors', ['chat_id' => $chat->id]);
        $this->assertDatabaseMissing('messages', ['chat_id' => $chat->id]);
    }

    public function test_widget_respects_domain_restrictions(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'restricted-bot',
            'name' => 'Restricted Bot',
            'allowed_domains' => ['allowed.com'],
        ]);

        $response = $this->withHeaders([
            'Referer' => 'https://allowed.com/page',
        ])->postJson("/api/widget/{$chat->public_id}/session");

        $response->assertStatus(200);

        $response = $this->withHeaders([
            'Referer' => 'https://forbidden.com/page',
        ])->postJson("/api/widget/{$chat->public_id}/session");

        $response->assertStatus(403);

        $response = $this->postJson("/api/widget/{$chat->public_id}/session");
        $response->assertStatus(403);
    }

    public function test_multiple_visitors_can_use_same_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $session1Response = $this->postJson("/api/widget/{$chat->public_id}/session");
        $sessionId1 = $session1Response->json('session_id');

        $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $sessionId1,
            'message' => 'Посетитель 1',
        ]);

        $session2Response = $this->postJson("/api/widget/{$chat->public_id}/session");
        $sessionId2 = $session2Response->json('session_id');

        $this->postJson("/api/widget/{$chat->public_id}/message", [
            'session_id' => $sessionId2,
            'message' => 'Посетитель 2',
        ]);

        $history1 = $this->getJson("/api/widget/{$chat->public_id}/history?session_id={$sessionId1}");
        $history2 = $this->getJson("/api/widget/{$chat->public_id}/history?session_id={$sessionId2}");

        $history1->assertStatus(200);
        $history2->assertStatus(200);

        $messages1 = $history1->json('messages');
        $messages2 = $history2->json('messages');

        $this->assertNotNull($messages1);
        $this->assertNotNull($messages2);
        $this->assertCount(2, $messages1);
        $this->assertCount(2, $messages2);

        $this->assertStringContainsString('Посетитель 1', $messages1[0]['content']);
        $this->assertStringContainsString('Посетитель 2', $messages2[0]['content']);
    }

    public function test_user_can_have_multiple_chats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('chats.store'), [
            'name' => 'Чат 1',
        ]);

        $this->post(route('chats.store'), [
            'name' => 'Чат 2',
        ]);

        $this->post(route('chats.store'), [
            'name' => 'Чат 3',
        ]);

        $response = $this->get(route('chats.index'));

        $response->assertStatus(200)
            ->assertInertia(fn($page) => $page->has('chats', 3));

        $this->assertEquals(3, $user->chats()->count());
    }
}
