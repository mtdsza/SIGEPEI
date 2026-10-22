<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AlunoAvatar from '@/Components/AlunoAvatar.vue';

const props = defineProps({
    turma: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Turma - ${turma.nome_curso}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ turma.nome_curso }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Ano Letivo: <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ turma.ano_letivo }}</span>
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                        :href="route('turmas.edit', turma.id)"
                        class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                    >
                        Editar Turma
                    </Link>
                    <Link
                        :href="route('turmas.index')"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                    >
                        Voltar para Lista
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- informações gerais da turma -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2 mb-4">
                    Informações Gerais
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Nome do Curso:</span>
                        <span class="text-slate-900 dark:text-white font-semibold text-sm">{{ turma.nome_curso }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Ano Letivo:</span>
                        <span class="text-slate-900 dark:text-white font-semibold text-sm">{{ turma.ano_letivo }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Status:</span>
                        <span
                            :class="[
                                'inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold rounded-sm border',
                                turma.is_active
                                    ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                    : 'bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 border-red-200 dark:border-red-800'
                            ]"
                        >
                            {{ turma.is_active ? 'Ativa' : 'Inativa' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- disciplinas e docentes associados à turma -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Disciplinas e Corpo Docente
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Disciplinas lecionadas nesta turma e respectivos professores titulares e substitutos.
                        </p>
                    </div>
                </div>

                <div v-if="turma.disciplinas && turma.disciplinas.length > 0" class="space-y-4">
                    <div
                        v-for="disc in turma.disciplinas"
                        :key="disc.id"
                        class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-zinc-700/60 pb-2.5 mb-3">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-slate-200 dark:bg-zinc-700 text-slate-800 dark:text-zinc-200 font-mono text-[11px] font-bold rounded">
                                    {{ disc.codigo }}
                                </span>
                                <span class="font-bold text-sm text-slate-900 dark:text-white">
                                    {{ disc.nome }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                Professores Associados:
                            </div>

                            <div v-if="disc.professores && disc.professores.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                <div
                                    v-for="prof in disc.professores"
                                    :key="prof.id"
                                    class="flex items-center justify-between p-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs"
                                >
                                    <div class="min-w-0 pr-2">
                                        <div class="font-semibold text-slate-800 dark:text-zinc-100 truncate">
                                            {{ prof.name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate font-mono">
                                            {{ prof.email }}
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'shrink-0 px-2 py-0.5 text-[10px] font-semibold rounded-sm border',
                                            prof.is_substituto
                                                ? 'bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800'
                                                : 'bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                        ]"
                                    >
                                        {{ prof.is_substituto ? 'Substituto' : 'Titular' }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-500 dark:text-zinc-400 italic">
                                Nenhum professor vinculado a esta disciplina na turma.
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="p-6 text-center text-xs text-slate-500 dark:text-zinc-400">
                    Nenhuma disciplina associada a esta turma no momento.
                </div>
            </div>

            <!-- listagem de discentes matriculados -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Estudantes Matriculados
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Relação de alunos vinculados a esta turma no sistema.
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 dark:text-zinc-400">
                        Total: {{ turma.alunos ? turma.alunos.length : 0 }} aluno(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Matrícula</th>
                                <th class="px-4 py-3">Nome do Aluno</th>
                                <th class="px-4 py-3">Situação</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="aluno in turma.alunos"
                                :key="aluno.id"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 transition-colors"
                            >
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-600 dark:text-zinc-300">{{ aluno.matricula }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <AlunoAvatar :src="aluno.foto_url" :nome="aluno.nome" size="sm" />
                                        <span>{{ aluno.nome }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-[10px] font-semibold rounded-sm border',
                                            aluno.is_atual
                                                ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 border-slate-300 dark:border-zinc-700'
                                        ]"
                                    >
                                        {{ aluno.is_atual ? 'Matrícula Vigente' : 'Histórico Anterior' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="route('alunos.show', aluno.id)"
                                        class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 font-medium rounded-sm border border-slate-300 dark:border-zinc-600"
                                    >
                                        Ver Aluno
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!turma.alunos || turma.alunos.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                    Nenhum aluno vinculado a esta turma.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
