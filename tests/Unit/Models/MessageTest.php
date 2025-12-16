<?php

namespace Tests\Unit\Models;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_belongs_to_chat(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $this->assertInstanceOf(Chat::class, $message->chat);
        $this->assertEquals($chat->id, $message->chat->id);
    }

    public function test_message_belongs_to_visitor(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $this->assertInstanceOf(Visitor::class, $message->visitor);
        $this->assertEquals($visitor->id, $message->visitor->id);
    }

    public function test_message_fillable_attributes(): void
    {
        $message = new Message([
            'chat_id' => 1,
            'visitor_id' => 2,
            'role' => 'user',
            'content' => 'Hello, world!',
        ]);

        $this->assertEquals(1, $message->chat_id);
        $this->assertEquals(2, $message->visitor_id);
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello, world!', $message->content);
    }

    public function test_message_can_be_created_with_user_role(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
            'role' => 'user',
            'content' => 'User message',
        ]);

        $this->assertEquals('user', $message->role);
        $this->assertNotNull($message->created_at);
    }

    public function test_message_can_be_created_with_assistant_role(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
            'role' => 'assistant',
            'content' => 'AI response',
        ]);

        $this->assertEquals('assistant', $message->role);
        $this->assertNotNull($message->created_at);
    }
}
