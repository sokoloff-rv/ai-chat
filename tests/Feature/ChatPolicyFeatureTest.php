<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatPolicyFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->chat = Chat::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_view_their_own_chat(): void
    {
        $this->assertTrue($this->user->can('view', $this->chat));
    }

    public function test_user_cannot_view_other_users_chat(): void
    {
        $this->assertFalse($this->otherUser->can('view', $this->chat));
    }

    public function test_user_can_update_their_own_chat(): void
    {
        $this->assertTrue($this->user->can('update', $this->chat));
    }

    public function test_user_cannot_update_other_users_chat(): void
    {
        $this->assertFalse($this->otherUser->can('update', $this->chat));
    }

    public function test_user_can_delete_their_own_chat(): void
    {
        $this->assertTrue($this->user->can('delete', $this->chat));
    }

    public function test_user_cannot_delete_other_users_chat(): void
    {
        $this->assertFalse($this->otherUser->can('delete', $this->chat));
    }
}
