<?php

namespace Tests\Unit\Models;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_belongs_to_chat(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        $this->assertInstanceOf(Chat::class, $visitor->chat);
        $this->assertEquals($chat->id, $visitor->chat->id);
    }

    public function test_visitor_has_many_messages(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);
        $message1 = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);
        $message2 = Message::factory()->create([
            'chat_id' => $chat->id,
            'visitor_id' => $visitor->id,
        ]);

        $this->assertCount(2, $visitor->messages);
        $this->assertTrue($visitor->messages->contains($message1));
        $this->assertTrue($visitor->messages->contains($message2));
    }

    public function test_visitor_fillable_attributes(): void
    {
        $visitor = new Visitor([
            'chat_id' => 1,
            'session_id' => 'test-session-id',
            'user_agent' => 'Mozilla/5.0',
            'ip_address' => '127.0.0.1',
            'referrer' => 'https://example.com',
        ]);

        $this->assertEquals(1, $visitor->chat_id);
        $this->assertEquals('test-session-id', $visitor->session_id);
        $this->assertEquals('Mozilla/5.0', $visitor->user_agent);
        $this->assertEquals('127.0.0.1', $visitor->ip_address);
        $this->assertEquals('https://example.com', $visitor->referrer);
    }

    public function test_visitor_can_be_created_without_optional_fields(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::create([
            'chat_id' => $chat->id,
            'session_id' => 'minimal-session',
        ]);

        $this->assertNull($visitor->user_agent);
        $this->assertNull($visitor->ip_address);
        $this->assertNull($visitor->referrer);
        $this->assertNotNull($visitor->created_at);
    }
}
