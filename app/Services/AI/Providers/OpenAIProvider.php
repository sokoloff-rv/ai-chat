<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Exceptions\AIProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->model = config('services.openai.model', 'gpt-5-mini');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
        $this->maxTokens = config('services.openai.max_completion_tokens', 1000);
        $this->temperature = config('services.openai.temperature', 0.7);
    }

    /**
     * Генерирует ответ с использованием OpenAI API.
     *
     * @param array $messages История сообщений
     * @param string|null $systemPrompt Системная инструкция
     * @return string Ответ от OpenAI
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
            ])
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $requestMessages,
                    'max_completion_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $content = $response->json('choices.0.message.content');

            if (empty($content)) {
                throw new AIProviderException(
                    'Пустой ответ от OpenAI API',
                    $this->getName(),
                    ['response' => $response->json()]
                );
            }

            return trim($content);
        } catch (AIProviderException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AIProviderException(
                'Ошибка при обращении к OpenAI API: ' . $e->getMessage(),
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
        return 'openai';
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
                'API ключ OpenAI не настроен. Установите переменную OPENAI_API_KEY в .env файле.',
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

        Log::error('OpenAI API Error', [
            'status' => $status,
            'body' => $body,
        ]);

        $message = match ($status) {
            401 => 'Недействительный API ключ OpenAI',
            429 => 'Превышен лимит запросов к OpenAI API',
            500, 502, 503 => 'Сервис OpenAI временно недоступен',
            default => "Ошибка OpenAI API: {$errorMessage}",
        };

        throw new AIProviderException($message, $this->getName(), [
            'status' => $status,
            'error' => $body,
        ]);
    }
}
