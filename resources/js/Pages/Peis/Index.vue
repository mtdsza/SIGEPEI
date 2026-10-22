<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientPagination from '@/Components/ClientPagination.vue';

const props = defineProps({
    peis: {
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

const filteredPeis = computed(() => {
    let list = props.peis;

    if (!isAdminOrNai.value || !showInactive.value) {
        list = list.filter(item => item.ativo !== false);
    }

    if (!searchQuery.value) return list;

    const query = searchQuery.value.toLowerCase();
    return list.filter(item =>
        item.aluno.toLowerCase().includes(query) ||
        item.codigo.toLowerCase().includes(query) ||
        item.turma.toLowerCase().includes(query) ||
        item.ano.toString().includes(query)
    );
});

watch([searchQuery, showInactive], () => {
    currentPage.value = 1;
});

const paginatedPeis = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredPeis.value.slice(start, start + itemsPerPage);
});

const toggleStatus = (item) => {
    const action = item.ativo ? 'inativar' : 'reativar';
    if (confirm(`Deseja ${action} o PEI ${item.codigo}?`)) {
        router.delete(route('peis.destroy', item.id));
    }
};
</script>

<template>
    <Head title="PEIs - Planos Educacionais Individualizados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Planos Educacionais Individualizados (PEIs)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Gerenciamento e acompanhamento dos planos educacionais dos alunos.
                    </p>
                </div>
                <Link
                    v-if="isAdminOrNai"
                    :href="route('peis.create')"
                    class="inline-flex items-center px-3.5 py-2 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Novo PEI
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
                            placeholder="Buscar por aluno, código, turma ou ano..."
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
                        <span>Mostrar desativados</span>
                    </label>
                </div>

                <div class="text-xs text-slate-500 dark:text-zinc-400">
                    Total: {{ filteredPeis.length }} registro(s) encontrado(s)
                </div>
            </div>

            <!-- listagem tabular de peis -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-zinc-300">
                    <thead class="bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-200 dark:border-zinc-700 font-semibold text-slate-700 dark:text-zinc-200 uppercase text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Aluno</th>
                            <th class="px-4 py-3">Turma</th>
                            <th class="px-4 py-3">Ano</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-700/60">
                        <tr
                            v-for="item in paginatedPeis"
                            :key="item.id"
                            :class="[
                                'transition-colors',
                                !item.ativo ? 'bg-red-50/40 dark:bg-red-950/20 text-slate-500 dark:text-zinc-400' : 'hover:bg-slate-50 dark:hover:bg-zinc-700/40'
                            ]"
                        >
                            <td class="px-4 py-3 font-mono font-semibold text-[11px] text-slate-700 dark:text-zinc-300">{{ item.codigo }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ item.aluno }}</td>
                            <td class="px-4 py-3">{{ item.turma }}</td>
                            <td class="px-4 py-3">{{ item.ano }}</td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="item.ativo"
                                    class="px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                >
                                    {{ item.status || 'Ativo' }}
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800"
                                >
                                    Inativo
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link
                                    :href="route('peis.show', item.id)"
                                    class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 font-medium rounded-sm border border-slate-300 dark:border-zinc-600"
                                >
                                    Abrir PEI
                                </Link>
                                <button
                                    v-if="isAdminOrNai"
                                    type="button"
                                    @click="toggleStatus(item)"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 font-medium rounded-sm border transition-colors',
                                        item.ativo
                                            ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 border-red-300 dark:border-red-800'
                                            : 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
                                    ]"
                                >
                                    {{ item.ativo ? 'Inativar' : 'Reativar' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredPeis.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400 text-xs">
                                Nenhum PEI encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- paginação da listagem de peis -->
            <ClientPagination
                :total-items="filteredPeis.length"
                :items-per-page="itemsPerPage"
                v-model="currentPage"
                item-label="PEI(s)"
            />
        </div>
    </AuthenticatedLayout>
</template>
