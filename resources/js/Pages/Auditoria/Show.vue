<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    log: {
        type: Object,
        required: true,
    },
});

const payloadTab = ref('novos'); // aba de payload selecionada: 'novos' ou 'anteriores'

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
</script>

<template>
    <Head :title="`Log de Auditoria #${log.id} - SIGEPEI`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                            Log de Auditoria #{{ String(log.id).padStart(6, '0') }}
                        </h1>
                        <span :class="['px-2.5 py-0.5 text-xs font-bold rounded-sm border', getAcaoBadgeClass(log.acao)]">
                            {{ log.acao }}
                        </span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-sm bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
                            {{ log.modulo }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Transação registrada em {{ log.data_formatada }} ({{ log.data_relativa }}).
                    </p>
                </div>

                <Link
                    :href="route('auditoria.index')"
                    class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-800 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors border border-slate-300 dark:border-zinc-600 shadow-2xs"
                >
                    &larr; Voltar para Trilha de Auditoria
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. detalhes e metadados da operação -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-4">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2 flex items-center justify-between">
                    <span>Metadados da Transação</span>
                    <span class="text-[11px] font-normal text-slate-500 dark:text-zinc-400">Registro Imutável de Segurança</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Data e Hora:</span>
                        <span class="text-slate-900 dark:text-white font-mono font-semibold">{{ log.data_formatada }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Usuário Responsável:</span>
                        <span class="text-slate-900 dark:text-white font-semibold">{{ log.usuario.nome }}</span>
                        <span class="block text-[11px] text-slate-500 dark:text-zinc-400 font-mono">{{ log.usuario.email }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Cargo / Perfil:</span>
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 inline-block mt-0.5">
                            {{ log.usuario.cargo || 'Não informado' }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Endereço IP:</span>
                        <span class="text-slate-900 dark:text-white font-mono font-semibold">{{ log.ip_address || '127.0.0.1' }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Módulo Afetado:</span>
                        <span class="text-slate-900 dark:text-white font-semibold">{{ log.modulo }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Tipo de Ação:</span>
                        <span class="text-slate-900 dark:text-white font-semibold">{{ log.acao }}</span>
                    </div>

                    <div v-if="log.entidade_tipo">
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">Entidade Vinculada:</span>
                        <div class="flex items-center space-x-1.5 mt-0.5">
                            <span class="font-mono text-slate-900 dark:text-white">{{ log.entidade_tipo }} #{{ log.entidade_id }}</span>
                            <Link
                                v-if="log.entidade_link"
                                :href="log.entidade_link"
                                class="text-xs text-[#006633] dark:text-emerald-400 font-bold hover:underline"
                            >
                                (Acessar &rarr;)
                            </Link>
                        </div>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium">User Agent (Navegador e Dispositivo):</span>
                        <span class="text-slate-700 dark:text-zinc-300 font-mono text-[11px] break-all">{{ log.user_agent || 'N/A' }}</span>
                    </div>
                </div>

                <!-- descrição resumida da ação realizada -->
                <div class="mt-4 p-3 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                    <span class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase">Resumo da Operação:</span>
                    <p class="text-xs font-semibold text-slate-900 dark:text-white mt-0.5">
                        {{ log.descricao }}
                    </p>
                </div>
            </div>

            <!-- 2. comparativo de alterações campo a campo (diff) -->
            <div v-if="log.campo_diffs && log.campo_diffs.length > 0" class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-3">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2">
                    Comparativo de Alterações (Diff de Campos)
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px]">
                            <tr>
                                <th class="px-4 py-2.5 w-1/4">Campo</th>
                                <th class="px-4 py-2.5 w-3/8 text-rose-700 dark:text-rose-400">Valor Anterior</th>
                                <th class="px-4 py-2.5 w-3/8 text-emerald-700 dark:text-emerald-400">Novo Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 font-mono text-[11px]">
                            <tr
                                v-for="(diff, index) in log.campo_diffs"
                                :key="index"
                                :class="[
                                    diff.alterado ? 'bg-amber-50/20 dark:bg-amber-950/10' : ''
                                ]"
                            >
                                <td class="px-4 py-2.5 font-bold font-sans text-slate-800 dark:text-zinc-200 align-top">
                                    <div class="flex items-center space-x-1.5">
                                        <span>{{ diff.campo }}</span>
                                        <span v-if="diff.alterado" class="px-1.5 py-0.2 text-[9px] bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 font-sans font-bold rounded-sm border border-amber-300 dark:border-amber-700">
                                            Modificado
                                        </span>
                                        <span v-else class="text-[9px] text-slate-400 dark:text-zinc-500 font-sans font-normal">
                                            Inalterado
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 break-all whitespace-pre-wrap align-top">
                                    <div :class="[
                                        diff.alterado && diff.anterior
                                            ? 'p-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 rounded-xs'
                                            : 'text-slate-600 dark:text-zinc-400'
                                    ]">
                                        {{ diff.anterior !== null && diff.anterior !== undefined ? diff.anterior : '(Vazio)' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 break-all whitespace-pre-wrap align-top">
                                    <div :class="[
                                        diff.alterado && diff.novo
                                            ? 'p-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 rounded-xs font-semibold'
                                            : 'text-slate-700 dark:text-zinc-300'
                                    ]">
                                        {{ diff.novo !== null && diff.novo !== undefined ? diff.novo : '(Vazio)' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. payload bruto em formato json -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Payload e Conteúdo da Transação (JSON)
                    </h2>

                    <div class="flex items-center space-x-1 text-xs">
                        <button
                            type="button"
                            @click="payloadTab = 'novos'"
                            :class="[
                                'px-2.5 py-1 rounded transition-colors font-semibold cursor-pointer',
                                payloadTab === 'novos'
                                    ? 'bg-[#006633] text-white'
                                    : 'bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-zinc-300 hover:bg-slate-200'
                            ]"
                        >
                            Dados Novos (Estado Atual)
                        </button>
                        <button
                            type="button"
                            @click="payloadTab = 'anteriores'"
                            :class="[
                                'px-2.5 py-1 rounded transition-colors font-semibold cursor-pointer',
                                payloadTab === 'anteriores'
                                    ? 'bg-slate-800 text-white dark:bg-zinc-100 dark:text-slate-900'
                                    : 'bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-zinc-300 hover:bg-slate-200'
                            ]"
                        >
                            Dados Anteriores
                        </button>
                    </div>
                </div>

                <div v-if="payloadTab === 'novos'">
                    <pre class="bg-slate-900 text-emerald-400 p-4 rounded-sm text-xs font-mono overflow-x-auto max-h-96"><code>{{ JSON.stringify(log.dados_novos || { status: "Nenhum payload novo registrado" }, null, 2) }}</code></pre>
                </div>

                <div v-else-if="payloadTab === 'anteriores'">
                    <pre class="bg-slate-900 text-amber-300 p-4 rounded-sm text-xs font-mono overflow-x-auto max-h-96"><code>{{ JSON.stringify(log.dados_anteriores || { status: "Nenhum dado anterior registrado (criação inicial ou ação pontual)" }, null, 2) }}</code></pre>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
