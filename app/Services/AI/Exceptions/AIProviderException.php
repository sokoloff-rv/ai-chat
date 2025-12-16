<?php

namespace App\Services\AI\Exceptions;

use Exception;

class AIProviderException extends Exception
{
    protected string $provider;
    protected ?array $context;

    public function __construct(
        string $message,
        string $provider = 'unknown',
        ?array $context = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->provider = $provider;
        $this->context = $context;
    }

    /**
     * Возвращает название провайдера, вызвавшего ошибку.
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Возвращает дополнительный контекст ошибки.
     */
    public function getContext(): ?array
    {
        return $this->context;
    }
}
