<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useUnsavedChanges } from '@/composables/useUnsavedChanges';

const props = defineProps({
    anosLetivos: {
        type: Array,
        default: () => [],
    },
});

const TRIMESTRES = [1, 2, 3];
const CAMPOS_DATAS = [
    'trimestre1_inicio', 'trimestre1_fim',
    'trimestre2_inicio', 'trimestre2_fim',
    'trimestre3_inicio', 'trimestre3_fim',
];

// identificador local para itens ainda não salvos (usado como chave de renderização)
let proximoUid = 1;
const prepararAnos = (lista) => lista.map((a) => ({ ...a, _uid: a.id ? `id-${a.id}` : `novo-${proximoUid++}` }));

const form = useForm({
    anos: prepararAnos(props.anosLetivos),
});

// fotografia do último estado salvo, usada para detectar alterações pendentes
const snapshot = (lista) => JSON.stringify(lista.map(({ _uid, ...resto }) => resto));
const estadoSalvo = ref(snapshot(form.anos));
const possuiAlteracoes = computed(() => snapshot(form.anos) !== estadoSalvo.value);

// mensagens de retorno exibidas no topo da página
const feedback = ref(null);
let feedbackTimer = null;
const mostrarFeedback = (tipo, texto) => {
    feedback.value = { tipo, texto };
    clearTimeout(feedbackTimer);
    feedbackTimer = setTimeout(() => (feedback.value = null), 5000);
};

const hojeIso = new Date().toLocaleDateString('sv-SE');

const statusDoAno = (item) => {
    if (!item.id) return { rotulo: 'Novo (não salvo)', classe: 'bg-sky-100 dark:bg-sky-950 text-sky-800 dark:text-sky-300 border-sky-200 dark:border-sky-800' };
    if (item.concluido) return { rotulo: 'Concluído', classe: 'bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-200 border-slate-300 dark:border-zinc-600' };
    if (item.trimestre1_inicio && item.trimestre1_inicio <= hojeIso && (!item.trimestre3_fim || hojeIso <= item.trimestre3_fim)) {
        return { rotulo: 'Em andamento', classe: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' };
    }
    return { rotulo: 'Em planejamento', classe: 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800' };
};

const trimestreVigente = (item) => {
    if (item.concluido) return null;
    return TRIMESTRES.find((t) => {
        const ini = item[`trimestre${t}_inicio`];
        const fim = item[`trimestre${t}_fim`];
        return ini && fim && ini <= hojeIso && hojeIso <= fim;
    }) ?? null;
};

const erro = (index, campo) => form.errors[`anos.${index}.${campo}`];
const errosGerais = computed(() => Object.entries(form.errors).filter(([k]) => !k.startsWith('anos.')).map(([, v]) => v));

const adicionarAno = () => {
    const anos = form.anos.map((a) => Number(a.ano)).filter(Boolean);
    const proximoAno = anos.length ? Math.max(...anos) + 1 : new Date().getFullYear();
    form.anos.unshift({
        id: null,
        _uid: `novo-${proximoUid++}`,
        ano: proximoAno,
        concluido: false,
        ...Object.fromEntries(CAMPOS_DATAS.map((c) => [c, ''])),
    });
    form.clearErrors();
};

const removendoId = ref(null);

const removerAno = (index) => {
    const item = form.anos[index];
    if (item.concluido) return;

    // anos ainda não salvos são descartados apenas localmente
    if (!item.id) {
        form.anos.splice(index, 1);
        form.clearErrors();
        return;
    }

    if (!confirm(`Deseja remover o ano letivo ${item.ano} do calendário? Esta ação não pode ser desfeita.`)) {
        return;
    }

    removendoId.value = item.id;
    router.delete(route('calendario.destroy', item.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // remove o item localmente preservando as demais edições ainda não salvas
            const idx = form.anos.findIndex((a) => a.id === item.id);
            if (idx !== -1) form.anos.splice(idx, 1);
            const salvo = JSON.parse(estadoSalvo.value).filter((a) => a.id !== item.id);
            estadoSalvo.value = JSON.stringify(salvo);
            form.clearErrors();
            mostrarFeedback('sucesso', `Ano letivo ${item.ano} removido.`);
        },
        onError: (errors) => {
            mostrarFeedback('erro', errors.calendario || 'Não foi possível remover o ano letivo.');
        },
        onFinish: () => {
            removendoId.value = null;
        },
    });
};

const salvar = () => {
    form
        .transform((data) => ({
            anos: data.anos.map(({ _uid, ...resto }) => resto),
        }))
        .put(route('calendario.update'), {
            preserveScroll: true,
            onSuccess: () => {
                form.anos = prepararAnos(props.anosLetivos);
                estadoSalvo.value = snapshot(form.anos);
                mostrarFeedback('sucesso', 'Calendário salvo com sucesso.');
            },
            onError: () => {
                mostrarFeedback('erro', 'Verifique os campos destacados antes de salvar.');
            },
        });
};

const descartarAlteracoes = () => {
    if (!confirm('Descartar todas as alterações não salvas?')) return;
    form.anos = prepararAnos(props.anosLetivos);
    form.clearErrors();
};

// alerta o usuário ao sair da página com alterações pendentes
useUnsavedChanges(possuiAlteracoes);

onBeforeUnmount(() => {
    clearTimeout(feedbackTimer);
});

const classeInput = (temErro) => [
    'w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border rounded-sm text-xs focus:ring-1 disabled:bg-slate-100 disabled:text-slate-500 dark:disabled:bg-zinc-800 dark:disabled:text-zinc-500 disabled:cursor-not-allowed',
    temErro
        ? 'border-red-400 dark:border-red-700 focus:ring-red-500 focus:border-red-500'
        : 'border-slate-300 dark:border-zinc-700 focus:ring-emerald-600 focus:border-emerald-600',
];
</script>

<template>
    <Head title="Calendário Escolar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Calendário Escolar e Trimestres
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Definição das datas de início e término dos períodos de cada trimestre letivo.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        v-if="possuiAlteracoes"
                        id="calendario-descartar"
                        type="button"
                        @click="descartarAlteracoes"
                        :disabled="form.processing"
                        class="inline-flex items-center px-3 py-2 text-slate-600 dark:text-zinc-300 hover:underline text-xs font-semibold disabled:opacity-50"
                    >
                        Descartar
                    </button>
                    <button
                        id="calendario-adicionar-ano"
                        type="button"
                        @click="adicionarAno"
                        :disabled="form.processing"
                        class="inline-flex items-center px-3.5 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-800 dark:text-zinc-100 text-xs font-semibold rounded-sm transition-colors disabled:opacity-50"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar Ano
                    </button>
                    <button
                        id="calendario-salvar"
                        type="button"
                        @click="salvar"
                        :disabled="form.processing || !possuiAlteracoes"
                        class="relative inline-flex items-center px-3.5 py-2 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="possuiAlteracoes && !form.processing" class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                        </span>
                        <svg v-if="form.processing" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Salvando...' : 'Salvar Calendário' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- mensagens de retorno das operações -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="feedback"
                    :class="[
                        'px-4 py-3 text-xs font-semibold rounded-sm border-l-4 border-y border-r shadow-xs flex items-center justify-between',
                        feedback.tipo === 'sucesso'
                            ? 'bg-emerald-50 dark:bg-emerald-950/30 border-[#006633] border-y-emerald-200 border-r-emerald-200 dark:border-y-emerald-900 dark:border-r-emerald-900 text-emerald-900 dark:text-emerald-200'
                            : 'bg-red-50 dark:bg-red-950/30 border-red-600 border-y-red-200 border-r-red-200 dark:border-y-red-900 dark:border-r-red-900 text-red-900 dark:text-red-200',
                    ]"
                >
                    <span>{{ feedback.texto }}</span>
                    <button type="button" class="ml-4 opacity-60 hover:opacity-100" @click="feedback = null" aria-label="Fechar">&times;</button>
                </div>
            </transition>

            <div v-if="errosGerais.length" class="px-4 py-3 text-xs rounded-sm bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-200">
                <div v-for="(msg, i) in errosGerais" :key="i">{{ msg }}</div>
            </div>

            <div
                v-if="possuiAlteracoes"
                class="px-4 py-2.5 text-xs rounded-sm bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 text-amber-900 dark:text-amber-200 flex items-center gap-2"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Existem alterações não salvas. Clique em <strong>Salvar Calendário</strong> para aplicá-las.
            </div>

            <!-- estado vazio -->
            <div
                v-if="form.anos.length === 0"
                class="bg-white dark:bg-zinc-800 border border-dashed border-slate-300 dark:border-zinc-700 rounded-sm p-10 text-center"
            >
                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-zinc-200">Nenhum ano letivo cadastrado</p>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Clique em “Adicionar Ano” para definir o primeiro calendário.</p>
            </div>

            <div
                v-for="(item, index) in form.anos"
                :key="item._uid"
                :class="[
                    'bg-white dark:bg-zinc-800 border shadow-sm rounded-sm p-6 transition-colors',
                    item.concluido ? 'border-slate-200 dark:border-zinc-700 opacity-90' : 'border-slate-200 dark:border-zinc-700',
                    !item.id ? 'ring-1 ring-sky-300 dark:ring-sky-800' : '',
                ]"
            >
                <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-zinc-700 gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-slate-900 dark:text-white">Ano Letivo</span>
                            <input
                                v-if="!item.id"
                                :id="`calendario-ano-${index}`"
                                type="number"
                                min="2000"
                                max="2100"
                                v-model.number="item.ano"
                                :class="[classeInput(erro(index, 'ano')), 'w-24 text-base font-bold']"
                            />
                            <span v-else class="text-lg font-bold text-slate-900 dark:text-white">{{ item.ano }}</span>
                        </div>
                        <span :class="['px-2 py-0.5 text-[10px] font-semibold rounded-sm border', statusDoAno(item).classe]">
                            {{ statusDoAno(item).rotulo }}
                        </span>
                        <span
                            v-if="trimestreVigente(item)"
                            class="px-2 py-0.5 text-[10px] font-semibold rounded-sm bg-[#006633] text-white"
                        >
                            {{ trimestreVigente(item) }}º trimestre em curso
                        </span>
                        <span v-if="erro(index, 'ano')" class="text-xs text-red-600 w-full sm:w-auto">{{ erro(index, 'ano') }}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <label
                            class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-zinc-300 cursor-pointer select-none bg-slate-50 dark:bg-zinc-900 px-2.5 py-1.5 rounded-sm border border-slate-200 dark:border-zinc-700"
                            :title="item.concluido ? 'Desmarque para reabrir a edição deste ano letivo' : 'Anos concluídos ficam bloqueados para edição e remoção'"
                        >
                            <input
                                :id="`calendario-concluido-${index}`"
                                type="checkbox"
                                v-model="item.concluido"
                                class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                            />
                            <span>Marcar como concluído</span>
                        </label>
                        <button
                            v-if="!item.concluido"
                            :id="`calendario-remover-${index}`"
                            type="button"
                            @click="removerAno(index)"
                            :disabled="removendoId === item.id && item.id !== null"
                            class="inline-flex items-center px-2.5 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 text-xs font-semibold rounded-sm border border-red-300 dark:border-red-800 transition-colors disabled:opacity-50"
                        >
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ removendoId === item.id && item.id !== null ? 'Removendo...' : 'Remover' }}
                        </button>
                        <span v-else class="inline-flex items-center text-[11px] text-slate-500 dark:text-zinc-400">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Bloqueado
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="t in TRIMESTRES"
                        :key="t"
                        :class="[
                            'p-4 border rounded-sm space-y-3 transition-colors',
                            trimestreVigente(item) === t
                                ? 'bg-emerald-50/70 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800'
                                : 'bg-slate-50 dark:bg-zinc-900/60 border-slate-200 dark:border-zinc-700/80',
                        ]"
                    >
                        <div class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">
                            {{ t }}º Trimestre
                        </div>
                        <div>
                            <label :for="`calendario-${index}-t${t}-inicio`" class="block text-[11px] font-medium text-slate-600 dark:text-zinc-400 mb-1">
                                Data de Início
                            </label>
                            <input
                                :id="`calendario-${index}-t${t}-inicio`"
                                type="date"
                                v-model="item[`trimestre${t}_inicio`]"
                                :disabled="item.concluido"
                                :class="classeInput(erro(index, `trimestre${t}_inicio`))"
                            />
                            <div v-if="erro(index, `trimestre${t}_inicio`)" class="text-[11px] text-red-600 mt-1">
                                {{ erro(index, `trimestre${t}_inicio`) }}
                            </div>
                        </div>
                        <div>
                            <label :for="`calendario-${index}-t${t}-fim`" class="block text-[11px] font-medium text-slate-600 dark:text-zinc-400 mb-1">
                                Data de Término
                            </label>
                            <input
                                :id="`calendario-${index}-t${t}-fim`"
                                type="date"
                                v-model="item[`trimestre${t}_fim`]"
                                :min="item[`trimestre${t}_inicio`] || undefined"
                                :disabled="item.concluido"
                                :class="classeInput(erro(index, `trimestre${t}_fim`))"
                            />
                            <div v-if="erro(index, `trimestre${t}_fim`)" class="text-[11px] text-red-600 mt-1">
                                {{ erro(index, `trimestre${t}_fim`) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
