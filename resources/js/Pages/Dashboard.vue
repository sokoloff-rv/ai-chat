<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    chatsCount: {
        type: Number,
        default: 0,
    },
});
</script>

<template>

    <Head title="Главная" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Главная
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 p-8 text-white shadow-lg">
                    <h1 class="text-2xl font-bold">
                        Добро пожаловать, {{ $page.props.auth.user.name }}!
                    </h1>
                    <p class="mt-2 text-indigo-100">
                        Создавайте AI-ассистентов и встраивайте их на свои сайты.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="flex flex-col rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Мои чат-боты</p>
                                <p class="text-2xl font-bold text-gray-900">{{ chatsCount }}</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <Link :href="route('chats.index')"">
                                <SecondaryButton class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    Перейти к списку →
                                </SecondaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="flex flex-col rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Быстрое действие</p>
                                <p class="text-lg font-semibold text-gray-900">Создать бота</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <Link :href="route('chats.create')">
                                <PrimaryButton class="bg-green-600 hover:bg-green-500 focus:bg-green-500 active:bg-green-700">
                                    Создать нового бота
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Помощь</p>
                                <p class="text-lg font-semibold text-gray-900">Как это работает</p>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <ol class="list-inside list-decimal space-y-1">
                                <li>Создайте чат-бота</li>
                                <li>Задайте инструкции для AI</li>
                                <li>Скопируйте код виджета</li>
                                <li>Вставьте на свой сайт</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
