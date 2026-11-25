<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useUnsavedChanges } from '@/composables/useUnsavedChanges';

const props = defineProps({
    deficienciasDisponiveis: {
        type: Array,
        default: () => [],
    },
    turmasDisponiveis: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    nome: '',
    matricula: '',
    data_nascimento: '',
    turma_id: '',
    deficiencias: [],
});

useUnsavedChanges(form);

const submit = () => {
    form.post(route('alunos.store'));
};
</script>

<template>
    <Head title="Criar Novo Aluno" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Cadastrar Novo Aluno
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Preencha os dados do estudante, turma inicial e transtornos/deficiências diagnosticados.
                    </p>
                </div>
                <Link
                    :href="route('alunos.index')"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                >
                    Voltar para Lista
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- informações cadastrais básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.nome"
                            required
                            placeholder="Nome completo do aluno"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.nome" class="text-xs text-red-600 mt-1">
                            {{ form.errors.nome }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Matrícula <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.matricula"
                            required
                            placeholder="Ex: 2026100123"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.matricula" class="text-xs text-red-600 mt-1">
                            {{ form.errors.matricula }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Data de Nascimento <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.data_nascimento"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.data_nascimento" class="text-xs text-red-600 mt-1">
                            {{ form.errors.data_nascimento }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Turma Inicial
                        </label>
                        <select
                            v-model="form.turma_id"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">Selecione uma turma...</option>
                            <option
                                v-for="t in turmasDisponiveis"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.nome_curso }} ({{ t.ano_letivo }})
                            </option>
                        </select>
                        <div v-if="form.errors.turma_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.turma_id }}
                        </div>
                    </div>
                </div>

                <!-- seleção de transtornos e deficiências -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-2">
                        Transtornos / Deficiências Diagnosticadas
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                        <label
                            v-for="def in deficienciasDisponiveis"
                            :key="def.id"
                            class="flex items-center space-x-2.5 text-xs text-slate-700 dark:text-zinc-300 cursor-pointer select-none"
                        >
                            <input
                                type="checkbox"
                                :value="def.id"
                                v-model="form.deficiencias"
                                class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                            />
                            <span>
                                <span class="font-mono text-[11px] font-semibold text-slate-500 dark:text-zinc-400">[{{ def.codigo }}]</span>
                                {{ def.nome }}
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <Link
                        :href="route('alunos.index')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Salvar Aluno
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
