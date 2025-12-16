<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIService;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\MockAIProvider;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    public function test_mock_provider_returns_response(): void
    {
        $service = new AIService(new MockAIProvider());

        $response = $service->generateResponse([
            ['role' => 'user', 'content' => 'Привет!'],
        ]);

        $this->assertNotEmpty($response);
        $this->assertIsString($response);
    }

    public function test_mock_provider_responds_to_greeting(): void
    {
        $service = new AIService(new MockAIProvider());

        $response = $service->generateResponse([
            ['role' => 'user', 'content' => 'Привет!'],
        ]);

        $this->assertStringContainsString('Здравствуйте', $response);
    }

    public function test_mock_provider_responds_to_how_are_you(): void
    {
        $service = new AIService(new MockAIProvider());

        $response = $service->generateResponse([
            ['role' => 'user', 'content' => 'как дела?'],
        ]);

        $this->assertStringContainsString('Отлично', $response);
    }

    public function test_mock_provider_includes_system_prompt_info(): void
    {
        $service = new AIService(new MockAIProvider());

        $response = $service->generateResponse(
            [['role' => 'user', 'content' => 'Расскажи о себе']],
            'Ты — фитнес-тренер'
        );

        $this->assertStringContainsString('фитнес-тренер', $response);
    }

    public function test_service_returns_provider_name(): void
    {
        $service = new AIService(new MockAIProvider());

        $this->assertEquals('mock', $service->getProviderName());
    }

    public function test_service_returns_provider_instance(): void
    {
        $mockProvider = new MockAIProvider();
        $service = new AIService($mockProvider);

        $this->assertInstanceOf(AIProviderInterface::class, $service->getProvider());
        $this->assertSame($mockProvider, $service->getProvider());
    }

    public function test_mock_provider_handles_conversation_history(): void
    {
        $service = new AIService(new MockAIProvider());

        $response = $service->generateResponse([
            ['role' => 'user', 'content' => 'Привет!'],
            ['role' => 'assistant', 'content' => 'Здравствуйте!'],
            ['role' => 'user', 'content' => 'Помощь'],
        ]);

        $this->assertStringContainsString('AI-ассистент', $response);
    }

    public function test_default_provider_is_mock(): void
    {
        config(['services.ai.driver' => 'mock']);

        $service = app(AIService::class);

        $this->assertEquals('mock', $service->getProviderName());
    }
}
