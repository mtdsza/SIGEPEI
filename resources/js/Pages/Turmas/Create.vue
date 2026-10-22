<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    disciplinasDisponiveis: {
        type: Array,
        default: () => [],
    },
    professoresDisponiveis: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    nome_curso: '',
    ano_letivo: new Date().getFullYear(),
    disciplinas: [
        {
            disciplina_id: '',
            professores: [
                { user_id: '', is_substituto: false }
            ],
        },
    ],
});

const adicionarDisciplina = () => {
    form.disciplinas.push({
        disciplina_id: '',
        professores: [
            { user_id: '', is_substituto: false }
        ],
    });
};

const removerDisciplina = (index) => {
    form.disciplinas.splice(index, 1);
};

const adicionarProfessor = (discIndex) => {
    form.disciplinas[discIndex].professores.push({
        user_id: '',
        is_substituto: true,
    });
};

const removerProfessor = (discIndex, profIndex) => {
    form.disciplinas[discIndex].professores.splice(profIndex, 1);
};

const submit = () => {
    form.post(route('turmas.store'));
};
</script>

<template>
    <Head title="Criar Nova Turma" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Cadastrar Nova Turma
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Defina o nome do curso, ano letivo e associe disciplinas com professores titulares e substitutos.
                    </p>
                </div>
                <Link
                    :href="route('turmas.index')"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                >
                    Voltar para Lista
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- informações cadastrais básicas da turma -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome do Curso <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.nome_curso"
                            required
                            placeholder="Ex: Técnico em Informática Integrado ao Ensino Médio"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.nome_curso" class="text-xs text-red-600 mt-1">
                            {{ form.errors.nome_curso }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Ano Letivo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model="form.ano_letivo"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.ano_letivo" class="text-xs text-red-600 mt-1">
                            {{ form.errors.ano_letivo }}
                        </div>
                    </div>
                </div>

                <!-- seção de vínculo de disciplinas e docentes -->
                <div class="pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                                Disciplinas e Professores Associados
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                Adicione as disciplinas e vincule os professores titulares e substitutos para cada uma.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="adicionarDisciplina"
                            class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-800 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 transition-colors"
                        >
                            + Adicionar Disciplina
                        </button>
                    </div>

                    <!-- lista de disciplinas vinculadas -->
                    <div class="space-y-4">
                        <div
                            v-for="(disc, discIndex) in form.disciplinas"
                            :key="discIndex"
                            class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                        Disciplina {{ discIndex + 1 }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="disc.disciplina_id"
                                        required
                                        class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    >
                                        <option value="">Selecione uma disciplina...</option>
                                        <option
                                            v-for="d in disciplinasDisponiveis"
                                            :key="d.id"
                                            :value="d.id"
                                        >
                                            [{{ d.codigo }}] {{ d.nome }}
                                        </option>
                                    </select>
                                </div>
                                <button
                                    v-if="form.disciplinas.length > 1"
                                    type="button"
                                    @click="removerDisciplina(discIndex)"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline pt-5"
                                >
                                    Remover Disciplina
                                </button>
                            </div>

                            <!-- docentes vinculados a esta disciplina -->
                            <div class="pt-2 pl-3 border-l-2 border-slate-300 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-zinc-300 uppercase">
                                        Professores Alocados:
                                    </span>
                                    <button
                                        type="button"
                                        @click="adicionarProfessor(discIndex)"
                                        class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                                    >
                                        + Adicionar Professor (Substituto)
                                    </button>
                                </div>

                                <div
                                    v-for="(prof, profIndex) in disc.professores"
                                    :key="profIndex"
                                    class="flex items-center gap-3 bg-white dark:bg-zinc-800 p-2.5 rounded border border-slate-200 dark:border-zinc-700 text-xs"
                                >
                                    <div class="flex-1">
                                        <select
                                            v-model="prof.user_id"
                                            required
                                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        >
                                            <option value="">Selecione o professor...</option>
                                            <option
                                                v-for="p in professoresDisponiveis"
                                                :key="p.id"
                                                :value="p.id"
                                            >
                                                {{ p.name }} ({{ p.cargo }} - {{ p.email }})
                                            </option>
                                        </select>
                                    </div>

                                    <label class="inline-flex items-center space-x-1.5 text-xs text-slate-700 dark:text-zinc-300 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="prof.is_substituto"
                                            class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                                        />
                                        <span>Substituto</span>
                                    </label>

                                    <button
                                        v-if="disc.professores.length > 1"
                                        type="button"
                                        @click="removerProfessor(discIndex, profIndex)"
                                        class="text-slate-400 hover:text-red-600 px-1 text-sm font-bold"
                                        title="Remover este professor"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- botões de ação do formulário -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <Link
                        :href="route('turmas.index')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Salvar Turma
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
