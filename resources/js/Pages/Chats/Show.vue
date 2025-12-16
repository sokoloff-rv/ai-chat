<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    chat: {
        type: Object,
        required: true,
    },
    embedCode: {
        type: String,
        required: true,
    },
});

const copied = ref(false);
const confirmingDeletion = ref(false);

const copyCode = () => {
    navigator.clipboard.writeText(props.embedCode);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const confirmDeletion = () => {
    confirmingDeletion.value = true;
};

const deleteChat = () => {
    router.delete(route('chats.destroy', props.chat.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
};
</script>

<template>

    <Head :title="chat.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('chats.index')" class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ chat.name }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('chats.demo', chat.id)" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm transition hover:bg-indigo-100">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Демо
                    </Link>
                    <Link :href="route('chats.edit', chat.id)" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Редактировать
                    </Link>
                    <button @click="confirmDeletion" class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-50">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Удалить
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Инструкции для AI</h3>
                    <div class="mt-4">
                        <p v-if="chat.user_instruction" class="whitespace-pre-wrap text-gray-600">
                            {{ chat.user_instruction }}
                        </p>
                        <p v-else class="italic text-gray-400">Инструкции не заданы</p>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Код для встраивания</h3>
                        <button @click="copyCode" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-700">
                            <svg v-if="!copied" class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ copied ? 'Скопировано!' : 'Копировать' }}
                        </button>
                    </div>

                    <div class="mt-4">
                        <div class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-sm text-gray-100">
                            <code>{{ embedCode }}</code>
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg bg-blue-50 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800">Как установить виджет</h4>
                                <p class="mt-1 text-sm text-blue-700">
                                    Скопируйте код выше и&nbsp;вставьте его перед закрывающим тегом <code class="rounded bg-blue-100 px-1">&lt;/body&gt;</code> на&nbsp;страницах вашего сайта, где должен отображаться чат-бот.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Информация</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID бота</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ chat.public_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Создан</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ new Date(chat.created_at).toLocaleString('ru-RU') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Удалить чат-бота?</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Вы уверены, что хотите удалить бота «{{ chat.name }}»? Это действие необратимо. Вся история сообщений также будет удалена.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">Отмена</SecondaryButton>
                    <DangerButton @click="deleteChat">Удалить</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
