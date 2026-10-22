<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientPagination from '@/Components/ClientPagination.vue';

const props = defineProps({
    logs: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            hoje: 0,
            peis: 0,
            autenticacoes: 0,
        }),
    },
    modulosDisponiveis: {
        type: Array,
        default: () => [],
    },
    acoesDisponiveis: {
        type: Array,
        default: () => [],
    },
    usuariosDisponiveis: {
        type: Array,
        default: () => [],
    },
    filtros: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filtros.search || '');
const moduloSelecionado = ref(props.filtros.modulo || '');
const acaoSelecionada = ref(props.filtros.acao || '');
const usuarioSelecionado = ref(props.filtros.user_id || '');
const dataInicio = ref(props.filtros.data_inicio || '');
const dataFim = ref(props.filtros.data_fim || '');

const currentPage = ref(1);
const itemsPerPage = 15;

const aplicarFiltros = () => {
    currentPage.value = 1;
    router.get(route('auditoria.index'), {
        search: search.value || undefined,
        modulo: moduloSelecionado.value || undefined,
        acao: acaoSelecionada.value || undefined,
        user_id: usuarioSelecionado.value || undefined,
        data_inicio: dataInicio.value || undefined,
        data_fim: dataFim.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    search.value = '';
    moduloSelecionado.value = '';
    acaoSelecionada.value = '';
    usuarioSelecionado.value = '';
    dataInicio.value = '';
    dataFim.value = '';
    aplicarFiltros();
};

const paginatedLogs = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return props.logs.slice(start, start + itemsPerPage);
});

const getAcaoBadgeClass = (acao) => {
    switch (acao) {
        case 'Criação':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800';
        case 'Atualização':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-800';
        case 'Inativação':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300 dark:border-rose-800';
        case 'Reativação':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800';
        case 'Login':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300 dark:border-purple-800';
        case 'Logout':
            return 'bg-slate-100 text-slate-700 dark:bg-zinc-700 dark:text-zinc-300 border-slate-300 dark:border-zinc-600';
        default:
            return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 border-slate-200 dark:border-zinc-700';
    }
};

const getModuloBadgeClass = (modulo) => {
    switch (modulo) {
        case 'PEIs':
            return 'bg-emerald-50 text-[#006633] dark:bg-emerald-950/60 dark:text-emerald-400 font-bold';
        case 'Alunos':
            return 'bg-sky-50 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300';
        case 'Turmas':
            return 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300';
        case 'Usuários':
            return 'bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300';
        default:
            return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
    }
};
</script>

<template>
    <Head title="Trilha de Auditoria - SIGEPEI" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Trilha de Auditoria e Logs do Sistema
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Registro cronológico, imutável e detalhado de todas as operações realizadas no sistema.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. estatísticas rápidas de auditoria -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <div class="p-4 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Total de Registros</span>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.total }}</div>
                    </div>
                    <div class="p-2 bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-zinc-300 rounded-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Operações Hoje</span>
                        <div class="text-xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">{{ stats.hoje }}</div>
                    </div>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-[#006633] dark:text-emerald-400 uppercase tracking-wider">Ações em PEIs</span>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.peis }}</div>
                    </div>
                    <div class="p-2 bg-[#006633]/10 text-[#006633] dark:text-emerald-400 rounded-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">Autenticações / Logins</span>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.autenticacoes }}</div>
                    </div>
                    <div class="p-2 bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-400 rounded-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- 2. painel de filtros e busca avançada -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                    <span class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filtros de Pesquisa na Auditoria</span>
                    </span>
                    <button
                        type="button"
                        @click="limparFiltros"
                        class="text-xs text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 underline cursor-pointer"
                    >
                        Limpar Filtros
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                    <!-- busca textual por descrição ou usuário -->
                    <div class="lg:col-span-2">
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Termo de Busca:</label>
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="aplicarFiltros"
                            placeholder="Buscar por descrição, usuário, IP..."
                            class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        />
                    </div>

                    <!-- filtro por módulo do sistema -->
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Módulo:</label>
                        <select
                            v-model="moduloSelecionado"
                            @change="aplicarFiltros"
                            class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        >
                            <option value="">Todos os Módulos</option>
                            <option v-for="m in modulosDisponiveis" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>

                    <!-- filtro por tipo de ação -->
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Tipo de Ação:</label>
                        <select
                            v-model="acaoSelecionada"
                            @change="aplicarFiltros"
                            class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        >
                            <option value="">Todas as Ações</option>
                            <option v-for="a in acoesDisponiveis" :key="a" :value="a">{{ a }}</option>
                        </select>
                    </div>

                    <!-- data inicial do período -->
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Data Início:</label>
                        <input
                            type="date"
                            v-model="dataInicio"
                            @change="aplicarFiltros"
                            class="w-full px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        />
                    </div>

                    <!-- data final do período -->
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-zinc-300 mb-1">Data Fim:</label>
                        <input
                            type="date"
                            v-model="dataFim"
                            @change="aplicarFiltros"
                            class="w-full px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        />
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="aplicarFiltros"
                        class="px-4 py-1.5 bg-[#006633] hover:bg-emerald-800 text-white font-semibold rounded-sm text-xs transition-colors shadow-2xs cursor-pointer flex items-center space-x-1"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Filtrar Resultados</span>
                    </button>
                </div>
            </div>

            <!-- 3. tabela de registros da trilha de auditoria -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row gap-2 justify-between items-center text-xs">
                    <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">
                        Histórico Cronológico
                    </span>
                    <span class="text-slate-500 dark:text-zinc-400">
                        Total: <strong class="text-slate-900 dark:text-white">{{ logs.length }}</strong> registro(s) encontrado(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Data / Hora</th>
                                <th class="px-4 py-3">Usuário</th>
                                <th class="px-4 py-3">Módulo</th>
                                <th class="px-4 py-3">Ação</th>
                                <th class="px-4 py-3">Descrição da Operação</th>
                                <th class="px-4 py-3">Endereço IP</th>
                                <th class="px-4 py-3 text-right">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="log in paginatedLogs"
                                :key="log.id"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 transition-colors"
                            >
                                <!-- data e hora do evento -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-mono font-medium text-[11px] text-slate-900 dark:text-white">
                                        {{ log.data_formatada }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-normal">
                                        {{ log.data_relativa }}
                                    </div>
                                </td>

                                <!-- usuário responsável pela ação -->
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ log.usuario.nome }}
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-500 dark:text-zinc-400 truncate">
                                        {{ log.usuario.email }}
                                    </div>
                                </td>

                                <!-- módulo do sistema afetado -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 text-[10px] font-semibold rounded-sm inline-block', getModuloBadgeClass(log.modulo)]">
                                        {{ log.modulo }}
                                    </span>
                                </td>

                                <!-- tipo de ação executada -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-sm border inline-block', getAcaoBadgeClass(log.acao)]">
                                        {{ log.acao }}
                                    </span>
                                </td>

                                <!-- descrição detalhada do evento -->
                                <td class="px-4 py-3">
                                    <span class="text-slate-800 dark:text-zinc-200">
                                        {{ log.descricao }}
                                    </span>
                                </td>

                                <!-- endereço ip do cliente -->
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                                    {{ log.ip_address || '127.0.0.1' }}
                                </td>

                                <!-- link para detalhes do evento -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="route('auditoria.show', log.id)"
                                        class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 text-xs transition-colors"
                                    >
                                        Ver Detalhes &rarr;
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="logs.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                    Nenhum registro de auditoria encontrado com os filtros aplicados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- paginação dos registros de auditoria -->
                <ClientPagination
                    :total-items="logs.length"
                    :items-per-page="itemsPerPage"
                    v-model="currentPage"
                    item-label="registro(s) de auditoria"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
