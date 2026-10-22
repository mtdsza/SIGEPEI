<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    aluno: {
        type: Object,
        required: true,
    },
    turmasDisponiveis: {
        type: Array,
        default: () => [],
    },
});

// controle de exibição do histórico de turmas anteriores
const showHistoricoTurmas = ref(false);

// controle de abertura do modal de enturmação
const showTurmaModal = ref(false);

const turmaForm = useForm({
    turma_id: '',
    ano_letivo: new Date().getFullYear(),
});

const submitAssociarTurma = () => {
    turmaForm.post(route('alunos.associar-turma', props.aluno.id), {
        onSuccess: () => {
            showTurmaModal.value = false;
            turmaForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="`Aluno - ${aluno.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Perfil do Aluno
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Matrícula: {{ aluno.matricula }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('alunos.index')"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                    >
                        Voltar para Lista
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- cartão principal do discente -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <!-- foto ou avatar do discente -->
                    <div class="shrink-0 flex flex-col items-center">
                        <img
                            v-if="aluno.foto_url"
                            :src="aluno.foto_url"
                            :alt="`Foto de ${aluno.nome}`"
                            class="w-28 h-28 object-cover rounded-sm border border-slate-200 dark:border-zinc-700 shadow-sm bg-slate-100 dark:bg-zinc-900"
                        />
                        <template v-else>
                            <div class="w-28 h-28 bg-slate-100 dark:bg-zinc-900 border-2 border-dashed border-slate-300 dark:border-zinc-700 rounded-sm flex flex-col items-center justify-center text-slate-400 dark:text-zinc-500 p-2 text-center">
                                <svg class="w-10 h-10 mb-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-[9px] uppercase tracking-wider font-semibold">Sem Foto</span>
                            </div>
                            <Link
                                v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                                :href="route('alunos.edit', aluno.id)"
                                class="text-[10px] text-[#006633] dark:text-emerald-400 hover:underline mt-1.5 text-center font-semibold"
                            >
                                Adicionar foto
                            </Link>
                        </template>
                    </div>

                    <!-- informações principais e ações rápidas -->
                    <div class="flex-1 min-w-0 space-y-4 w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h2 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white">
                                    {{ aluno.nome }}
                                </h2>
                                <div class="text-xs text-slate-500 dark:text-zinc-400 font-mono mt-0.5">
                                    Matrícula: <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ aluno.matricula }}</span>
                                </div>
                            </div>

                            <!-- botões de ação do cabeçalho restritos a administração e nai -->
                            <div v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'" class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    @click="showTurmaModal = true"
                                    class="inline-flex items-center px-3 py-1.5 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Associar a uma turma
                                </button>
                                <Link
                                    :href="route('alunos.edit', aluno.id)"
                                    class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar Perfil
                                </Link>
                            </div>
                        </div>

                        <!-- dados básicos do discente -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-3 border-t border-slate-200 dark:border-zinc-700 text-xs">
                            <div>
                                <span class="block text-slate-500 dark:text-zinc-400 font-medium">Idade:</span>
                                <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                    {{ aluno.idade_formatada }}
                                </span>
                            </div>

                            <!-- turma atual e controle de turmas anteriores -->
                            <div>
                                <span class="block text-slate-500 dark:text-zinc-400 font-medium">Turma Atual:</span>
                                <div class="flex items-center space-x-2 mt-0.5">
                                    <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                        {{ aluno.turma_atual ? `${aluno.turma_atual.nome} (${aluno.turma_atual.ano})` : 'Nenhuma' }}
                                    </span>
                                    <button
                                        v-if="aluno.turmas_anteriores && aluno.turmas_anteriores.length > 0"
                                        type="button"
                                        @click="showHistoricoTurmas = !showHistoricoTurmas"
                                        class="inline-flex items-center px-1.5 py-0.5 text-[11px] font-bold bg-slate-100 dark:bg-zinc-700 hover:bg-slate-200 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 rounded border border-slate-300 dark:border-zinc-600 transition-colors"
                                        :title="showHistoricoTurmas ? 'Ocultar turmas anteriores' : 'Exibir turmas anteriores'"
                                    >
                                        {{ showHistoricoTurmas ? '−' : '+' }}
                                        <span class="ml-1 text-[10px] font-normal">anteriores</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <span class="block text-slate-500 dark:text-zinc-400 font-medium">Status Cadastral:</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Ativo
                                </span>
                            </div>
                        </div>

                        <!-- listagem expansível de turmas anteriores -->
                        <div v-if="showHistoricoTurmas && aluno.turmas_anteriores && aluno.turmas_anteriores.length > 0" class="p-3 bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-700 rounded-sm text-xs space-y-1.5">
                            <div class="font-bold text-slate-700 dark:text-zinc-300 uppercase text-[10px] tracking-wider mb-1">
                                Histórico de Turmas Anteriores:
                            </div>
                            <div
                                v-for="t in aluno.turmas_anteriores"
                                :key="t.id"
                                class="flex items-center justify-between py-1 px-2 bg-white dark:bg-zinc-800 rounded border border-slate-200 dark:border-zinc-700 text-xs"
                            >
                                <span class="font-medium text-slate-800 dark:text-zinc-200">{{ t.nome }}</span>
                                <span class="text-slate-500 dark:text-zinc-400 font-mono text-[11px]">Ano Letivo: {{ t.ano }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ficha do discente (extraída do pei mais recente) -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Ficha do Aluno
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            <span v-if="aluno.ficha_pei && aluno.ficha_pei.existe">
                                Informações consolidadas do PEI mais recente (<span class="font-mono font-semibold text-slate-700 dark:text-zinc-300">{{ aluno.ficha_pei.pei_codigo }}</span> - Ano Letivo {{ aluno.ficha_pei.pei_ano }}).
                            </span>
                            <span v-else>
                                Perfil comportamental, rotina e necessidades específicas registradas no PEI.
                            </span>
                        </p>
                    </div>

                    <Link
                        v-if="aluno.ficha_pei && aluno.ficha_pei.existe && aluno.ficha_pei.pei_id"
                        :href="route('peis.show', aluno.ficha_pei.pei_id)"
                        class="text-xs font-semibold text-[#006633] dark:text-emerald-400 hover:underline inline-flex items-center"
                    >
                        Abrir PEI Completo &rarr;
                    </Link>
                </div>

                <!-- mensagem de ausência de pei cadastrado -->
                <div
                    v-if="!aluno.ficha_pei || !aluno.ficha_pei.existe"
                    class="p-6 bg-slate-50 dark:bg-zinc-900/60 border border-dashed border-slate-300 dark:border-zinc-700 rounded-sm text-center space-y-3"
                >
                    <svg class="w-10 h-10 mx-auto text-slate-400 dark:text-zinc-500 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="text-sm font-semibold text-slate-700 dark:text-zinc-300">
                        Este aluno ainda não possui ficha cadastrada em PEI.
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-md mx-auto">
                        Ao cadastrar e preencher o primeiro Plano Educacional Individualizado para este estudante, os dados da ficha aparecerão consolidados aqui automaticamente.
                    </p>
                    <Link
                        v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                        :href="route('peis.create')"
                        class="inline-flex items-center px-3.5 py-2 bg-[#006633] hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors"
                    >
                        Criar Novo PEI para este Aluno
                    </Link>
                </div>

                <!-- campos pedagógicos da ficha com base no pei -->
                <div v-else class="space-y-4">
                    <!-- diagnóstico e necessidades específicas -->
                    <div class="p-4 bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800/60 rounded-sm space-y-1.5">
                        <div class="font-bold text-blue-950 dark:text-blue-200 uppercase text-[11px] tracking-wide">
                            Diagnóstico / Transtornos / Necessidades Específicas:
                        </div>
                        <p class="text-slate-800 dark:text-zinc-200 font-semibold leading-relaxed">
                            {{ aluno.ficha_pei.diagnostico || 'Não informado no PEI.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- 1. atividades de tempo livre -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                O que gosta de fazer no tempo livre?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ aluno.ficha_pei.tempo_livre || 'Não informado no PEI.' }}
                            </p>
                        </div>

                        <!-- 2. prática de esportes e atividades físicas -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Gosta de praticar esporte?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ aluno.ficha_pei.pratica_esporte || 'Não informado no PEI.' }}
                            </p>
                        </div>

                        <!-- 3. características pedagógicas e comportamentais -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5 md:col-span-2">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Características do discente:
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ aluno.ficha_pei.caracteristicas_discente || 'Não informado no PEI.' }}
                            </p>
                        </div>

                        <!-- 4. organização e cumprimento de prazos -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ aluno.ficha_pei.organizacao_e_prazos || 'Não informado no PEI.' }}
                            </p>
                        </div>

                        <!-- 5. interação social e comunicação -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Apresenta dificuldade na interação social?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ aluno.ficha_pei.dificuldade_interacao_social || 'Não informado no PEI.' }}
                            </p>
                        </div>

                        <!-- 6. sugestões de recursos e estratégias -->
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/60 rounded-sm space-y-1.5 md:col-span-2">
                            <div class="font-bold text-emerald-900 dark:text-emerald-300 uppercase text-[11px] tracking-wide">
                                Sugestões de recursos ou estratégias que possam ser utilizadas para auxiliar no processo de aprendizagem:
                            </div>
                            <p class="text-slate-800 dark:text-zinc-200 leading-relaxed font-medium">
                                {{ aluno.ficha_pei.sugestoes_recursos_estrategias || 'Não informado no PEI.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. histórico de planos educacionais individualizados (peis) -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide">
                            Histórico de PEIs
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Lista de todos os Planos Educacionais Individualizados associados a este aluno no sistema.
                        </p>
                    </div>
                    <Link
                        v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                        :href="route('peis.create')"
                        class="inline-flex items-center px-3 py-1.5 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                    >
                        + Novo PEI
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Código do PEI</th>
                                <th class="px-4 py-3">Ano Letivo</th>
                                <th class="px-4 py-3">Data de Elaboração</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="pei in aluno.historico_peis"
                                :key="pei.id"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 transition-colors"
                            >
                                <td class="px-4 py-3 font-mono font-medium text-slate-900 dark:text-white">
                                    {{ pei.codigo }}
                                </td>
                                <td class="px-4 py-3">{{ pei.ano }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-zinc-400">{{ pei.data_criacao }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-[10px] font-semibold rounded-sm border',
                                            pei.status === 'Ativo'
                                                ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-300 dark:border-zinc-700'
                                        ]"
                                    >
                                        {{ pei.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="route('peis.show', pei.id)"
                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-sm text-xs transition-colors shadow-2xs"
                                    >
                                        Acessar PEI &rarr;
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!aluno.historico_peis || aluno.historico_peis.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                    Nenhum PEI vinculado a este aluno até o momento.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- modal de vinculação a nova turma -->
        <div
            v-if="showTurmaModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xl max-w-md w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Associar Aluno a uma Turma
                    </h3>
                    <button
                        type="button"
                        @click="showTurmaModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-lg leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitAssociarTurma" class="space-y-4 text-xs">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Turma de Destino <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="turmaForm.turma_id"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">Selecione a turma...</option>
                            <option
                                v-for="t in turmasDisponiveis"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.nome_curso }} ({{ t.ano_letivo }})
                            </option>
                        </select>
                        <div v-if="turmaForm.errors.turma_id" class="text-xs text-red-600 mt-1">
                            {{ turmaForm.errors.turma_id }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Ano Letivo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model="turmaForm.ano_letivo"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="turmaForm.errors.ano_letivo" class="text-xs text-red-600 mt-1">
                            {{ turmaForm.errors.ano_letivo }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="showTurmaModal = false"
                            class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="turmaForm.processing"
                            class="px-3.5 py-1.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            Confirmar Associação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
