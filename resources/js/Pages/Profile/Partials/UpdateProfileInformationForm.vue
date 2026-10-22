<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="border-b border-slate-200 dark:border-zinc-700 pb-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Informações do Perfil
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                Atualize seu nome e endereço de e-mail institucional.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-5 space-y-4 text-xs"
        >
            <div>
                <label for="profile_name" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input
                    id="profile_name"
                    type="text"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <label for="profile_email" class="block font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                    E-mail Institucional <span class="text-red-500">*</span>
                </label>
                <input
                    id="profile_email"
                    type="email"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-100"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">
                    Seu endereço de e-mail ainda não foi verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline hover:text-amber-900 dark:hover:text-amber-300 ml-1 font-semibold"
                    >
                        Clique aqui para reenviar o e-mail de verificação.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                >
                    Um novo link de verificação foi enviado para seu e-mail.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm uppercase tracking-wider disabled:opacity-50"
                >
                    Salvar Alterações
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
                        Informações salvas com sucesso.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
