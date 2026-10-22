<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Redefinir Senha" />

        <div class="mb-5">
            <h1 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center">
                Redefinir Senha
            </h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 text-center mt-1">
                Crie uma nova senha de acesso segura para sua conta.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 text-xs">
            <div>
                <label for="email" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    E-mail <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Nova Senha <span class="text-red-500">*</span>
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Confirmar Nova Senha <span class="text-red-500">*</span>
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white font-semibold rounded-sm transition-colors text-xs uppercase tracking-wider shadow-sm disabled:opacity-50"
                >
                    Salvar Nova Senha
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
