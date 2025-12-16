<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateChatRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateChatRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_name_is_required(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertContains('required', $rules['name']);
    }

    public function test_name_must_be_string(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertContains('string', $rules['name']);
    }

    public function test_name_has_max_length(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertContains('max:255', $rules['name']);
    }

    public function test_user_instruction_is_nullable(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertArrayHasKey('user_instruction', $rules);
        $this->assertContains('nullable', $rules['user_instruction']);
    }

    public function test_user_instruction_has_max_length(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertContains('max:5000', $rules['user_instruction']);
    }

    public function test_allowed_domains_is_nullable_array(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertArrayHasKey('allowed_domains', $rules);
        $this->assertContains('nullable', $rules['allowed_domains']);
        $this->assertContains('array', $rules['allowed_domains']);
    }

    public function test_allowed_domains_items_are_validated(): void
    {
        $request = new UpdateChatRequest();

        $rules = $request->rules();

        $this->assertArrayHasKey('allowed_domains.*', $rules);
        $this->assertContains('string', $rules['allowed_domains.*']);
        $this->assertContains('max:255', $rules['allowed_domains.*']);
    }

    public function test_authorize_returns_true(): void
    {
        $request = new UpdateChatRequest();

        $this->assertTrue($request->authorize());
    }

    public function test_has_custom_messages(): void
    {
        $request = new UpdateChatRequest();

        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);
        $this->assertArrayHasKey('user_instruction.max', $messages);
    }
}
