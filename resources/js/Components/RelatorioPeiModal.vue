<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pei: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);

const formato = ref('pdf');
const secoes = ref(['ficha_aluno', 'ficha_pei', 'nai']);
const apoios = ref([]);
const disciplinas = ref([]);
const trimestres = ref([1, 2, 3]);

const initSelection = () => {
    formato.value = 'pdf';
    secoes.value = ['ficha_aluno', 'ficha_pei', 'nai'];
    apoios.value = (props.pei.equipes_apoio || []).map(a => a.id);
    disciplinas.value = (props.pei.disciplinas || []).map(d => d.id);
    trimestres.value = [1, 2, 3];
};

watch(() => props.show, (val) => {
    if (val) {
        initSelection();
    }
});

const temAbaTrimestralSelecionada = computed(() => {
    return secoes.value.includes('nai') || apoios.value.length > 0 || disciplinas.value.length > 0;
});

const totalSelecionado = computed(() => {
    return secoes.value.length + apoios.value.length + disciplinas.value.length;
});

const podeGerar = computed(() => {
    if (totalSelecionado.value === 0) return false;
    if (temAbaTrimestralSelecionada.value && trimestres.value.length === 0) return false;
    return true;
});

const selecionarTudo = () => {
    secoes.value = ['ficha_aluno', 'ficha_pei', 'nai'];
    apoios.value = (props.pei.equipes_apoio || []).map(a => a.id);
    disciplinas.value = (props.pei.disciplinas || []).map(d => d.id);
    trimestres.value = [1, 2, 3];
};

const limparTudo = () => {
    secoes.value = [];
    apoios.value = [];
    disciplinas.value = [];
};

const todasDisciplinasSelecionadas = computed(() => {
    const lista = props.pei.disciplinas || [];
    return lista.length > 0 && disciplinas.value.length === lista.length;
});

const toggleTodasDisciplinas = () => {
    if (todasDisciplinasSelecionadas.value) {
        disciplinas.value = [];
    } else {
        disciplinas.value = (props.pei.disciplinas || []).map(d => d.id);
    }
};

const gerarRelatorio = () => {
    if (!podeGerar.value) return;

    const params = new URLSearchParams();
    params.append('formato', formato.value);

    secoes.value.forEach(s => params.append('secoes[]', s));
    apoios.value.forEach(id => params.append('apoios[]', String(id)));
    disciplinas.value.forEach(id => params.append('disciplinas[]', String(id)));
    trimestres.value.forEach(t => params.append('trimestres[]', String(t)));

    const url = `${route('peis.relatorio', props.pei.id)}?${params.toString()}`;
    window.location.assign(url);
    emit('close');
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
        @click.self="emit('close')"
    >
        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto my-6 text-xs">
            <!-- cabeçalho -->
            <div class="flex items-start justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950 text-[#006633] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ pei.codigo }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-zinc-400 font-medium">
                            {{ pei.aluno?.nome }}
                        </span>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mt-1">
                        Imprimir Relatório do PEI
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                        Escolha o formato e selecione quais abas e disciplinas deseja incluir no documento.
                    </p>
                </div>
                <button
                    type="button"
                    @click="emit('close')"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-2xl leading-none p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 cursor-pointer"
                    title="Fechar (Esc)"
                >
                    &times;
                </button>
            </div>

            <!-- formato -->
            <div class="space-y-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                    1. Formato do Arquivo
                </span>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        class="flex items-center gap-3 p-3 rounded-sm border cursor-pointer transition-colors"
                        :class="formato === 'pdf'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/40 hover:bg-slate-100 dark:hover:bg-zinc-700/40'"
                    >
                        <input type="radio" value="pdf" v-model="formato" class="text-[#006633] focus:ring-emerald-600" />
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">PDF (.pdf)</div>
                            <div class="text-[11px] text-slate-500 dark:text-zinc-400">Ideal para impressão e arquivamento</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-sm border cursor-pointer transition-colors"
                        :class="formato === 'docx'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/40 hover:bg-slate-100 dark:hover:bg-zinc-700/40'"
                    >
                        <input type="radio" value="docx" v-model="formato" class="text-[#006633] focus:ring-emerald-600" />
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">Word (.docx)</div>
                            <div class="text-[11px] text-slate-500 dark:text-zinc-400">Editável no Word / LibreOffice</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- seleção de abas -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                        2. Abas a Incluir no Relatório
                    </span>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="selecionarTudo" class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline cursor-pointer">
                            Selecionar tudo
                        </button>
                        <button type="button" @click="limparTudo" class="text-[11px] font-semibold text-slate-500 dark:text-zinc-400 hover:underline cursor-pointer">
                            Limpar
                        </button>
                    </div>
                </div>

                <div class="p-2.5 rounded-sm bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 text-[11px] text-slate-600 dark:text-zinc-400">
                    Os <strong>Dados Gerais do PEI</strong> (identificação do discente, matrícula, turma, diagnóstico e responsáveis) são incluídos automaticamente no cabeçalho do relatório.
                </div>

                <!-- abas principais -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 hover:bg-slate-50 dark:hover:bg-zinc-700/40 cursor-pointer">
                        <input type="checkbox" value="ficha_aluno" v-model="secoes" class="rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900" />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">Ficha do Aluno</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 hover:bg-slate-50 dark:hover:bg-zinc-700/40 cursor-pointer">
                        <input type="checkbox" value="ficha_pei" v-model="secoes" class="rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900" />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">Ficha do PEI</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 hover:bg-slate-50 dark:hover:bg-zinc-700/40 cursor-pointer">
                        <input type="checkbox" value="nai" v-model="secoes" class="rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900" />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">NAI (Trimestres)</span>
                    </label>
                </div>

                <!-- equipes de apoio -->
                <div v-if="pei.equipes_apoio && pei.equipes_apoio.length > 0" class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-zinc-700">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400">
                        Equipes de Apoio
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label
                            v-for="apoio in pei.equipes_apoio"
                            :key="apoio.id"
                            class="flex items-center gap-2 p-2.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 hover:bg-slate-50 dark:hover:bg-zinc-700/40 cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :value="apoio.id"
                                v-model="apoios"
                                class="rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900"
                            />
                            <span class="font-semibold text-slate-800 dark:text-zinc-200 truncate">{{ apoio.nome }}</span>
                        </label>
                    </div>
                </div>

                <!-- disciplinas individuais -->
                <div v-if="pei.disciplinas && pei.disciplinas.length > 0" class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400">
                            Disciplinas ({{ disciplinas.length }}/{{ pei.disciplinas.length }})
                        </span>
                        <button
                            type="button"
                            @click="toggleTodasDisciplinas"
                            class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline cursor-pointer"
                        >
                            {{ todasDisciplinasSelecionadas ? 'Desmarcar todas as disciplinas' : 'Marcar todas as disciplinas' }}
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                        <label
                            v-for="disc in pei.disciplinas"
                            :key="disc.id"
                            class="flex items-start gap-2 p-2.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 hover:bg-slate-50 dark:hover:bg-zinc-700/40 cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :value="disc.id"
                                v-model="disciplinas"
                                class="mt-0.5 rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 shrink-0"
                            />
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-800 dark:text-zinc-200 leading-tight">
                                    <span class="font-mono text-[10px] text-[#006633] dark:text-emerald-400">[{{ disc.codigo }}]</span>
                                    {{ disc.nome }}
                                </div>
                                <div v-if="disc.professores?.length" class="text-[10px] text-slate-500 dark:text-zinc-400 truncate mt-0.5">
                                    {{ disc.professores.map(p => p.name).join(', ') }}
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- filtro de trimestres -->
            <div v-if="temAbaTrimestralSelecionada" class="space-y-2 pt-2 border-t border-slate-200 dark:border-zinc-700">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                    3. Trimestres (aplicado a NAI, Equipes de Apoio e Disciplinas)
                </span>
                <div class="flex flex-wrap gap-3">
                    <label
                        v-for="t in [1, 2, 3]"
                        :key="t"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="t"
                            v-model="trimestres"
                            class="rounded-sm border-slate-300 dark:border-zinc-600 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900"
                        />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ t }}º Trimestre</span>
                    </label>
                </div>
            </div>

            <!-- aviso de validação -->
            <div v-if="!podeGerar" class="p-2.5 rounded-sm bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300 font-medium">
                {{ totalSelecionado === 0 ? 'Selecione ao menos uma aba ou disciplina para gerar o relatório.' : 'Selecione ao menos um trimestre para as abas trimestrais marcadas.' }}
            </div>

            <!-- rodapé de ações -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-zinc-700">
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 cursor-pointer transition-colors"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    @click="gerarRelatorio"
                    :disabled="!podeGerar"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-bold rounded-sm shadow-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Gerar e Salvar ({{ formato.toUpperCase() }})</span>
                </button>
            </div>
        </div>
    </div>
</template>

