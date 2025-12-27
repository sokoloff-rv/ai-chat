<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class OpenAIProviderLogicTest extends TestCase
{
    /**
     * Проверяет, что модели рассуждений (начиная с o1-) используют max_completion_tokens и не учитывают temperature.
     */
    public function test_reasoning_model_uses_correct_parameters()
    {
        Config::set('services.openai.api_key', 'test-key');
        Config::set('services.openai.model', 'o1-preview');
        Config::set('services.openai.max_completion_tokens', 2000);
        Config::set('services.openai.temperature', 0.7);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Test response']]]
            ], 200),
        ]);

        $provider = new OpenAIProvider();
        $provider->generateResponse([['role' => 'user', 'content' => 'Hello']]);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['model'] === 'o1-preview' &&
                isset($data['max_completion_tokens']) &&
                $data['max_completion_tokens'] === 2000 &&
                !isset($data['max_tokens']) &&
                !isset($data['temperature']);
        });
    }

    /**
     * Проверяет, что стандартные модели используют значения max_tokens и temperature.
     */
    public function test_standard_model_uses_correct_parameters()
    {
        Config::set('services.openai.api_key', 'test-key');
        Config::set('services.openai.model', 'gpt-4o');
        Config::set('services.openai.max_completion_tokens', 1500); // Config name is max_completion_tokens, but used as max_tokens
        Config::set('services.openai.temperature', 0.5);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Test response']]]
            ], 200),
        ]);

        $provider = new OpenAIProvider();
        $provider->generateResponse([['role' => 'user', 'content' => 'Hello']]);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['model'] === 'gpt-4o' &&
                isset($data['max_tokens']) &&
                $data['max_tokens'] === 1500 &&
                !isset($data['max_completion_tokens']) &&
                isset($data['temperature']) &&
                $data['temperature'] === 0.5;
        });
    }

    /**
     * Проверяет, рассматривается ли gpt-5-mini как модель для рассуждений.
     */
    public function test_gpt5_mini_is_treated_as_reasoning_model()
    {
        Config::set('services.openai.api_key', 'test-key');
        Config::set('services.openai.model', 'gpt-5-mini');
        Config::set('services.openai.max_completion_tokens', 1000);
        Config::set('services.openai.temperature', 0.9);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Test response']]]
            ], 200),
        ]);

        $provider = new OpenAIProvider();
        $provider->generateResponse([['role' => 'user', 'content' => 'Hello']]);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['model'] === 'gpt-5-mini' &&
                isset($data['max_completion_tokens']) &&
                !isset($data['max_tokens']) &&
                !isset($data['temperature']);
        });
    }

    /**
     * Проверяет, что модель gpt-4o-mini считается стандартной и использует max_tokens и temperature.
     */
    public function test_gpt4o_mini_is_treated_as_standard_model()
    {
        Config::set('services.openai.api_key', 'test-key');
        Config::set('services.openai.model', 'gpt-4o-mini');
        Config::set('services.openai.max_completion_tokens', 1200);
        Config::set('services.openai.temperature', 0.3);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Test response']]]
            ], 200),
        ]);

        $provider = new OpenAIProvider();
        $provider->generateResponse([['role' => 'user', 'content' => 'Hello']]);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['model'] === 'gpt-4o-mini' &&
                isset($data['max_tokens']) &&
                $data['max_tokens'] === 1200 &&
                !isset($data['max_completion_tokens']) &&
                isset($data['temperature']) &&
                $data['temperature'] === 0.3;
        });
    }
}
