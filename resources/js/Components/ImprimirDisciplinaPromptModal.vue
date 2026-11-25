<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    peiId: {
        type: [Number, String],
        default: null,
    },
    peiCodigo: {
        type: String,
        default: '',
    },
    alunoNome: {
        type: String,
        default: '',
    },
    disciplinaId: {
        type: [Number, String],
        default: null,
    },
    disciplinaCodigo: {
        type: String,
        default: '',
    },
    disciplinaNome: {
        type: String,
        default: '',
    },
    trimestre: {
        type: Number,
        default: 1,
    },
});

const emit = defineEmits(['close']);

const formato = ref('pdf');
const escopo = ref('trimestre'); // 'trimestre' | 'todos'

watch(() => props.show, (val) => {
    if (val) {
        formato.value = 'pdf';
        escopo.value = 'trimestre';
    }
});

const gerarRelatorio = () => {
    if (!props.peiId || !props.disciplinaId) return;

    const params = new URLSearchParams();
    params.append('formato', formato.value);
    if (escopo.value === 'trimestre' && props.trimestre) {
        params.append('trimestre', String(props.trimestre));
    }

    const url = `${route('peis.disciplina-relatorio', [props.peiId, props.disciplinaId])}?${params.toString()}`;
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
        v-if="show && peiId && disciplinaId"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
        @click.self="emit('close')"
    >
        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-lg w-full p-5 sm:p-6 space-y-5 text-xs">
            <!-- cabeçalho -->
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 dark:border-zinc-700 pb-3">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-[#006633] dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Preenchimento salvo com sucesso
                    </span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Deseja imprimir o relatório desta disciplina?
                    </h2>
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

            <!-- resumo da disciplina preenchida -->
            <div class="p-3 rounded-sm bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 space-y-1">
                <div class="font-bold text-slate-900 dark:text-white text-xs">
                    <span v-if="disciplinaCodigo" class="font-mono text-[#006633] dark:text-emerald-400">[{{ disciplinaCodigo }}]</span>
                    {{ disciplinaNome }}
                </div>
                <div class="text-[11px] text-slate-600 dark:text-zinc-400">
                    Discente: <strong class="text-slate-800 dark:text-zinc-200">{{ alunoNome }}</strong>
                    <span v-if="peiCodigo" class="ml-1.5 font-mono">({{ peiCodigo }})</span>
                </div>
            </div>

            <!-- escopo do relatório -->
            <div class="space-y-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                    Período a incluir
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label
                        class="flex items-center gap-2 p-2.5 rounded-sm border cursor-pointer transition-colors"
                        :class="escopo === 'trimestre'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40'"
                    >
                        <input type="radio" value="trimestre" v-model="escopo" class="text-[#006633] focus:ring-emerald-600" />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">Apenas {{ trimestre }}º Trimestre</span>
                    </label>
                    <label
                        class="flex items-center gap-2 p-2.5 rounded-sm border cursor-pointer transition-colors"
                        :class="escopo === 'todos'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40'"
                    >
                        <input type="radio" value="todos" v-model="escopo" class="text-[#006633] focus:ring-emerald-600" />
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">Todos os Trimestres</span>
                    </label>
                </div>
            </div>

            <!-- formato -->
            <div class="space-y-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                    Formato do Arquivo
                </span>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        class="flex items-center gap-2.5 p-3 rounded-sm border cursor-pointer transition-colors"
                        :class="formato === 'pdf'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40'"
                    >
                        <input type="radio" value="pdf" v-model="formato" class="text-[#006633] focus:ring-emerald-600" />
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">PDF (.pdf)</div>
                            <div class="text-[10px] text-slate-500 dark:text-zinc-400">Pronto para impressão</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-2.5 p-3 rounded-sm border cursor-pointer transition-colors"
                        :class="formato === 'docx'
                            ? 'border-[#006633] dark:border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30'
                            : 'border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40'"
                    >
                        <input type="radio" value="docx" v-model="formato" class="text-[#006633] focus:ring-emerald-600" />
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">Word (.docx)</div>
                            <div class="text-[10px] text-slate-500 dark:text-zinc-400">Documento editável</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- ações -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-zinc-700">
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 cursor-pointer transition-colors"
                >
                    Não, fechar
                </button>
                <button
                    type="button"
                    @click="gerarRelatorio"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-bold rounded-sm shadow-sm cursor-pointer transition-colors"
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

