<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatModelFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $chat->user);
        $this->assertEquals($user->id, $chat->user->id);
    }

    public function test_chat_has_many_visitors(): void
    {
        $chat = Chat::factory()->create();
        Visitor::factory()->count(3)->create(['chat_id' => $chat->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $chat->visitors);
        $this->assertCount(3, $chat->visitors);
    }

    public function test_chat_has_many_messages(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        Message::factory()->count(5)->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $chat->messages);
        $this->assertCount(5, $chat->messages);
    }

    public function test_allowed_domains_is_cast_to_array(): void
    {
        $chat = Chat::factory()->create([
            'allowed_domains' => ['example.com', 'test.com'],
        ]);

        $this->assertIsArray($chat->allowed_domains);
        $this->assertEquals(['example.com', 'test.com'], $chat->allowed_domains);
    }

    public function test_chat_can_be_created_with_minimal_data(): void
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'test-id-123',
            'name' => 'Test Chat',
        ]);

        $this->assertNotNull($chat->id);
        $this->assertEquals('Test Chat', $chat->name);
        $this->assertNull($chat->user_instruction);
        $this->assertNull($chat->allowed_domains);
    }

    public function test_deleting_chat_cascades_to_visitors(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        $chat->delete();

        $this->assertDatabaseMissing('visitors', ['id' => $visitor->id]);
    }

    public function test_deleting_chat_cascades_to_messages(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $chat->delete();

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_public_id_is_fillable(): void
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'user_id' => $user->id,
            'public_id' => 'custom-public-id',
            'name' => 'Test',
        ]);

        $this->assertEquals('custom-public-id', $chat->public_id);
    }
}
