<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useUnsavedChanges } from '@/composables/useUnsavedChanges';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

useUnsavedChanges(form);

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="border-b border-slate-200 dark:border-zinc-700 pb-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Alterar Senha
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                Certifique-se de que sua conta está utilizando uma senha longa e segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-5 space-y-4 text-xs">
            <div>
                <label for="current_password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Senha Atual <span class="text-red-500">*</span>
                </label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                    autocomplete="current-password"
                />
                <InputError
                    :message="form.errors.current_password"
                    class="mt-1"
                />
            </div>

            <div>
                <label for="new_password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Nova Senha <span class="text-red-500">*</span>
                </label>
                <input
                    id="new_password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <div>
                <label for="new_password_confirmation" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Confirmar Nova Senha <span class="text-red-500">*</span>
                </label>
                <input
                    id="new_password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                    autocomplete="new-password"
                />
                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-1"
                />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm uppercase tracking-wider disabled:opacity-50"
                >
                    Atualizar Senha
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                    >
                        Senha alterada com sucesso.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
