<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_guest_cannot_access_chats_index(): void
    {
        $response = $this->get(route('chats.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_chats_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('chats.index'));

        $response->assertStatus(200);
    }

    public function test_user_sees_only_their_chats(): void
    {
        $userChat = Chat::factory()->create(['user_id' => $this->user->id]);
        $otherChat = Chat::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('chats.index'));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('Chats/Index')
                    ->has('chats', 1)
            );
    }

    public function test_user_can_view_create_chat_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('chats.create'));

        $response->assertStatus(200)
            ->assertInertia(fn($page) => $page->component('Chats/Create'));
    }

    public function test_user_can_create_chat(): void
    {
        $chatData = [
            'name' => 'Тестовый чат-бот',
            'user_instruction' => 'Ты — помощник по тестированию',
            'allowed_domains' => ['example.com', 'test.com'],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('chats.store'), $chatData);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('chats', [
            'name' => 'Тестовый чат-бот',
            'user_id' => $this->user->id,
            'user_instruction' => 'Ты — помощник по тестированию',
        ]);
    }

    public function test_chat_creation_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('chats.store'), [
                'user_instruction' => 'Инструкция',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_chat_name_cannot_exceed_255_characters(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('chats.store'), [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_instruction_cannot_exceed_5000_characters(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('chats.store'), [
                'name' => 'Тест',
                'user_instruction' => str_repeat('a', 5001),
            ]);

        $response->assertSessionHasErrors('user_instruction');
    }

    public function test_user_can_view_their_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('chats.show', $chat));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('Chats/Show')
                    ->has('chat')
                    ->has('embedCode')
            );
    }

    public function test_user_cannot_view_other_users_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('chats.show', $chat));

        $response->assertStatus(403);
    }

    public function test_user_can_view_edit_form_for_their_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('chats.edit', $chat));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('Chats/Edit')
                    ->has('chat')
            );
    }

    public function test_user_cannot_edit_other_users_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('chats.edit', $chat));

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Обновленное название',
            'user_instruction' => 'Новая инструкция',
            'allowed_domains' => ['newdomain.com'],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('chats.update', $chat), $updateData);

        $response->assertRedirect(route('chats.show', $chat))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'name' => 'Обновленное название',
            'user_instruction' => 'Новая инструкция',
        ]);
    }

    public function test_user_cannot_update_other_users_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->put(route('chats.update', $chat), [
                'name' => 'Попытка обновить',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('chats.destroy', $chat));

        $response->assertRedirect(route('chats.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('chats', ['id' => $chat->id]);
    }

    public function test_user_cannot_delete_other_users_chat(): void
    {
        $chat = Chat::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('chats.destroy', $chat));

        $response->assertStatus(403);

        $this->assertDatabaseHas('chats', ['id' => $chat->id]);
    }

    public function test_chat_creates_with_uuid_public_id(): void
    {
        $chatData = [
            'name' => 'Тестовый чат',
        ];

        $this->actingAs($this->user)
            ->post(route('chats.store'), $chatData);

        $chat = Chat::where('name', 'Тестовый чат')->first();

        $this->assertNotNull($chat->public_id);
        $this->assertIsString($chat->public_id);
    }
}
