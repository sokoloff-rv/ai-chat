<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * Отображает список чат-ботов пользователя.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $chats = $user->chats()->latest()->get();

        return Inertia::render('Chats/Index', [
            'chats' => $chats,
        ]);
    }

    /**
     * Отображает форму создания нового чат-бота.
     */
    public function create(): Response
    {
        return Inertia::render('Chats/Create');
    }

    /**
     * Сохраняет нового чат-бота в базе данных.
     */
    public function store(StoreChatRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $chat = Auth::user()->chats()->create([
            'public_id' => Str::uuid(),
            'name' => $validated['name'],
            'user_instruction' => $validated['user_instruction'] ?? null,
            'allowed_domains' => $validated['allowed_domains'] ?? null,
        ]);

        return redirect()
            ->route('chats.show', $chat)
            ->with('success', 'Чат-бот успешно создан!');
    }

    /**
     * Отображает страницу чат-бота с кодом для встраивания.
     */
    public function show(Chat $chat): Response
    {
        $this->authorize('view', $chat);

        return Inertia::render('Chats/Show', [
            'chat' => $chat,
            'embedCode' => $this->generateEmbedCode($chat),
        ]);
    }

    /**
     * Отображает форму редактирования чат-бота.
     */
    public function edit(Chat $chat): Response
    {
        $this->authorize('update', $chat);

        return Inertia::render('Chats/Edit', [
            'chat' => $chat,
        ]);
    }

    /**
     * Обновляет настройки чат-бота.
     */
    public function update(UpdateChatRequest $request, Chat $chat): RedirectResponse
    {
        $this->authorize('update', $chat);

        $chat->update($request->validated());

        return redirect()
            ->route('chats.show', $chat)
            ->with('success', 'Настройки чат-бота обновлены!');
    }

    /**
     * Удаляет чат-бота.
     */
    public function destroy(Chat $chat): RedirectResponse
    {
        $this->authorize('delete', $chat);

        $chat->delete();

        return redirect()
            ->route('chats.index')
            ->with('success', 'Чат-бот удален!');
    }

    /**
     * Генерирует HTML-код для встраивания виджета на сайт.
     */
    private function generateEmbedCode(Chat $chat): string
    {
        return sprintf(
            '<script src="%s/widget.js" data-chat-id="%s"></script>',
            config('app.url'),
            $chat->public_id
        );
    }
}
