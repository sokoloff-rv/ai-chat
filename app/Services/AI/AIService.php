<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Exceptions\AIProviderException;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\MockAIProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\OpenRouterProvider;
use Illuminate\Support\Facades\Log;

class AIService
{
    private AIProviderInterface $provider;

    public function __construct(?AIProviderInterface $provider = null)
    {
        $this->provider = $provider ?? $this->resolveProvider();
    }

    /**
     * Генерирует ответ AI на основе истории сообщений.
     *
     * @param array $messages История сообщений
     * @param string|null $systemPrompt Системная инструкция
     * @return string Ответ AI
     *
     * @throws AIProviderException
     */
    public function generateResponse(array $messages, ?string $systemPrompt = null): string
    {
        try {
            Log::debug('AI Request', [
                'provider' => $this->provider->getName(),
                'messages_count' => count($messages),
                'has_system_prompt' => ! empty($systemPrompt),
            ]);

            $response = $this->provider->generateResponse($messages, $systemPrompt);

            Log::debug('AI Response', [
                'provider' => $this->provider->getName(),
                'response_length' => strlen($response),
            ]);

            return $response;
        } catch (AIProviderException $e) {
            Log::error('AI Provider Error', [
                'provider' => $e->getProvider(),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);

            throw $e;
        }
    }

    /**
     * Возвращает текущего провайдера.
     */
    public function getProvider(): AIProviderInterface
    {
        return $this->provider;
    }

    /**
     * Возвращает название текущего провайдера.
     */
    public function getProviderName(): string
    {
        return $this->provider->getName();
    }

    /**
     * Определяет и создаёт провайдера на основе конфигурации.
     */
    private function resolveProvider(): AIProviderInterface
    {
        $driver = config('services.ai.driver', 'mock');

        return match ($driver) {
            'openai' => app(OpenAIProvider::class),
            'openrouter' => app(OpenRouterProvider::class),
            'gemini' => app(GeminiProvider::class),
            default => app(MockAIProvider::class),
        };
    }
}
