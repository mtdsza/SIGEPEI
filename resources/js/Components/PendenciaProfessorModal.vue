<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AlunoAvatar from '@/Components/AlunoAvatar.vue';
import { confirmDiscard, useUnsavedChanges } from '@/composables/useUnsavedChanges';
import {
    ESTRATEGIAS_ENSINO_OPCOES,
    ESTRATEGIAS_AVALIACAO_OPCOES,
    TEMPO_OPCOES,
    AVALIACAO_DESEMPENHO_OPCOES,
} from '@/constants/peiProfessorOptions';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pendencia: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const showOrientacoes = ref(true);

const defaultPlanejamento = () => ({
    objetivos: '',
    conteudos: '',
    metodologias_recursos: '',
    estrategias_ensino: [],
    estrategias_ensino_outros: '',
    estrategias_avaliacao: [],
    estrategias_avaliacao_outros: '',
    tempo: '',
    tempo_opcao: '',
    tempo_outro: '',
    avaliacao_resultados: '',
    avaliacao_desempenho: [],
    avaliacao_desempenho_outros: '',
    sugestoes_aproveitamento: '',
});

const form = useForm({
    trimestre: 1,
    grau_autonomia: [],
    especifique_outros: '',
    planejamentos: [],
    avaliacoes: [],
    redirect_to: '',
});

const initForm = () => {
    if (!props.pendencia) return;

    form.trimestre = props.pendencia.trimestre || 1;
    form.redirect_to = route('dashboard', { trimestre: props.pendencia.trimestre });

    const dados = props.pendencia.dados_edicao || {};
    form.grau_autonomia = Array.isArray(dados.grau_autonomia) ? [...dados.grau_autonomia] : [];
    form.especifique_outros = dados.especifique_outros || '';

    // carrega os planejamentos existentes ou inicia com um vazio
    if (Array.isArray(dados.planejamentos) && dados.planejamentos.length > 0) {
        form.planejamentos = dados.planejamentos.map(p => {
            const tempoOpcao = p.tempo_opcao || (TEMPO_OPCOES.includes(p.tempo) ? p.tempo : (p.tempo ? 'Outro' : ''));
            const tempoOutro = p.tempo_outro || (tempoOpcao === 'Outro' ? (p.tempo_outro || p.tempo || '') : '');

            return {
                objetivos: p.objetivos || '',
                conteudos: p.conteudos || '',
                metodologias_recursos: p.metodologias_recursos || '',
                estrategias_ensino: Array.isArray(p.estrategias_ensino) ? [...p.estrategias_ensino] : [],
                estrategias_ensino_outros: p.estrategias_ensino_outros || '',
                estrategias_avaliacao: Array.isArray(p.estrategias_avaliacao) ? [...p.estrategias_avaliacao] : [],
                estrategias_avaliacao_outros: p.estrategias_avaliacao_outros || '',
                tempo: p.tempo || '',
                tempo_opcao: tempoOpcao,
                tempo_outro: tempoOutro,
                avaliacao_resultados: p.avaliacao_resultados || '',
                avaliacao_desempenho: Array.isArray(p.avaliacao_desempenho) ? [...p.avaliacao_desempenho] : [],
                avaliacao_desempenho_outros: p.avaliacao_desempenho_outros || '',
                sugestoes_aproveitamento: p.sugestoes_aproveitamento || '',
            };
        });
    } else {
        form.planejamentos = [defaultPlanejamento()];
    }

    // carrega instrumentos de avaliação padrão
    const tipos = ['prova_escrita', 'trabalhos', 'prova_pratica', 'outros', 'prova_oral'];
    if (Array.isArray(dados.avaliacoes) && dados.avaliacoes.length > 0) {
        form.avaliacoes = tipos.map(tipo => {
            const av = dados.avaliacoes.find(a => a.tipo === tipo) || {};
            return {
                tipo,
                adotado: Boolean(av.adotado),
                valor_1: av.valor_1 || '',
                nota_1: av.nota_1 || '',
                valor_2: av.valor_2 || '',
                nota_2: av.nota_2 || '',
                valor_3: av.valor_3 || '',
                nota_3: av.nota_3 || '',
            };
        });
    } else {
        form.avaliacoes = tipos.map(tipo => ({
            tipo,
            adotado: false,
            valor_1: '',
            nota_1: '',
            valor_2: '',
            nota_2: '',
            valor_3: '',
            nota_3: '',
        }));
    }

    form.defaults();
};

useUnsavedChanges(() => props.show && form.isDirty);

const tentarFechar = () => {
    if (!confirmDiscard(form.isDirty)) return;
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.clearErrors();
        initForm();
    }
});

watch(() => props.pendencia, () => {
    if (props.show) {
        initForm();
    }
});

const addPlanejamentoRow = () => {
    form.planejamentos.push(defaultPlanejamento());
};

const removePlanejamentoRow = (index) => {
    if (form.planejamentos.length > 1) {
        form.planejamentos.splice(index, 1);
    }
};

const submit = () => {
    if (!props.pendencia) return;

    const dadosImpressao = {
        pei_id: props.pendencia.pei_id,
        pei_codigo: props.pendencia.pei_codigo || '',
        aluno_nome: props.pendencia.aluno_nome || '',
        disciplina_id: props.pendencia.disciplina_id,
        disciplina_codigo: props.pendencia.disciplina_codigo || '',
        disciplina_nome: props.pendencia.atribuicao || '',
        trimestre: props.pendencia.trimestre || 1,
    };

    form.post(route('peis.update-disciplina-trimestre', [props.pendencia.pei_id, props.pendencia.disciplina_id]), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('saved', dadosImpressao);
            emit('close');
        },
    });
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && props.show) {
        tentarFechar();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div
        v-if="show && pendencia"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
        @click.self="tentarFechar"
    >
        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-5xl w-full p-5 sm:p-7 space-y-6 max-h-[92vh] overflow-y-auto my-6 text-xs">
            
            <!-- 1. cabeçalho principal do modal -->
            <div class="flex items-start justify-between border-b border-slate-200 dark:border-zinc-700 pb-3.5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-[#006633] dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            {{ pendencia.trimestre }}º Trimestre • Adaptação Curricular
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            Preenchimento Rápido
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-1">
                        [{{ pendencia.disciplina_codigo || 'DISC' }}] {{ pendencia.atribuicao }}
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                        Resolva a pendência pedagógica do discente diretamente do seu painel, sem sair da página.
                    </p>
                </div>
                <button
                    type="button"
                    @click="tentarFechar"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-2xl leading-none p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                    title="Fechar (Esc)"
                >
                    &times;
                </button>
            </div>

            <!-- 2. cartão de contexto do estudante e necessidades específicas -->
            <div class="bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <AlunoAvatar :src="pendencia.aluno_foto_url" :nome="pendencia.aluno_nome" size="lg" />
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ pendencia.aluno_nome }}
                            </h3>
                            <span class="font-mono text-[11px] font-bold text-[#006633] dark:text-emerald-400 bg-white dark:bg-zinc-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-zinc-700">
                                {{ pendencia.pei_codigo }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-zinc-400 mt-0.5">
                            <strong>Turma:</strong> {{ pendencia.turma_nome }}
                            <span v-if="pendencia.aluno_matricula" class="ml-2 font-mono">
                                • Matrícula: {{ pendencia.aluno_matricula }}
                            </span>
                        </div>
                        
                        <!-- tags de necessidades e deficiências do estudante -->
                        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 mr-1">
                                Necessidades:
                            </span>
                            <span
                                v-for="def in (pendencia.deficiencias || [])"
                                :key="def"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800"
                            >
                                • {{ def }}
                            </span>
                            <span
                                v-if="!pendencia.deficiencias || pendencia.deficiencias.length === 0"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
                            >
                                Atendimento Educacional Especializado (AEE)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- status e detalhe da pendência -->
                <div class="shrink-0 w-full md:w-auto p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-sm">
                    <div class="text-[10px] font-bold uppercase text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Status da Pendência
                    </div>
                    <div class="text-xs font-semibold text-amber-800 dark:text-amber-300 mt-0.5">
                        {{ pendencia.detalhe }}
                    </div>
                </div>
            </div>

            <!-- 3. caixa de orientação pedagógica (recolhível) -->
            <div class="bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/80 rounded-sm p-3.5">
                <div class="flex items-center justify-between cursor-pointer select-none" @click="showOrientacoes = !showOrientacoes">
                    <div class="flex items-center gap-2 text-emerald-900 dark:text-emerald-200 font-bold text-xs">
                        <span class="text-emerald-600 dark:text-emerald-400">💡</span>
                        <span>Orientações do NAI para o Preenchimento do Docente</span>
                    </div>
                    <button type="button" class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">
                        {{ showOrientacoes ? 'Ocultar orientações' : 'Ver orientações' }}
                    </button>
                </div>
                <div v-if="showOrientacoes" class="mt-2.5 pt-2.5 border-t border-emerald-200/80 dark:border-emerald-900/60 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-[11px] text-emerald-950 dark:text-emerald-200 leading-relaxed">
                    <div class="space-y-0.5">
                        <strong class="block text-emerald-900 dark:text-emerald-300">1. Objetivos & Conteúdos:</strong>
                        <span>Descreva os objetivos acadêmicos esperados e conteúdos priorizados na sua disciplina para o período.</span>
                    </div>
                    <div class="space-y-0.5">
                        <strong class="block text-emerald-900 dark:text-emerald-300">2. Estratégias Metodológicas:</strong>
                        <span>Marque as estratégias de ensino e condições avaliativas que serão adotadas com o discente.</span>
                    </div>
                    <div class="space-y-0.5">
                        <strong class="block text-emerald-900 dark:text-emerald-300">3. Prazos & Desempenho:</strong>
                        <span>Selecione a estimativa de tempo e registre como o aluno respondeu aos conteúdos e atividades.</span>
                    </div>
                    <div class="space-y-0.5">
                        <strong class="block text-emerald-900 dark:text-emerald-300">4. Avaliações & Notas:</strong>
                        <span>Assinale os instrumentos adotados (provas, trabalhos, etc.) e informe as notas parciais ou finais.</span>
                    </div>
                </div>
            </div>

            <!-- 4. formulário de preenchimento -->
            <form @submit.prevent="submit" class="space-y-6">
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded text-xs text-rose-700 dark:text-rose-300 space-y-1"
                >
                    <p class="font-bold">Verifique os campos antes de salvar:</p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
                    </ul>
                </div>
                <!-- ============================================== -->
                <!-- parte 1: planejamento pedagógico e adaptação -->
                <!-- ============================================== -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#006633] text-white flex items-center justify-center text-[10px] font-bold">1</span>
                                Parte 1: Planejamento Pedagógico & Adaptação Curricular
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                Objetivos, conteúdos curriculares priorizados, estratégias e avaliação de resultados.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addPlanejamentoRow"
                            class="inline-flex items-center px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-[#006633] dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 font-semibold text-xs rounded border border-emerald-300 dark:border-emerald-700 transition-colors gap-1"
                        >
                            <span>+</span>
                            <span>Adicionar Linha</span>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="(plan, pIdx) in form.planejamentos"
                            :key="pIdx"
                            class="p-4 sm:p-5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-4 shadow-2xs"
                        >
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                                <span class="font-bold text-slate-800 dark:text-zinc-200 text-xs flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-zinc-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ pIdx + 1 }}
                                    </span>
                                    Item de Planejamento #{{ pIdx + 1 }}
                                </span>
                                <button
                                    v-if="form.planejamentos.length > 1"
                                    type="button"
                                    @click="removePlanejamentoRow(pIdx)"
                                    class="text-red-600 dark:text-red-400 text-xs font-semibold hover:underline cursor-pointer"
                                >
                                    Remover este item
                                </button>
                            </div>

                            <!-- 1.1 objetivos e conteúdos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                        Objetivos Acadêmicos <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="plan.objetivos"
                                        required
                                        rows="2"
                                        placeholder="Ex: Identificar conceitos fundamentais, compreender processos, desenvolver autonomia..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                        Conteúdos Curriculares Priorizados <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="plan.conteudos"
                                        required
                                        rows="2"
                                        placeholder="Ex: Tópicos principais do currículo selecionados e adaptados para o período..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- 1.2 metodologias / estratégias / recursos -->
                            <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-4">
                                <div class="border-b border-slate-200 dark:border-zinc-700 pb-2 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                            Metodologias / Estratégias / Recursos
                                        </h5>
                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                            Como ensinar? O que usar para ensinar e como avaliar?
                                        </p>
                                    </div>
                                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold uppercase">
                                        Opções do NAI
                                    </span>
                                </div>

                                <!-- 1 - estratégias de ensino -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800 dark:text-zinc-200">
                                            1 - Estratégias de ensino
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            {{ plan.estrategias_ensino.length }} selecionada(s)
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <label
                                            v-for="opcao in ESTRATEGIAS_ENSINO_OPCOES"
                                            :key="opcao"
                                            class="flex items-start gap-2 p-2 rounded-sm border border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/40 hover:bg-slate-100 dark:hover:bg-zinc-700/50 cursor-pointer text-xs select-none transition-colors"
                                            :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-950 dark:text-emerald-200 font-medium': plan.estrategias_ensino.includes(opcao) }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="opcao"
                                                v-model="plan.estrategias_ensino"
                                                class="mt-0.5 rounded-sm border-slate-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-900 shrink-0"
                                            />
                                            <span class="leading-tight">{{ opcao }}</span>
                                        </label>
                                    </div>
                                    <div class="pt-1">
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400 uppercase mb-1">
                                            Outras informações/estratégias utilizadas:
                                        </label>
                                        <input
                                            type="text"
                                            v-model="plan.estrategias_ensino_outros"
                                            placeholder="Descreva outras estratégias de ensino personalizadas..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>
                                </div>

                                <!-- 2 - estratégias e condições de avaliação -->
                                <div class="space-y-2 pt-3 border-t border-slate-200 dark:border-zinc-700/60">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800 dark:text-zinc-200">
                                            2 - Estratégias e condições de avaliação
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            {{ plan.estrategias_avaliacao.length }} selecionada(s)
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <label
                                            v-for="opcao in ESTRATEGIAS_AVALIACAO_OPCOES"
                                            :key="opcao"
                                            class="flex items-start gap-2 p-2 rounded-sm border border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/40 hover:bg-slate-100 dark:hover:bg-zinc-700/50 cursor-pointer text-xs select-none transition-colors"
                                            :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-950 dark:text-emerald-200 font-medium': plan.estrategias_avaliacao.includes(opcao) }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="opcao"
                                                v-model="plan.estrategias_avaliacao"
                                                class="mt-0.5 rounded-sm border-slate-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-900 shrink-0"
                                            />
                                            <span class="leading-tight">{{ opcao }}</span>
                                        </label>
                                    </div>
                                    <div class="pt-1">
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400 uppercase mb-1">
                                            Outras estratégias utilizadas:
                                        </label>
                                        <input
                                            type="text"
                                            v-model="plan.estrategias_avaliacao_outros"
                                            placeholder="Descreva outras condições ou recursos avaliativos..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- 1.3 tempo / prazo de realização -->
                            <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase">
                                    Tempo <span class="text-slate-400 font-normal lowercase">(prazo de realização do trabalho)</span> <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                                    <label
                                        v-for="opcao in TEMPO_OPCOES"
                                        :key="opcao"
                                        class="flex items-center gap-2 p-2 rounded-sm border border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/40 hover:bg-slate-100 dark:hover:bg-zinc-700/50 cursor-pointer text-xs select-none transition-colors"
                                        :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-950 dark:text-emerald-200 font-medium': plan.tempo_opcao === opcao }"
                                    >
                                        <input
                                            type="radio"
                                            :name="`dash_tempo_opcao_${pIdx}`"
                                            :value="opcao"
                                            v-model="plan.tempo_opcao"
                                            class="border-slate-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-900 shrink-0"
                                        />
                                        <span class="leading-tight">{{ opcao }}</span>
                                    </label>
                                </div>
                                <div v-if="plan.tempo_opcao === 'Outro'" class="pt-1">
                                    <input
                                        type="text"
                                        v-model="plan.tempo_outro"
                                        placeholder="Especifique outro prazo de realização..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>
                            </div>

                            <!-- 1.4 avaliação de desempenho / resultados -->
                            <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-3">
                                <div class="flex items-baseline justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                            Avaliação de Desempenho / Resultados
                                        </h5>
                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                            Como o discente respondeu a esse trabalho, quais habilidades e dificuldades apresentadas
                                        </p>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-medium shrink-0">
                                        {{ plan.avaliacao_desempenho.length }} selecionada(s)
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto p-1 border border-slate-200 dark:border-zinc-700/80 rounded-sm bg-slate-50/50 dark:bg-zinc-900/40">
                                    <label
                                        v-for="opcao in AVALIACAO_DESEMPENHO_OPCOES"
                                        :key="opcao"
                                        class="flex items-start gap-2 p-2 rounded-sm border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:bg-slate-100 dark:hover:bg-zinc-700/50 cursor-pointer text-xs select-none transition-colors"
                                        :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-950 dark:text-emerald-200 font-medium': plan.avaliacao_desempenho.includes(opcao) }"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="opcao"
                                            v-model="plan.avaliacao_desempenho"
                                            class="mt-0.5 rounded-sm border-slate-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-900 shrink-0"
                                        />
                                        <span class="leading-tight">{{ opcao }}</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400 uppercase mb-1">
                                        Outras observações sobre o desempenho, avanços e/ou dificuldades:
                                    </label>
                                    <textarea
                                        v-model="plan.avaliacao_desempenho_outros"
                                        rows="2"
                                        placeholder="Descreva outras observações específicas sobre o rendimento do estudante..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- 1.5 sugestões para melhor aproveitamento -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                    Sugestões para melhor aproveitamento do discente
                                </label>
                                <textarea
                                    v-model="plan.sugestoes_aproveitamento"
                                    rows="2"
                                    placeholder="Sugestões e encaminhamentos pedagógicos para os próximos períodos..."
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- parte 2: instrumentos de avaliação e notas -->
                <!-- ============================================== -->
                <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#006633] text-white flex items-center justify-center text-[10px] font-bold">2</span>
                            Parte 2: Instrumentos de Avaliação Adotados
                        </h4>
                        <!-- aviso sobre envio de cópia para o NAI -->
                        <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-sm flex items-center gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                <strong>Nota ao Docente:</strong> Enviar cópia das atividades avaliativas realizadas para o e-mail do NAI.
                            </span>
                        </div>
                    </div>

                    <!-- checklist de instrumentos avaliativos e notas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="av in form.avaliacoes"
                            :key="av.tipo"
                            class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-3"
                        >
                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-800 dark:text-zinc-200 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    v-model="av.adotado"
                                    class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                                />
                                <span v-if="av.tipo === 'prova_escrita'">Prova escrita</span>
                                <span v-else-if="av.tipo === 'trabalhos'">Trabalhos individuais / em grupo / seminários</span>
                                <span v-else-if="av.tipo === 'prova_pratica'">Prova prática de laboratório / campo</span>
                                <span v-else-if="av.tipo === 'outros'">Outros instrumentos</span>
                                <span v-else-if="av.tipo === 'prova_oral'">Prova oral</span>
                            </label>

                            <!-- campos de notas se adotado -->
                            <div v-if="av.adotado" class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200 dark:border-zinc-700">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400">1º Valor / Nota</label>
                                    <div class="flex space-x-1 mt-1">
                                        <input
                                            type="text"
                                            v-model="av.valor_1"
                                            placeholder="Valor"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px]"
                                        />
                                        <input
                                            type="text"
                                            v-model="av.nota_1"
                                            placeholder="Nota"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px] font-semibold"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400">2º Valor / Nota</label>
                                    <div class="flex space-x-1 mt-1">
                                        <input
                                            type="text"
                                            v-model="av.valor_2"
                                            placeholder="Valor"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px]"
                                        />
                                        <input
                                            type="text"
                                            v-model="av.nota_2"
                                            placeholder="Nota"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px] font-semibold"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 dark:text-zinc-400">3º Valor / Nota</label>
                                    <div class="flex space-x-1 mt-1">
                                        <input
                                            type="text"
                                            v-model="av.valor_3"
                                            placeholder="Valor"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px]"
                                        />
                                        <input
                                            type="text"
                                            v-model="av.nota_3"
                                            placeholder="Nota"
                                            class="w-1/2 px-1.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-[11px] font-semibold"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. barra de ações inferior -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <a
                        :href="route('peis.show', pendencia.pei_id)"
                        target="_blank"
                        class="text-xs font-semibold text-[#006633] dark:text-emerald-400 hover:underline flex items-center gap-1 order-2 sm:order-1"
                    >
                        <span>Abrir página completa do PEI</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>

                    <div class="flex items-center space-x-3 w-full sm:w-auto justify-end order-1 sm:order-2">
                        <button
                            type="button"
                            @click="tentarFechar"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 cursor-pointer transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-bold rounded-sm shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer transition-colors"
                        >
                            <svg v-if="form.processing" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ form.processing ? 'Salvando PEI...' : 'Salvar e Concluir Pendência' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
