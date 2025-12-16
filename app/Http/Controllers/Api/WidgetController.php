<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WidgetController extends Controller
{
    public function __construct(
        private AIService $aiService
    ) {}

    /**
     * Получить конфигурацию чат-бота для виджета.
     */
    public function config(string $publicId): JsonResponse
    {
        $chat = Chat::where('public_id', $publicId)->first();

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        return response()->json([
            'name' => $chat->name,
            'welcome_message' => 'Привет! Чем могу помочь?',
            'allowed_domains' => $chat->allowed_domains,
        ]);
    }

    /**
     * Начать новую сессию чата.
     */
    public function startSession(Request $request, string $publicId): JsonResponse
    {
        $chat = Chat::where('public_id', $publicId)->first();

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
            'session_id' => bin2hex(random_bytes(16)),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('Referer'),
        ]);

        return response()->json([
            'session_id' => $visitor->session_id,
            'chat_name' => $chat->name,
        ]);
    }

    /**
     * Отправить сообщение и получить ответ AI.
     */
    public function sendMessage(Request $request, string $publicId): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $chat = Chat::where('public_id', $publicId)->first();

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        $visitor = $chat->visitors()
            ->where('session_id', $request->session_id)
            ->first();

        if (!$visitor) {
            return response()->json([
                'error' => 'Сессия не найдена',
            ], 404);
        }

        $userMessage = $visitor->messages()->create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $request->message,
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
                'error' => 'Произошла ошибка при обработке запроса: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить историю сообщений сессии.
     */
    public function getHistory(Request $request, string $publicId): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $chat = Chat::where('public_id', $publicId)->first();

        if (!$chat) {
            return response()->json([
                'error' => 'Чат-бот не найден',
            ], 404);
        }

        $visitor = $chat->visitors()
            ->where('session_id', $request->session_id)
            ->first();

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
        if (empty($chat->allowed_domains)) {
            return true;
        }

        $referer = $request->header('Referer');
        if (!$referer) {
            return false;
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);
        if (!$refererHost) {
            return false;
        }

        $allowedDomains = array_map('trim', explode("\n", $chat->allowed_domains));

        foreach ($allowedDomains as $domain) {
            if (empty($domain)) {
                continue;
            }

            if ($refererHost === $domain || str_ends_with($refererHost, '.' . $domain)) {
                return true;
            }
        }

        return false;
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
