<?php

namespace Tests\Unit\Models;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $chat->user);
        $this->assertEquals($user->id, $chat->user->id);
    }

    public function test_chat_has_many_messages(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message1 = Message::factory()->create(['chat_id' => $chat->id, 'visitor_id' => $visitor->id]);
        $message2 = Message::factory()->create(['chat_id' => $chat->id, 'visitor_id' => $visitor->id]);

        $this->assertCount(2, $chat->messages);
        $this->assertTrue($chat->messages->contains($message1));
        $this->assertTrue($chat->messages->contains($message2));
    }

    public function test_chat_has_many_visitors(): void
    {
        $chat = Chat::factory()->create();
        $visitor1 = Visitor::factory()->create(['chat_id' => $chat->id]);
        $visitor2 = Visitor::factory()->create(['chat_id' => $chat->id]);

        $this->assertCount(2, $chat->visitors);
        $this->assertTrue($chat->visitors->contains($visitor1));
        $this->assertTrue($chat->visitors->contains($visitor2));
    }

    public function test_chat_fillable_attributes(): void
    {
        $chat = new Chat([
            'public_id' => 'test-id',
            'name' => 'Test Chat',
            'user_id' => 1,
            'user_instruction' => 'Test instruction',
            'allowed_domains' => 'example.com',
        ]);

        $this->assertEquals('test-id', $chat->public_id);
        $this->assertEquals('Test Chat', $chat->name);
        $this->assertEquals(1, $chat->user_id);
        $this->assertEquals('Test instruction', $chat->user_instruction);
        $this->assertEquals('example.com', $chat->allowed_domains);
    }

    public function test_chat_can_be_created_without_optional_fields(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'public_id' => 'test-id',
            'name' => 'Minimal Chat',
            'user_id' => $user->id,
        ]);

        $this->assertNull($chat->user_instruction);
        $this->assertNull($chat->allowed_domains);
        $this->assertNotNull($chat->created_at);
        $this->assertNotNull($chat->updated_at);
    }
}
