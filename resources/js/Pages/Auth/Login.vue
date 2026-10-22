<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showForgotModal = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Acesso ao Sistema" />

        <div class="mb-5">
            <h1 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center">
                Acesso ao Sistema
            </h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 text-center mt-0.5">
                Informe suas credenciais para entrar no SIGEPEI
            </p>
        </div>

        <div v-if="status" class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-xs font-medium text-emerald-800 dark:text-emerald-300 rounded-sm">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4 text-xs">
            <!-- campo de e-mail institucional -->
            <div>
                <label for="email" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    E-mail Institucional <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="exemplo@sigepei.local"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- campo de senha de acesso -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase">
                        Senha de Acesso <span class="text-red-500">*</span>
                    </label>
                    <button
                        type="button"
                        @click="showForgotModal = true"
                        class="text-[11px] text-[#006633] dark:text-emerald-400 hover:underline cursor-pointer font-medium"
                    >
                        Esqueceu a senha?
                    </button>
                </div>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <!-- opção de manter conectado -->
            <div class="pt-1">
                <label class="inline-flex items-center space-x-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                    />
                    <span class="text-slate-600 dark:text-zinc-400">Manter conectado neste dispositivo</span>
                </label>
            </div>

            <!-- botão de envio do formulário de login -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-semibold rounded-sm transition-colors text-xs uppercase tracking-wider shadow-sm disabled:opacity-50"
                >
                    Entrar no Sistema
                </button>
            </div>
        </form>

        <!-- modal informativo de recuperação de senha -->
        <Modal :show="showForgotModal" max-width="md" @close="showForgotModal = false">
            <div class="p-6 space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700 flex items-center justify-center shrink-0 text-amber-700 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Recuperação de Senha
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Orientações para redefinição do seu acesso
                        </p>
                    </div>
                </div>

                <div class="p-3 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-sm text-xs text-slate-700 dark:text-zinc-300 space-y-2">
                    <p class="font-medium text-amber-900 dark:text-amber-200">
                        O envio automático de e-mails para recuperação de senha está desativado no momento.
                    </p>
                    <p>
                        Para redefinir a sua senha, por favor entre em contato diretamente com o <strong class="text-slate-900 dark:text-white">Administrador do Sistema</strong>.
                    </p>
                    <p class="text-[11px] text-slate-600 dark:text-zinc-400">
                        O administrador poderá atualizar sua senha diretamente através do painel de gerenciamento de usuários.
                    </p>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="showForgotModal = false"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors uppercase tracking-wider"
                    >
                        Entendido
                    </button>
                </div>
            </div>
        </Modal>
    </GuestLayout>
</template>
