<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificação de E-mail" />

        <div class="mb-5">
            <h1 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center">
                Verificação de E-mail
            </h1>
            <p class="text-xs text-slate-500 dark:text-zinc-400 text-center mt-1">
                Por favor, confirme seu endereço de e-mail clicando no link enviado. Caso não tenha recebido, podemos enviar um novo link.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-xs font-medium text-emerald-800 dark:text-emerald-300 rounded-sm"
        >
            Um novo link de verificação foi enviado para o endereço de e-mail informado.
        </div>

        <form @submit.prevent="submit" class="space-y-4 text-xs">
            <div class="flex items-center justify-between pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="py-2 px-4 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white font-semibold rounded-sm transition-colors text-xs uppercase tracking-wider shadow-sm disabled:opacity-50"
                >
                    Reenviar E-mail de Verificação
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:underline font-medium"
                >
                    Deslogar
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
