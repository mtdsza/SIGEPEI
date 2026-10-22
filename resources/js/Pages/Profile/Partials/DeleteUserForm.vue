<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header class="border-b border-slate-200 dark:border-zinc-700 pb-3">
            <h2 class="text-sm font-bold text-red-700 dark:text-red-400 uppercase tracking-wider">
                Excluir Conta
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                Uma vez excluída sua conta, todos os recursos e dados vinculados serão permanentemente apagados.
            </p>
        </header>

        <div>
            <button
                type="button"
                @click="confirmUserDeletion"
                class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-semibold rounded-sm shadow-sm uppercase tracking-wider transition-colors"
            >
                Excluir Minha Conta
            </button>
        </div>

        <!-- modal de confirmação de exclusão de conta -->
        <div
            v-if="confirmingUserDeletion"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xl max-w-md w-full p-6 space-y-4 text-xs">
                <div class="border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Confirmar Exclusão de Conta
                    </h3>
                </div>

                <p class="text-slate-600 dark:text-zinc-400">
                    Tem certeza de que deseja excluir sua conta? Esta ação é irreversível. Por favor, digite sua senha para confirmar.
                </p>

                <div class="space-y-1">
                    <label for="delete_password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase">
                        Sua Senha
                    </label>
                    <input
                        id="delete_password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-red-600 focus:border-red-600 dark:text-zinc-100"
                        placeholder="••••••••"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="px-4 py-1.5 bg-red-700 hover:bg-red-800 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Excluir Definitivamente
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
