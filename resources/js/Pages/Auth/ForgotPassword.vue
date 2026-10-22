<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    status: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <GuestLayout>
        <Head title="Recuperação de Senha" />

        <div class="mb-5">
            <h1 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center">
                Recuperação de Senha
            </h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 text-center mt-1">
                Orientações para recuperação e redefinição de acesso
            </p>
        </div>

        <div v-if="status" class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-xs font-medium text-emerald-800 dark:text-emerald-300 rounded-sm">
            {{ status }}
        </div>

        <div class="space-y-4 text-xs">
            <div class="p-4 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-sm text-slate-700 dark:text-zinc-300 space-y-2.5">
                <div class="flex items-center space-x-2 text-amber-800 dark:text-amber-300 font-semibold text-xs uppercase tracking-wide">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Envio por E-mail Desativado</span>
                </div>

                <p class="leading-relaxed">
                    O envio automático de links de redefinição por e-mail está temporariamente desativado no SIGEPEI.
                </p>

                <p class="leading-relaxed">
                    Para recuperar ou redefinir a sua senha, por favor entre em contato diretamente com o <strong class="text-slate-900 dark:text-white">Administrador do Sistema</strong> informando seu nome e e-mail institucional.
                </p>

                <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                    O administrador realizará a atualização da sua credencial diretamente no módulo de gerenciamento de usuários.
                </p>
            </div>

            <div class="pt-2 flex items-center justify-center">
                <Link
                    :href="route('login')"
                    class="w-full text-center py-2.5 px-4 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-semibold rounded-sm transition-colors text-xs uppercase tracking-wider shadow-sm"
                >
                    Voltar para a Página de Login
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

