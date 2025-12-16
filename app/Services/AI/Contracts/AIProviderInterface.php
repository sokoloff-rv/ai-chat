<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Генерирует ответ AI на основе истории сообщений.
     *
     * @param array $messages История сообщений в формате [['role' => 'user|assistant', 'content' => '...']]
     * @param string|null $systemPrompt Системная инструкция для AI
     * @return string Ответ AI
     *
     * @throws \App\Services\AI\Exceptions\AIProviderException
     */
    public function generateResponse(array $messages, ?string $systemPrompt = null): string;

    /**
     * Возвращает название провайдера.
     *
     * @return string
     */
    public function getName(): string;
}
