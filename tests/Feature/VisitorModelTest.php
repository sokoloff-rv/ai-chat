<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorModelTest extends TestCase
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

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $visitor->messages);
    }

    public function test_visitor_can_be_created_with_minimal_data(): void
    {
        $chat = Chat::factory()->create();

        $visitor = Visitor::create([
            'chat_id' => $chat->id,
            'session_id' => 'test-session-123',
        ]);

        $this->assertNotNull($visitor->id);
        $this->assertEquals('test-session-123', $visitor->session_id);
        $this->assertNull($visitor->user_agent);
        $this->assertNull($visitor->ip_address);
        $this->assertNull($visitor->referrer);
    }

    public function test_visitor_stores_all_tracking_data(): void
    {
        $chat = Chat::factory()->create();

        $visitor = Visitor::create([
            'chat_id' => $chat->id,
            'session_id' => 'test-session-123',
            'user_agent' => 'Mozilla/5.0',
            'ip_address' => '127.0.0.1',
            'referrer' => 'https://example.com',
        ]);

        $this->assertEquals('Mozilla/5.0', $visitor->user_agent);
        $this->assertEquals('127.0.0.1', $visitor->ip_address);
        $this->assertEquals('https://example.com', $visitor->referrer);
    }

    public function test_session_id_is_unique(): void
    {
        $chat = Chat::factory()->create();

        Visitor::create([
            'chat_id' => $chat->id,
            'session_id' => 'unique-session',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Visitor::create([
            'chat_id' => $chat->id,
            'session_id' => 'unique-session',
        ]);
    }

    public function test_deleting_chat_cascades_to_visitor(): void
    {
        $chat = Chat::factory()->create();
        $visitor = Visitor::factory()->create(['chat_id' => $chat->id]);

        $chat->delete();

        $this->assertDatabaseMissing('visitors', ['id' => $visitor->id]);
    }
}
