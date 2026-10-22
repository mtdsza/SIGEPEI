<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientPagination from '@/Components/ClientPagination.vue';

const props = defineProps({
    disciplinas: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));

const searchQuery = ref('');
const showInactive = ref(false);
const currentPage = ref(1);
const itemsPerPage = 10;

const filteredDisciplinas = computed(() => {
    let list = props.disciplinas;

    if (!isAdmin.value || !showInactive.value) {
        list = list.filter(item => item.ativo !== false);
    }

    if (!searchQuery.value) return list;

    const query = searchQuery.value.toLowerCase();
    return list.filter(item =>
        item.nome.toLowerCase().includes(query) ||
        item.codigo.toLowerCase().includes(query)
    );
});

watch([searchQuery, showInactive], () => {
    currentPage.value = 1;
});

const paginatedDisciplinas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredDisciplinas.value.slice(start, start + itemsPerPage);
});

const toggleStatus = (disciplina) => {
    const action = disciplina.ativo ? 'inativar' : 'reativar';
    if (confirm(`Deseja ${action} a disciplina "${disciplina.nome}"?`)) {
        router.delete(route('disciplinas.destroy', disciplina.id));
    }
};
</script>

<template>
    <Head title="Disciplinas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Disciplinas
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Gerenciamento da grade curricular e disciplinas do sistema.
                    </p>
                </div>
                <Link
                    :href="route('disciplinas.create')"
                    class="inline-flex items-center px-3.5 py-2 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Nova Disciplina
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
                            placeholder="Buscar por código ou nome da disciplina..."
                            class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 dark:text-zinc-200"
                        />
                    </div>

                    <!-- controle de exibição de registros inativos para administradores -->
                    <label
                        v-if="isAdmin"
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
                    Total: {{ filteredDisciplinas.length }} disciplina(s) encontrada(s)
                </div>
            </div>

            <!-- listagem tabular de disciplinas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                    <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Nome da Disciplina</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr
                            v-for="disciplina in paginatedDisciplinas"
                            :key="disciplina.id"
                            :class="[
                                'transition-colors',
                                !disciplina.ativo ? 'bg-red-50/40 dark:bg-red-950/20 text-slate-500 dark:text-zinc-400' : 'hover:bg-slate-50 dark:hover:bg-zinc-700/40'
                            ]"
                        >
                            <td class="px-4 py-3 font-mono font-medium text-[11px] text-slate-600 dark:text-zinc-300">{{ disciplina.codigo }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ disciplina.nome }}</td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="disciplina.ativo"
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
                                    :href="route('disciplinas.edit', disciplina.id)"
                                    class="inline-flex items-center px-2 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-300 font-medium rounded-sm border border-amber-300 dark:border-amber-700"
                                >
                                    Editar
                                </Link>
                                <button
                                    type="button"
                                    @click="toggleStatus(disciplina)"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 font-medium rounded-sm border transition-colors',
                                        disciplina.ativo
                                            ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 border-red-300 dark:border-red-800'
                                            : 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
                                    ]"
                                >
                                    {{ disciplina.ativo ? 'Inativar' : 'Reativar' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredDisciplinas.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                Nenhuma disciplina encontrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- paginação da listagem de disciplinas -->
            <ClientPagination
                :total-items="filteredDisciplinas.length"
                :items-per-page="itemsPerPage"
                v-model="currentPage"
                item-label="disciplina(s)"
            />
        </div>
    </AuthenticatedLayout>
</template>
