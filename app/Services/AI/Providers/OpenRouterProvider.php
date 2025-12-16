<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Exceptions\AIProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', '');
        $this->model = config('services.openrouter.model', 'openai/gpt-4o-mini');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->maxTokens = config('services.openrouter.max_tokens', 1000);
        $this->temperature = config('services.openrouter.temperature', 0.7);
    }

    /**
     * Генерирует ответ с использованием OpenRouter API.
     *
     * @param array $messages История сообщений
     * @param string|null $systemPrompt Системная инструкция
     * @return string Ответ от OpenRouter
     *
     * @throws AIProviderException
     */
    public function generateResponse(array $messages, ?string $systemPrompt = null): string
    {
        $this->validateConfiguration();

        $requestMessages = $this->prepareMessages($messages, $systemPrompt);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
                ->timeout(60)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $requestMessages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $content = $response->json('choices.0.message.content');

            if (empty($content)) {
                throw new AIProviderException(
                    'Пустой ответ от OpenRouter API',
                    $this->getName(),
                    ['response' => $response->json()]
                );
            }

            return trim($content);
        } catch (AIProviderException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('OpenRouter API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AIProviderException(
                'Ошибка при обращении к OpenRouter API: ' . $e->getMessage(),
                $this->getName(),
                null,
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Возвращает название провайдера.
     */
    public function getName(): string
    {
        return 'openrouter';
    }

    /**
     * Проверяет корректность конфигурации.
     *
     * @throws AIProviderException
     */
    private function validateConfiguration(): void
    {
        if (empty($this->apiKey)) {
            throw new AIProviderException(
                'API ключ OpenRouter не настроен. Установите переменную OPENROUTER_API_KEY в .env файле.',
                $this->getName()
            );
        }
    }

    /**
     * Подготавливает массив сообщений для API.
     */
    private function prepareMessages(array $messages, ?string $systemPrompt): array
    {
        $requestMessages = [];

        if ($systemPrompt) {
            $requestMessages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach ($messages as $message) {
            $requestMessages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        return $requestMessages;
    }

    /**
     * Обрабатывает ошибки API.
     *
     * @throws AIProviderException
     */
    private function handleError($response): void
    {
        $status = $response->status();
        $body = $response->json();
        $errorMessage = $body['error']['message'] ?? 'Неизвестная ошибка';

        Log::error('OpenRouter API Error', [
            'status' => $status,
            'body' => $body,
        ]);

        $message = match ($status) {
            401 => 'Недействительный API ключ OpenRouter',
            429 => 'Превышен лимит запросов к OpenRouter API',
            500, 502, 503 => 'Сервис OpenRouter временно недоступен',
            default => "Ошибка OpenRouter API: {$errorMessage}",
        };

        throw new AIProviderException($message, $this->getName(), [
            'status' => $status,
            'error' => $body,
        ]);
    }
}
