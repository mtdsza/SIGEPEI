<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    disciplina: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    codigo: props.disciplina.codigo,
    nome: props.disciplina.nome,
});

const submit = () => {
    form.put(route('disciplinas.update', props.disciplina.id));
};
</script>

<template>
    <Head :title="`Editar Disciplina - ${disciplina.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Editar Disciplina - {{ disciplina.nome }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Código: {{ disciplina.codigo }}
                    </p>
                </div>
                <Link
                    :href="route('disciplinas.index')"
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
                            Código da Disciplina <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.codigo"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.codigo" class="text-xs text-red-600 mt-1">
                            {{ form.errors.codigo }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome da Disciplina <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.nome"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.nome" class="text-xs text-red-600 mt-1">
                            {{ form.errors.nome }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <Link
                        :href="route('disciplinas.index')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
