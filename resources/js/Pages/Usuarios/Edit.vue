<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isSelf = computed(() => page.props.auth?.user?.id === props.user.id);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    cargo: props.user.cargo || 'Professor',
    is_admin: props.user.is_admin ? 1 : 0,
    password: '',
});

const submit = () => {
    form.put(route('usuarios.update', props.user.id));
};
</script>

<template>
    <Head :title="`Editar Usuário - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Editar Usuário - {{ user.name }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        {{ user.email }}
                    </p>
                </div>
                <Link
                    :href="route('usuarios.index')"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                >
                    Voltar para Lista
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.name" class="text-xs text-red-600 mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            v-model="form.email"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.email" class="text-xs text-red-600 mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Cargo <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.cargo"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="NAI">NAI</option>
                            <option value="Professor">Professor</option>
                            <option value="Outros">Outros</option>
                        </select>
                        <div v-if="form.errors.cargo" class="text-xs text-red-600 mt-1">
                            {{ form.errors.cargo }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            É Administrador? <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.is_admin"
                            :disabled="isSelf"
                            :class="[
                                'w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600',
                                isSelf ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-zinc-800' : ''
                            ]"
                        >
                            <option :value="0">Não</option>
                            <option :value="1">Sim</option>
                        </select>
                        <p v-if="isSelf" class="text-[11px] text-amber-700 dark:text-amber-400 mt-1">
                            Você não pode remover seus próprios privilégios de administrador.
                        </p>
                        <div v-if="form.errors.is_admin" class="text-xs text-red-600 mt-1">
                            {{ form.errors.is_admin }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nova Senha (opcional)
                        </label>
                        <input
                            type="password"
                            v-model="form.password"
                            placeholder="Deixe em branco para manter a atual"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.password" class="text-xs text-red-600 mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <Link
                        :href="route('usuarios.index')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
