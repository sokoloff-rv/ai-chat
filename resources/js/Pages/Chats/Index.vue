<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    chats: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <Head title="Мои чат-боты" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Мои чат-боты
                </h2>
                <Link :href="route('chats.create')">
                    <PrimaryButton class="bg-green-600 hover:bg-green-500 focus:bg-green-500 active:bg-green-700">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Создать бота
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="chats.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Нет чат-ботов</h3>
                    <p class="mt-2 text-gray-500">
                        Создайте своего первого AI-ассистента для встраивания на сайт.
                    </p>
                    <div class="mt-6">
                        <Link :href="route('chats.create')">
                            <PrimaryButton>Создать первого бота</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="chat in chats" :key="chat.id" :href="route('chats.show', chat.id)" class="group block rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-gray-300">
                        <div class="flex items-start justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-gray-400 transition group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-indigo-600">
                            {{ chat.name }}
                        </h3>

                        <p class="mt-2 line-clamp-2 text-sm text-gray-500">
                            {{ chat.user_instruction || 'Без инструкций' }}
                        </p>

                        <div class="mt-4 flex items-center text-xs text-gray-400">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ new Date(chat.created_at).toLocaleDateString('ru-RU') }}
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
