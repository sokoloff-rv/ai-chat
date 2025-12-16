<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    user_instruction: '',
});

const submit = () => {
    form.post(route('chats.store'));
};
</script>

<template>

    <Head title="Создать чат-бота" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('chats.index')" class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Создать чат-бота</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Название бота" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" placeholder="Например: Консультант магазина" required autofocus />
                            <InputError :message="form.errors.name" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Название для вашего удобства, посетители его не&nbsp;видят.
                            </p>
                        </div>

                        <div>
                            <InputLabel for="instruction" value="Инструкции для AI" />
                            <textarea id="instruction" v-model="form.user_instruction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="8" placeholder="Опишите роль и поведение вашего бота. Например: Ты — дружелюбный консультант интернет-магазина электроники. Помогай клиентам выбрать товары, отвечай на вопросы о характеристиках и наличии. Будь вежливым и профессиональным."></textarea>
                            <InputError :message="form.errors.user_instruction" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Чем подробнее инструкции, тем точнее бот будет следовать вашим требованиям.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-4">
                        <Link :href="route('chats.index')">
                            <SecondaryButton type="button">Отмена</SecondaryButton>
                        </Link>
                        <PrimaryButton class="bg-green-600 hover:bg-green-500 focus:bg-green-500 active:bg-green-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Создать бота
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
