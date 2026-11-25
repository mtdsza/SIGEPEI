<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useUnsavedChanges } from '@/composables/useUnsavedChanges';

const props = defineProps({
    deficiencia: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    codigo: props.deficiencia.codigo,
    nome: props.deficiencia.nome,
});

useUnsavedChanges(form);

const submit = () => {
    form.put(route('deficiencias.update', props.deficiencia.id));
};
</script>

<template>
    <Head :title="`Editar Deficiência - ${deficiencia.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Editar Deficiência - {{ deficiencia.nome }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Código: {{ deficiencia.codigo }}
                    </p>
                </div>
                <Link
                    :href="route('deficiencias.index')"
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
                            Código <span class="text-red-500">*</span>
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
                            Nome por Extenso <span class="text-red-500">*</span>
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
                        :href="route('deficiencias.index')"
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
