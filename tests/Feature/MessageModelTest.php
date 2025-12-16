<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageModelTest extends TestCase
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

    public function test_message_has_required_fields(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
            'role' => 'user',
            'content' => 'Test message',
        ]);

        $this->assertNotNull($message->id);
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Test message', $message->content);
    }

    public function test_deleting_visitor_cascades_to_messages(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $visitor->delete();

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_can_create_multiple_messages_for_conversation(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        Message::create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
            'role' => 'user',
            'content' => 'Привет',
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
            'role' => 'assistant',
            'content' => 'Здравствуйте!',
        ]);

        $this->assertCount(2, $visitor->messages);
    }
}
