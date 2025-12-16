<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;

class MockAIProvider implements AIProviderInterface
{
    /**
     * Генерирует тестовый ответ для разработки и тестирования.
     *
     * @param array $messages История сообщений
     * @param string|null $systemPrompt Системная инструкция
     * @return string Тестовый ответ
     */
    public function generateResponse(array $messages, ?string $systemPrompt = null): string
    {
        $lastMessage = end($messages);
        $userMessage = $lastMessage['content'] ?? 'пустое сообщение';

        $responses = [
            'привет' => 'Здравствуйте! Рад вас видеть. Чем могу помочь?',
            'как дела' => 'Отлично, спасибо! Готов помочь вам с любыми вопросами.',
            'что ты умеешь' => 'Я — AI-ассистент. Вы можете задать мне любой вопрос, и я постараюсь помочь.',
            'пока' => 'До свидания! Буду рад пообщаться снова.',
        ];

        $lowerMessage = mb_strtolower($userMessage);
        foreach ($responses as $keyword => $response) {
            if (str_contains($lowerMessage, $keyword)) {
                return $response;
            }
        }

        $roleInfo = $systemPrompt
            ? "Согласно моим инструкциям, я должен: \"{$this->truncate($systemPrompt, 100)}\"\n\n"
            : '';

        return $roleInfo . "Это тестовый ответ на ваше сообщение: \"{$this->truncate($userMessage, 100)}\"\n\n" .
            "В production-режиме здесь будет реальный ответ от AI. " .
            "Для включения настоящего AI установите переменную окружения AI_DRIVER=openai";
    }

    /**
     * Возвращает название провайдера.
     */
    public function getName(): string
    {
        return 'mock';
    }

    /**
     * Обрезает строку до указанной длины.
     */
    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
