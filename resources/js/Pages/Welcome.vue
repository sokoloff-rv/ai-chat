<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>

    <Head title="AI Chat — Конструктор чат-ботов" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-white to-purple-100">
        <nav class="mx-auto max-w-7xl px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">AI Chat</span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('chats.index')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        Мои чат-боты
                    </Link>

                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-medium text-gray-700 transition hover:text-indigo-600">
                            Войти
                        </Link>

                        <Link v-if="canRegister" :href="route('register')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                            Зарегистрироваться
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-6 py-16 lg:py-24">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                    Создайте своего
                    <span class="text-indigo-600">AI-ассистента</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                    Конструктор персонализированных чат-ботов для вашего сайта. Настройте инструкции, получите код и&nbsp;встройте умного помощника к&nbsp;себе на&nbsp;сайт за&nbsp;несколько минут.
                </p>

                <div v-if="!$page.props.auth.user" class="mt-10 flex items-center justify-center gap-4">
                    <Link v-if="canRegister" :href="route('register')" class="rounded-lg bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-lg transition hover:bg-indigo-700 hover:shadow-xl">
                        Начать бесплатно
                    </Link>
                    <Link v-if="canLogin" :href="route('login')" class="rounded-lg border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow transition hover:bg-gray-50">
                        Войти в аккаунт
                    </Link>
                </div>

                <div v-else class="mt-10">
                    <Link :href="route('chats.index')" class="rounded-lg bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-lg transition hover:bg-indigo-700 hover:shadow-xl">
                        Перейти к чат-ботам
                    </Link>
                </div>
            </div>

            <div class="mt-24 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-8 shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Персональные инструкции</h3>
                    <p class="mt-2 text-gray-600">
                        Задайте роль и поведение бота: фитнес-тренер, консультант магазина, техподдержка — всё, что угодно.
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Простое встраивание</h3>
                    <p class="mt-2 text-gray-600">
                        Один скрипт — и виджет появится на вашем сайте. Shadow DOM изолирует стили от конфликтов.
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">История переписки</h3>
                    <p class="mt-2 text-gray-600">
                        Посетители продолжают диалог даже после перезагрузки страницы. Вся история сохраняется.
                    </p>
                </div>
            </div>
        </main>

        <footer class="border-t border-gray-200 bg-white/50">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <p class="text-center text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} AI&nbsp;Chat. Создано с ❤️ на&nbsp;Laravel и&nbsp;Vue.
                </p>
            </div>
        </footer>
    </div>
</template>
