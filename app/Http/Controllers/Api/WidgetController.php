<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AI\AIService;
use App\Services\DomainValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    public function __construct(
        private AIService $aiService,
        private DomainValidator $domainValidator
    ) {}

    /**
     * Получить конфигурацию чат-бота для виджета.
     */
    public function config(string $publicId): JsonResponse
    {
        $chat = $this->findChatByPublicId($publicId);

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        return response()->json([
            'name' => e($chat->name),
            'welcome_message' => 'Привет! Чем могу помочь?',
            'allowed_domains' => $chat->getAllowedDomainsList(),
        ]);
    }

    /**
     * Начать новую сессию чата.
     */
    public function startSession(Request $request, string $publicId): JsonResponse
    {
        $key = 'session:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Слишком много запросов. Попробуйте позже.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $chat = $this->findChatByPublicId($publicId);

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        if (!$this->isAllowedDomain($chat, $request)) {
            return response()->json([
                'error' => 'Домен не разрешен',
            ], 403);
        }

        $visitor = $chat->visitors()->create([
            'session_id' => Str::uuid()->toString(),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'referrer' => substr($request->header('Referer') ?? '', 0, 500),
        ]);

        return response()->json([
            'session_id' => $visitor->session_id,
            'chat_name' => e($chat->name),
        ]);
    }

    /**
     * Отправить сообщение и получить ответ AI.
     */
    public function sendMessage(Request $request, string $publicId): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string|uuid',
            'message' => 'required|string|max:2000',
        ]);

        $key = 'message:' . $request->session_id;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'error' => 'Слишком много сообщений. Подождите минуту.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $chat = $this->findChatByPublicId($publicId);

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        $visitor = $this->findVisitorBySession($chat, $request->session_id);

        if (!$visitor) {
            return response()->json([
                'error' => 'Сессия не найдена',
            ], 404);
        }

        $sanitizedMessage = $this->sanitizeMessage($request->message);

        $userMessage = $visitor->messages()->create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $sanitizedMessage,
        ]);

        try {
            $history = $this->buildMessageHistory($visitor, $chat);

            $aiResponse = $this->aiService->generateResponse($history, $chat->user_instruction);

            $assistantMessage = $visitor->messages()->create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $aiResponse,
            ]);

            return response()->json([
                'message' => $aiResponse,
                'message_id' => $assistantMessage->id,
            ]);
        } catch (\App\Services\AI\Exceptions\AIProviderException $e) {
            Log::error('AI Provider Error', [
                'chat_id' => $chat->id,
                'provider' => $e->getProvider(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Throwable $e) {
            Log::error('AI Error', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Произошла внутренняя ошибка сервера. Пожалуйста, попробуйте позже.',
            ], 500);
        }
    }

    /**
     * Получить историю сообщений сессии.
     */
    public function getHistory(Request $request, string $publicId): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string|uuid',
        ]);

        $chat = $this->findChatByPublicId($publicId);

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        $visitor = $this->findVisitorBySession($chat, $request->session_id);

        if (!$visitor) {
            return response()->json([
                'error' => 'Сессия не найдена',
            ], 404);
        }

        $messages = $visitor->messages()
            ->select('role', 'content', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Проверяет, разрешён ли домен для данного чат-бота.
     */
    private function isAllowedDomain(Chat $chat, Request $request): bool
    {
        return $this->domainValidator->isAllowed(
            $chat->getAllowedDomainsList(),
            $request->header('Referer')
        );
    }

    /**
     * Находит чат по публичному ID.
     */
    private function findChatByPublicId(string $publicId): ?Chat
    {
        return Chat::where('public_id', $publicId)->first();
    }

    /**
     * Находит посетителя по ID сессии.
     */
    private function findVisitorBySession(Chat $chat, string $sessionId)
    {
        return $chat->visitors()->where('session_id', $sessionId)->first();
    }

    /**
     * Санитизирует сообщение для защиты от XSS.
     */
    private function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $message);
        $message = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $message);
        $message = preg_replace('/on\w+\s*=\s*["\'].*?["\']/i', '', $message);
        $message = strip_tags($message);

        return trim($message);
    }

    /**
     * Формирует историю сообщений для отправки в AI.
     */
    private function buildMessageHistory($visitor, Chat $chat): array
    {
        $messages = $visitor->messages()
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(fn($msg) => [
            'role' => $msg->role,
            'content' => $msg->content,
        ])->toArray();
    }
}
