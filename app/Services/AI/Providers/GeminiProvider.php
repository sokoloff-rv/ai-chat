<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Exceptions\AIProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
        $this->maxTokens = config('services.gemini.max_tokens', 1000);
        $this->temperature = config('services.gemini.temperature', 0.7);
    }

    /**
     * Генерирует ответ с использованием Google Gemini API.
     *
     * @param  array  $messages  История сообщений
     * @param  string|null  $systemPrompt  Системная инструкция
     * @return string Ответ от Gemini
     *
     * @throws AIProviderException
     */
    public function generateResponse(array $messages, ?string $systemPrompt = null): string
    {
        $this->validateConfiguration();

        $contents = $this->prepareContents($messages);
        $systemInstruction = $systemPrompt ? ['parts' => [['text' => $systemPrompt]]] : null;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

            $requestBody = [
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ],
            ];

            if ($systemInstruction) {
                $requestBody['systemInstruction'] = $systemInstruction;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post($url . '?key=' . $this->apiKey, $requestBody);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $content = $response->json('candidates.0.content.parts.0.text');

            if (empty($content)) {
                throw new AIProviderException(
                    'Пустой ответ от Gemini API',
                    $this->getName(),
                    ['response' => $response->json()]
                );
            }

            return trim($content);
        } catch (AIProviderException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gemini API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AIProviderException(
                'Ошибка при обращении к Gemini API: ' . $e->getMessage(),
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
        return 'gemini';
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
                'API ключ Gemini не настроен. Установите переменную GEMINI_API_KEY в .env файле.',
                $this->getName()
            );
        }
    }

    /**
     * Подготавливает массив сообщений для Gemini API.
     * Gemini использует формат contents с ролями 'user' и 'model'.
     */
    private function prepareContents(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            // Gemini использует 'model' вместо 'assistant'
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']],
                ],
            ];
        }

        return $contents;
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

        Log::error('Gemini API Error', [
            'status' => $status,
            'body' => $body,
        ]);

        $message = match ($status) {
            400 => "Некорректный запрос к Gemini API: {$errorMessage}",
            401, 403 => 'Недействительный API ключ Gemini',
            429 => 'Превышен лимит запросов к Gemini API',
            500, 502, 503 => 'Сервис Gemini временно недоступен',
            default => "Ошибка Gemini API: {$errorMessage}",
        };

        throw new AIProviderException($message, $this->getName(), [
            'status' => $status,
            'error' => $body,
        ]);
    }
}
