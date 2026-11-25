<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientPagination from '@/Components/ClientPagination.vue';

const props = defineProps({
    turmas: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const isAdminOrNai = computed(() => Boolean(page.props.auth?.user?.is_admin || page.props.auth?.user?.cargo === 'NAI'));

const searchQuery = ref('');
const showInactive = ref(false);
const currentPage = ref(1);
const itemsPerPage = 10;

const filteredTurmas = computed(() => {
    let list = props.turmas;

    if (!isAdminOrNai.value || !showInactive.value) {
        list = list.filter(item => item.ativo !== false);
    }

    if (!searchQuery.value) return list;

    const query = searchQuery.value.toLowerCase();
    return list.filter(item =>
        (item.nome ?? '').toLowerCase().includes(query) ||
        String(item.ano ?? '').includes(query)
    );
});

watch([searchQuery, showInactive], () => {
    currentPage.value = 1;
});

const paginatedTurmas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredTurmas.value.slice(start, start + itemsPerPage);
});

const toggleStatus = (turma) => {
    const action = turma.ativo ? 'inativar' : 'reativar';
    if (confirm(`Deseja ${action} a turma "${turma.nome}"?`)) {
        router.delete(route('turmas.destroy', turma.id));
    }
};
</script>

<template>
    <Head title="Turmas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Turmas
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Gerenciamento de turmas, cursos e alocação de disciplinas e docentes.
                    </p>
                </div>
                <Link
                    v-if="isAdminOrNai"
                    :href="route('turmas.create')"
                    class="inline-flex items-center px-3.5 py-2 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Nova Turma
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm overflow-hidden">
            <!-- barra de filtros e busca rápida -->
            <div class="p-4 border-b border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row gap-3 justify-between items-center text-xs">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full sm:w-auto">
                    <div class="w-full sm:w-72">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Buscar por curso ou ano letivo..."
                            class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        />
                    </div>

                    <!-- controle de exibição de registros inativos para administradores e equipe nai -->
                    <label
                        v-if="isAdminOrNai"
                        class="inline-flex items-center space-x-2 text-xs font-medium text-slate-700 dark:text-zinc-300 cursor-pointer select-none bg-white dark:bg-zinc-900 px-2.5 py-1.5 rounded-sm border border-slate-200 dark:border-zinc-700"
                    >
                        <input
                            type="checkbox"
                            v-model="showInactive"
                            class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                        />
                        <span>Mostrar desativadas</span>
                    </label>
                </div>

                <div class="text-xs text-slate-500 dark:text-zinc-400">
                    Total: {{ filteredTurmas.length }} turma(s) encontrada(s)
                </div>
            </div>

            <!-- listagem tabular de turmas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-zinc-300">
                    <thead class="bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-200 dark:border-zinc-700 font-semibold text-slate-700 dark:text-zinc-200 uppercase text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Curso / Turma</th>
                            <th class="px-4 py-3">Ano Letivo</th>
                            <th class="px-4 py-3">Disciplinas</th>
                            <th class="px-4 py-3">Alunos</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-700/60">
                        <tr
                            v-for="turma in paginatedTurmas"
                            :key="turma.id"
                            :class="[
                                'transition-colors',
                                !turma.ativo ? 'bg-red-50/40 dark:bg-red-950/20 text-slate-500 dark:text-zinc-400' : 'hover:bg-slate-50 dark:hover:bg-zinc-700/40'
                            ]"
                        >
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ turma.nome }}</td>
                            <td class="px-4 py-3">{{ turma.ano }}</td>
                            <td class="px-4 py-3">{{ turma.disciplinas_qtd }}</td>
                            <td class="px-4 py-3">{{ turma.alunos_qtd }}</td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="turma.ativo"
                                    class="px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                >
                                    Ativa
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800"
                                >
                                    Inativa
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link
                                    :href="route('turmas.show', turma.id)"
                                    class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 font-medium rounded-sm border border-slate-300 dark:border-zinc-600"
                                >
                                    Ver
                                </Link>
                                <Link
                                    v-if="isAdminOrNai"
                                    :href="route('turmas.edit', turma.id)"
                                    class="inline-flex items-center px-2 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-300 font-medium rounded-sm border border-amber-300 dark:border-amber-700"
                                >
                                    Editar
                                </Link>
                                <button
                                    v-if="isAdminOrNai"
                                    type="button"
                                    @click="toggleStatus(turma)"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 font-medium rounded-sm border transition-colors',
                                        turma.ativo
                                            ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 border-red-300 dark:border-red-800'
                                            : 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
                                    ]"
                                >
                                    {{ turma.ativo ? 'Inativar' : 'Reativar' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredTurmas.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                Nenhuma turma encontrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- paginação da listagem de turmas -->
            <ClientPagination
                :total-items="filteredTurmas.length"
                :items-per-page="itemsPerPage"
                v-model="currentPage"
                item-label="turma(s)"
            />
        </div>
    </AuthenticatedLayout>
</template>
