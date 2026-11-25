<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientPagination from '@/Components/ClientPagination.vue';
import AlunoAvatar from '@/Components/AlunoAvatar.vue';
import PendenciaProfessorModal from '@/Components/PendenciaProfessorModal.vue';
import ImprimirDisciplinaPromptModal from '@/Components/ImprimirDisciplinaPromptModal.vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            peis_count: 0,
            alunos_count: 0,
            turmas_count: 0,
            ano_atual: new Date().getFullYear(),
            trimestre_vigente: 2,
            trimestre_selecionado: 2,
            taxa_conclusao: 0,
            minhas_pendencias_count: 0,
            todas_pendencias_count: 0,
        }),
    },
    minhasPendencias: {
        type: Array,
        default: () => [],
    },
    todasPendencias: {
        type: Array,
        default: () => [],
    },
    peisRecentes: {
        type: Array,
        default: () => [],
    },
    usuario: {
        type: Object,
        default: () => ({
            name: '',
            cargo: 'Professor',
            is_admin: false,
        }),
    },
});

const searchPei = ref('');
const alertViewMode = ref('minhas'); // modo de visualização das pendências: 'minhas' ou 'institucional'

// estados de paginação dos blocos do dashboard
const minhasPage = ref(1);
const todasPage = ref(1);
const tablePage = ref(1);
const itemsPerPageCards = 6;
const itemsPerPageTable = 10;

const filteredPeis = computed(() => {
    if (!searchPei.value.trim()) return props.peisRecentes;
    const term = searchPei.value.toLowerCase();
    return props.peisRecentes.filter(p =>
        (p.aluno_nome ?? '').toLowerCase().includes(term) ||
        (p.codigo ?? '').toLowerCase().includes(term) ||
        (p.turma_nome ?? '').toLowerCase().includes(term)
    );
});

// reseta a página atual ao aplicar novo filtro de busca
const resetPeiPage = () => {
    tablePage.value = 1;
};

watch(searchPei, resetPeiPage);

// listagens paginadas computadas em memória
const paginatedMinhasPendencias = computed(() => {
    const start = (minhasPage.value - 1) * itemsPerPageCards;
    return props.minhasPendencias.slice(start, start + itemsPerPageCards);
});

const paginatedTodasPendencias = computed(() => {
    const start = (todasPage.value - 1) * itemsPerPageCards;
    return props.todasPendencias.slice(start, start + itemsPerPageCards);
});

const paginatedPeis = computed(() => {
    const start = (tablePage.value - 1) * itemsPerPageTable;
    return filteredPeis.value.slice(start, start + itemsPerPageTable);
});

const trocarTrimestre = (t) => {
    minhasPage.value = 1;
    todasPage.value = 1;
    router.get(route('dashboard'), { trimestre: t }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// controle do modal de edição rápida da pendência do professor
const showModalPendencia = ref(false);
const pendenciaSelecionada = ref(null);

const abrirModalPendencia = (item) => {
    pendenciaSelecionada.value = item;
    showModalPendencia.value = true;
};

const fecharModalPendencia = () => {
    showModalPendencia.value = false;
    pendenciaSelecionada.value = null;
};

// prompt de impressão após preenchimento rápido de disciplina no dashboard
const showImprimirDisciplinaPrompt = ref(false);
const disciplinaRecemSalva = ref(null);

const aoSalvarPendencia = (dados) => {
    disciplinaRecemSalva.value = dados;
    showImprimirDisciplinaPrompt.value = true;
};
</script>

<template>
    <Head title="Visão Geral - SIGEPEI" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                            Visão Geral
                        </h1>
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ usuario.cargo }}
                        </span>
                        <span v-if="usuario.is_admin" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                            Administrador
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Olá, <strong class="text-slate-800 dark:text-zinc-200">{{ usuario.name }}</strong>. Acompanhe o status e as pendências dos Planos Educacionais Individualizados.
                    </p>
                </div>

                <!-- seletor de trimestre e ano letivo -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center bg-slate-100 dark:bg-zinc-800 p-1 rounded border border-slate-300 dark:border-zinc-700 text-xs font-semibold">
                        <button
                            v-for="t in [1, 2, 3]"
                            :key="t"
                            type="button"
                            @click="trocarTrimestre(t)"
                            :class="[
                                'px-2.5 py-1 rounded transition-colors cursor-pointer flex items-center space-x-1',
                                metrics.trimestre_selecionado === t
                                    ? 'bg-[#006633] text-white shadow-xs'
                                    : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            <span>{{ t }}º Trimestre</span>
                            <span v-if="metrics.trimestre_vigente === t" class="w-1.5 h-1.5 rounded-full bg-amber-400" title="Trimestre Vigente"></span>
                        </button>
                    </div>

                    <div class="hidden sm:block text-xs font-semibold px-2.5 py-1.5 bg-white dark:bg-zinc-800 rounded border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300">
                        Ano: <span class="font-bold text-slate-900 dark:text-white">{{ metrics.ano_atual }}</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. atalhos para ações rápidas da administração e nai -->
            <div v-if="usuario.is_admin || usuario.cargo === 'NAI'">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    <!-- atalho para elaboração de novo pei -->
                    <Link
                        :href="route('peis.create')"
                        class="group p-4 bg-[#006633] hover:bg-emerald-800 text-white rounded-sm shadow-xs transition-all duration-200 flex items-center justify-between border border-[#00552b]"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white/10 rounded-sm group-hover:bg-white/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider">Criar Novo PEI</span>
                                <span class="text-[11px] text-emerald-100">Novo plano individualizado</span>
                            </div>
                        </div>
                        <span class="text-white/70 group-hover:text-white group-hover:translate-x-0.5 transition-transform text-sm">&rarr;</span>
                    </Link>

                    <!-- atalho para cadastro de novo discente -->
                    <Link
                        :href="route('alunos.create')"
                        class="group p-4 bg-slate-800 hover:bg-slate-700 text-white rounded-sm shadow-xs transition-all duration-200 flex items-center justify-between border border-slate-700"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white/10 rounded-sm group-hover:bg-white/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider">Cadastrar Aluno</span>
                                <span class="text-[11px] text-slate-300">Novo discente no sistema</span>
                            </div>
                        </div>
                        <span class="text-white/70 group-hover:text-white group-hover:translate-x-0.5 transition-transform text-sm">&rarr;</span>
                    </Link>

                    <!-- atalho para cadastro de turma -->
                    <Link
                        :href="route('turmas.create')"
                        class="group p-4 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700/80 text-slate-800 dark:text-zinc-100 rounded-sm shadow-xs transition-all duration-200 flex items-center justify-between border border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-slate-100 dark:bg-zinc-700 rounded-sm text-slate-700 dark:text-zinc-200 group-hover:bg-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider">Cadastrar Turma</span>
                                <span class="text-[11px] text-slate-500 dark:text-zinc-400">Cursos e anos letivos</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-slate-700 dark:group-hover:text-white group-hover:translate-x-0.5 transition-transform text-sm">&rarr;</span>
                    </Link>

                    <!-- atalho para consulta ao calendário acadêmico -->
                    <Link
                        :href="route('calendario.index')"
                        class="group p-4 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700/80 text-slate-800 dark:text-zinc-100 rounded-sm shadow-xs transition-all duration-200 flex items-center justify-between border border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-slate-100 dark:bg-zinc-700 rounded-sm text-slate-700 dark:text-zinc-200 group-hover:bg-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider">Calendário Letivo</span>
                                <span class="text-[11px] text-slate-500 dark:text-zinc-400">Datas e prazos dos trimestres</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-slate-700 dark:group-hover:text-white group-hover:translate-x-0.5 transition-transform text-sm">&rarr;</span>
                    </Link>
                </div>
            </div>

            <!-- 2. central de avisos e pendências operacionais -->
            <div class="space-y-3">
                <!-- abas de alternância entre pendências individuais e visão geral institucional -->
                <div v-if="usuario.is_admin || usuario.cargo === 'NAI'" class="flex items-center space-x-2 border-b border-slate-200 dark:border-zinc-700 pb-2">
                    <button
                        type="button"
                        @click="alertViewMode = 'minhas'"
                        :class="[
                            'px-3 py-1.5 text-xs font-bold rounded-sm transition-colors cursor-pointer flex items-center space-x-1.5',
                            alertViewMode === 'minhas'
                                ? 'bg-slate-800 dark:bg-zinc-100 text-white dark:text-slate-900'
                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:bg-slate-200'
                        ]"
                    >
                        <span>Minhas Atribuições Pessoais</span>
                        <span
                            :class="[
                                'px-1.5 py-0.2 rounded-full text-[10px]',
                                minhasPendencias.length > 0 ? 'bg-amber-500 text-white' : 'bg-slate-400 text-white'
                            ]"
                        >
                            {{ minhasPendencias.length }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="alertViewMode = 'institucional'"
                        :class="[
                            'px-3 py-1.5 text-xs font-bold rounded-sm transition-colors cursor-pointer flex items-center space-x-1.5',
                            alertViewMode === 'institucional'
                                ? 'bg-slate-800 dark:bg-zinc-100 text-white dark:text-slate-900'
                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:bg-slate-200'
                        ]"
                    >
                        <span>Visão Institucional do Campus</span>
                        <span
                            :class="[
                                'px-1.5 py-0.2 rounded-full text-[10px]',
                                todasPendencias.length > 0 ? 'bg-blue-600 text-white' : 'bg-slate-400 text-white'
                            ]"
                        >
                            {{ todasPendencias.length }}
                        </span>
                    </button>
                </div>

                <!-- modo a: atribuições e pendências do usuário logado -->
                <div v-if="alertViewMode === 'minhas'">
                    <div v-if="minhasPendencias.length > 0" class="bg-amber-50/70 dark:bg-amber-950/30 border-l-4 border-amber-500 p-5 rounded-r-sm border-y border-r border-amber-200 dark:border-amber-900/60 shadow-xs space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center space-x-2.5">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                                <h2 class="text-sm font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wide">
                                    Atenção: Você possui {{ minhasPendencias.length }} pendência(s) de preenchimento no {{ metrics.trimestre_selecionado }}º Trimestre
                                </h2>
                            </div>
                            <span v-if="metrics.trimestre_selecionado === metrics.trimestre_vigente" class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200">
                                Trimestre Vigente
                            </span>
                        </div>

                        <p class="text-xs text-amber-800 dark:text-amber-300">
                            Os seguintes itens sob sua responsabilidade requerem registro pedagógico para o trimestre selecionado:
                        </p>

                        <!-- grade paginada de ações pendentes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                            <div
                                v-for="item in paginatedMinhasPendencias"
                                :key="item.id"
                                class="p-3.5 bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-800/80 rounded-sm shadow-2xs flex flex-col justify-between space-y-2.5 hover:border-amber-400 transition-colors"
                            >
                                <div>
                                    <div class="flex items-center justify-between gap-1 text-[11px]">
                                        <span class="font-mono font-bold text-[#006633] dark:text-emerald-400">
                                            {{ item.pei_codigo }}
                                        </span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300">
                                            {{ item.tipo_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2.5 mt-1.5">
                                        <AlunoAvatar :src="item.aluno_foto_url" :nome="item.aluno_nome" size="md" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                                {{ item.aluno_nome }}
                                            </h3>
                                            <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                                {{ item.atribuicao }} • {{ item.turma_nome }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-[11px] font-medium text-amber-700 dark:text-amber-400 mt-1.5 flex items-center space-x-1">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ item.detalhe }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button
                                        v-if="item.tipo === 'disciplina'"
                                        type="button"
                                        @click="abrirModalPendencia(item)"
                                        class="inline-flex items-center justify-center flex-1 px-3 py-1.5 bg-[#006633] hover:bg-emerald-800 text-white text-[11px] font-semibold rounded-sm transition-colors shadow-2xs cursor-pointer gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Preencher</span>
                                    </button>
                                    <Link
                                        :href="route('peis.show', item.pei_id)"
                                        :class="[
                                            'inline-flex items-center justify-center text-[11px] font-semibold rounded-sm transition-colors',
                                            item.tipo === 'disciplina'
                                                ? 'px-2 py-1.5 bg-slate-100 dark:bg-zinc-700 hover:bg-slate-200 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 border border-slate-200 dark:border-zinc-600 shrink-0'
                                                : 'w-full px-3 py-1.5 bg-[#006633] hover:bg-emerald-800 text-white shadow-2xs'
                                        ]"
                                        :title="item.tipo === 'disciplina' ? 'Abrir página completa do PEI' : 'Preencher no PEI'"
                                    >
                                        <span v-if="item.tipo === 'disciplina'">Ver PEI &rarr;</span>
                                        <span v-else>Preencher no PEI &rarr;</span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- paginação de atribuições do usuário -->
                        <ClientPagination
                            :total-items="minhasPendencias.length"
                            :items-per-page="itemsPerPageCards"
                            v-model="minhasPage"
                            item-label="pendência(s)"
                        />
                    </div>

                    <!-- mensagem de ausência de pendências para o usuário -->
                    <div v-else class="bg-emerald-50/70 dark:bg-emerald-950/20 border-l-4 border-[#006633] p-4 rounded-r-sm border-y border-r border-emerald-200 dark:border-emerald-900/50 shadow-xs flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/60 text-[#006633] dark:text-emerald-400 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200">
                                    Todas as suas atribuições do {{ metrics.trimestre_selecionado }}º Trimestre estão concluídas.
                                </div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400">
                                    Nenhuma pendência pessoal atribuída ao seu usuário no trimestre selecionado.
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="route('peis.index')"
                            class="text-xs font-semibold text-[#006633] dark:text-emerald-400 hover:underline hidden sm:inline-block"
                        >
                            Ver todos os PEIs &rarr;
                        </Link>
                    </div>
                </div>

                <!-- modo b: visão institucional de pendências do campus -->
                <div v-else-if="alertViewMode === 'institucional'">
                    <div v-if="todasPendencias.length > 0" class="bg-blue-50/70 dark:bg-blue-950/30 border-l-4 border-blue-600 p-5 rounded-r-sm border-y border-r border-blue-200 dark:border-blue-900/60 shadow-xs space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center space-x-2.5">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-600"></span>
                                </span>
                                <h2 class="text-sm font-bold text-blue-950 dark:text-blue-200 uppercase tracking-wide">
                                    Visão do Campus: {{ todasPendencias.length }} pendência(s) identificadas no {{ metrics.trimestre_selecionado }}º Trimestre
                                </h2>
                            </div>
                            <span class="text-xs text-blue-800 dark:text-blue-300 font-medium">
                                Acompanhamento de toda a equipe docente e NAI
                            </span>
                        </div>

                        <p class="text-xs text-blue-900/80 dark:text-blue-300">
                            Lista de todas as disciplinas e acompanhamentos ainda não preenchidos no campus:
                        </p>

                        <!-- grade paginada de pendências institucionais -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                            <div
                                v-for="item in paginatedTodasPendencias"
                                :key="item.id"
                                class="p-3.5 bg-white dark:bg-zinc-800 border border-blue-200 dark:border-blue-800/80 rounded-sm shadow-2xs flex flex-col justify-between space-y-2.5 hover:border-blue-400 transition-colors"
                            >
                                <div>
                                    <div class="flex items-center justify-between gap-1 text-[11px]">
                                        <span class="font-mono font-bold text-[#006633] dark:text-emerald-400">
                                            {{ item.pei_codigo }}
                                        </span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300">
                                            {{ item.tipo_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2.5 mt-1.5">
                                        <AlunoAvatar :src="item.aluno_foto_url" :nome="item.aluno_nome" size="md" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                                {{ item.aluno_nome }}
                                            </h3>
                                            <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">
                                                {{ item.atribuicao }} • {{ item.turma_nome }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-[11px] font-medium text-slate-700 dark:text-zinc-300 mt-1.5">
                                        Responsáveis: <strong class="text-slate-900 dark:text-white">{{ item.responsaveis }}</strong>
                                    </div>
                                    <div class="text-[10px] text-blue-700 dark:text-blue-400 mt-0.5">
                                        Status: {{ item.detalhe }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <Link
                                        :href="route('peis.show', item.pei_id)"
                                        class="inline-flex items-center justify-center w-full px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold rounded-sm transition-colors shadow-2xs"
                                    >
                                        Abrir PEI &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- paginação de pendências institucionais -->
                        <ClientPagination
                            :total-items="todasPendencias.length"
                            :items-per-page="itemsPerPageCards"
                            v-model="todasPage"
                            item-label="pendência(s)"
                        />
                    </div>

                    <div v-else class="bg-emerald-50/70 dark:bg-emerald-950/20 border-l-4 border-[#006633] p-4 rounded-r-sm border-y border-r border-emerald-200 dark:border-emerald-900/50 shadow-xs flex items-center justify-between">
                        <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200">
                            Todos os PEIs do campus estão com o {{ metrics.trimestre_selecionado }}º Trimestre preenchido.
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. monitoramento trimestral de peis -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200 dark:border-zinc-700">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Acompanhamento Trimestral de PEIs
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Status de preenchimento por trimestre para cada estudante atendido.
                        </p>
                    </div>

                    <div class="w-full sm:w-64">
                        <input
                            type="text"
                            v-model="searchPei"
                            placeholder="Buscar por discente ou código..."
                            class="w-full text-xs px-3 py-1.5 rounded border border-slate-300 dark:border-zinc-600 bg-slate-50 dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-300 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-50 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Código</th>
                                <th class="px-4 py-3">Estudante</th>
                                <th class="px-4 py-3">Curso / Turma</th>
                                <th class="px-4 py-3 text-center">1º Trimestre</th>
                                <th class="px-4 py-3 text-center">2º Trimestre</th>
                                <th class="px-4 py-3 text-center">3º Trimestre</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="pei in paginatedPeis"
                                :key="pei.id"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 transition-colors"
                            >
                                <td class="px-4 py-3 font-mono font-bold text-[#006633] dark:text-emerald-400">
                                    {{ pei.codigo }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <AlunoAvatar :src="pei.aluno_foto_url" :nome="pei.aluno_nome" size="sm" />
                                        <div>
                                            {{ pei.aluno_nome }}
                                            <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-normal">
                                                Matrícula: {{ pei.aluno_matricula }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    {{ pei.turma_nome }}
                                </td>

                                <!-- status do 1º trimestre -->
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-[10px] font-bold rounded-sm inline-block',
                                            pei.status_trimestres[1]?.status === 'completo'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'
                                                : pei.status_trimestres[1]?.status === 'andamento'
                                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800'
                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800'
                                        ]"
                                    >
                                        {{ pei.status_trimestres[1]?.status === 'completo' ? 'Concluído' : pei.status_trimestres[1]?.status === 'andamento' ? 'Parcial' : 'Pendente' }}
                                    </span>
                                </td>

                                <!-- status do 2º trimestre -->
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-[10px] font-bold rounded-sm inline-block',
                                            pei.status_trimestres[2]?.status === 'completo'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'
                                                : pei.status_trimestres[2]?.status === 'andamento'
                                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800'
                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800'
                                        ]"
                                    >
                                        {{ pei.status_trimestres[2]?.status === 'completo' ? 'Concluído' : pei.status_trimestres[2]?.status === 'andamento' ? 'Parcial' : 'Pendente' }}
                                    </span>
                                </td>

                                <!-- status do 3º trimestre -->
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-[10px] font-bold rounded-sm inline-block',
                                            pei.status_trimestres[3]?.status === 'completo'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'
                                                : pei.status_trimestres[3]?.status === 'andamento'
                                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800'
                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800'
                                        ]"
                                    >
                                        {{ pei.status_trimestres[3]?.status === 'completo' ? 'Concluído' : pei.status_trimestres[3]?.status === 'andamento' ? 'Parcial' : 'Pendente' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="route('peis.show', pei.id)"
                                        class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-800 dark:text-zinc-200 text-xs font-semibold rounded transition-colors"
                                    >
                                        Abrir PEI &rarr;
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="filteredPeis.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400">
                                    Nenhum PEI encontrado com os filtros aplicados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- paginação da listagem de peis -->
                <ClientPagination
                    :total-items="filteredPeis.length"
                    :items-per-page="itemsPerPageTable"
                    v-model="tablePage"
                    item-label="PEI(s)"
                />
            </div>
        </div>

        <!-- modal de preenchimento rápido da pendência do professor -->
        <PendenciaProfessorModal
            :show="showModalPendencia"
            :pendencia="pendenciaSelecionada"
            @close="fecharModalPendencia"
            @saved="aoSalvarPendencia"
        />

        <!-- prompt de impressão após preenchimento de disciplina -->
        <ImprimirDisciplinaPromptModal
            :show="showImprimirDisciplinaPrompt"
            :pei-id="disciplinaRecemSalva?.pei_id"
            :pei-codigo="disciplinaRecemSalva?.pei_codigo || ''"
            :aluno-nome="disciplinaRecemSalva?.aluno_nome || ''"
            :disciplina-id="disciplinaRecemSalva?.disciplina_id"
            :disciplina-codigo="disciplinaRecemSalva?.disciplina_codigo || ''"
            :disciplina-nome="disciplinaRecemSalva?.disciplina_nome || ''"
            :trimestre="disciplinaRecemSalva?.trimestre || 1"
            @close="showImprimirDisciplinaPrompt = false"
        />
    </AuthenticatedLayout>
</template>
