<?php

namespace Tests\Unit\Policies;

use App\Models\Chat;
use App\Models\User;
use App\Policies\ChatPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ChatPolicy $policy;
    private User $user;
    private User $otherUser;
    private Chat $userChat;
    private Chat $otherUserChat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ChatPolicy();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->userChat = Chat::factory()->create(['user_id' => $this->user->id]);
        $this->otherUserChat = Chat::factory()->create(['user_id' => $this->otherUser->id]);
    }

    public function test_user_can_view_their_own_chat(): void
    {
        $result = $this->policy->view($this->user, $this->userChat);

        $this->assertTrue($result);
    }

    public function test_user_cannot_view_other_users_chat(): void
    {
        $result = $this->policy->view($this->user, $this->otherUserChat);

        $this->assertFalse($result);
    }

    public function test_user_can_update_their_own_chat(): void
    {
        $result = $this->policy->update($this->user, $this->userChat);

        $this->assertTrue($result);
    }

    public function test_user_cannot_update_other_users_chat(): void
    {
        $result = $this->policy->update($this->user, $this->otherUserChat);

        $this->assertFalse($result);
    }

    public function test_user_can_delete_their_own_chat(): void
    {
        $result = $this->policy->delete($this->user, $this->userChat);

        $this->assertTrue($result);
    }

    public function test_user_cannot_delete_other_users_chat(): void
    {
        $result = $this->policy->delete($this->user, $this->otherUserChat);

        $this->assertFalse($result);
    }
}
