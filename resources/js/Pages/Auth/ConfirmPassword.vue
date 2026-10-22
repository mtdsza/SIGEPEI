<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmação de Senha" />

        <div class="mb-5">
            <h1 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center">
                Confirmação de Segurança
            </h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 text-center mt-1">
                Esta é uma área segura do sistema. Por favor, confirme sua senha antes de continuar.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 text-xs">
            <div>
                <label for="password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Sua Senha Atual <span class="text-red-500">*</span>
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white font-semibold rounded-sm transition-colors text-xs uppercase tracking-wider shadow-sm disabled:opacity-50"
                >
                    Confirmar Senha
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
