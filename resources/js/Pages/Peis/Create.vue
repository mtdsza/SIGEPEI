<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    alunosDisponiveis: {
        type: Array,
        default: () => [],
    },
    turmasDisponiveis: {
        type: Array,
        default: () => [],
    },
    usuariosDisponiveis: {
        type: Array,
        default: () => [],
    },
    hoje: {
        type: String,
        default: '',
    },
});

const form = useForm({
    aluno_id: '',
    turma_id: '',
    ano_letivo: new Date().getFullYear(),
    data_elaboracao: props.hoje || new Date().toISOString().split('T')[0],
    diagnostico: '',
    // dados da ficha do discente (6 campos pedagógicos)
    tempo_livre: '',
    pratica_esporte: '',
    caracteristicas_discente: '',
    organizacao_e_prazos: '',
    dificuldade_interacao_social: '',
    sugestoes_recursos_estrategias: '',
    // responsáveis pela elaboração do pei
    responsaveis: [
        { tipo: 'usuario', user_id: '', nome_externo: '' },
    ],
    // identificações de habilidades, dificuldades e fatores contextuais
    habilidades: [''],
    dificuldades: [''],
    dificultadores_internos: [''],
    facilitadores_internos: [''],
    dificultadores_externos: [''],
    facilitadores_externos: [''],
    // propostas e possibilidades de intervenção pedagógica
    intervencoes: [
        { demanda: '', encaminhamento_estrategia: '', responsavel: '' },
    ],
});

// discente selecionado e verificação de pei anterior
const alunoSelecionado = computed(() => {
    return props.alunosDisponiveis.find(a => a.id === Number(form.aluno_id));
});

const temPeiAnterior = computed(() => {
    return Boolean(alunoSelecionado.value?.ultimo_pei);
});

// ao selecionar o discente, preenche automaticamente turma, ano e diagnóstico inicial
watch(() => form.aluno_id, (novoId) => {
    if (!novoId) return;
    const aluno = props.alunosDisponiveis.find(a => a.id === Number(novoId));
    if (aluno) {
        if (aluno.turma_id) {
            form.turma_id = aluno.turma_id;
        }
        if (aluno.ano_letivo) {
            form.ano_letivo = aluno.ano_letivo;
        }
        if (aluno.deficiencias_texto) {
            form.diagnostico = aluno.deficiencias_texto;
        }
    }
});

// ações de importação e cópia de dados do pei anterior
const copiarFichaAlunoAnterior = () => {
    const ultimoPei = alunoSelecionado.value?.ultimo_pei;
    if (!ultimoPei) return;

    form.tempo_livre = ultimoPei.tempo_livre || '';
    form.pratica_esporte = ultimoPei.pratica_esporte || '';
    form.caracteristicas_discente = ultimoPei.caracteristicas_discente || '';
    form.organizacao_e_prazos = ultimoPei.organizacao_e_prazos || '';
    form.dificuldade_interacao_social = ultimoPei.dificuldade_interacao_social || '';
    form.sugestoes_recursos_estrategias = ultimoPei.sugestoes_recursos_estrategias || '';
};

const copiarFichaPeiAnterior = () => {
    const ultimoPei = alunoSelecionado.value?.ultimo_pei;
    if (!ultimoPei) return;

    if (ultimoPei.diagnostico) {
        form.diagnostico = ultimoPei.diagnostico;
    }

    if (ultimoPei.responsaveis?.length) {
        form.responsaveis = ultimoPei.responsaveis.map(r => ({
            tipo: r.tipo || 'usuario',
            user_id: r.user_id ? String(r.user_id) : '',
            nome_externo: r.nome_externo || '',
        }));
    }

    form.habilidades = ultimoPei.habilidades?.length ? [...ultimoPei.habilidades] : [''];
    form.dificuldades = ultimoPei.dificuldades?.length ? [...ultimoPei.dificuldades] : [''];
    form.dificultadores_internos = ultimoPei.dificultadores_internos?.length ? [...ultimoPei.dificultadores_internos] : [''];
    form.facilitadores_internos = ultimoPei.facilitadores_internos?.length ? [...ultimoPei.facilitadores_internos] : [''];
    form.dificultadores_externos = ultimoPei.dificultadores_externos?.length ? [...ultimoPei.dificultadores_externos] : [''];
    form.facilitadores_externos = ultimoPei.facilitadores_externos?.length ? [...ultimoPei.facilitadores_externos] : [''];

    if (ultimoPei.intervencoes?.length) {
        form.intervencoes = ultimoPei.intervencoes.map(i => ({
            demanda: i.demanda || '',
            encaminhamento_estrategia: i.encaminhamento_estrategia || '',
            responsavel: i.responsavel || '',
        }));
    }
};

const copiarTudoAnterior = () => {
    copiarFichaAlunoAnterior();
    copiarFichaPeiAnterior();
};

// funções auxiliares para adicionar e remover itens de texto dinâmicos
const addItem = (arrayName) => {
    form[arrayName].push('');
};

const removeItem = (arrayName, index) => {
    form[arrayName].splice(index, 1);
};

// gerenciamento da lista de responsáveis
const addResponsavel = () => {
    form.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
};

const removeResponsavel = (index) => {
    form.responsaveis.splice(index, 1);
};

// gerenciamento da lista de intervenções
const addIntervencao = () => {
    form.intervencoes.push({ demanda: '', encaminhamento_estrategia: '', responsavel: '' });
};

const removeIntervencao = (index) => {
    form.intervencoes.splice(index, 1);
};

const submit = () => {
    form.post(route('peis.store'));
};
</script>

<template>
    <Head title="Criar Novo PEI" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Criar Novo PEI
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Preencha os dados cadastrais, a Ficha do Aluno e a Ficha do PEI.
                    </p>
                </div>
                <Link
                    :href="route('peis.index')"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                >
                    Voltar para Lista
                </Link>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- banner de importação de dados de pei do ano anterior -->
            <div
                class="border rounded-sm p-4 transition-all"
                :class="temPeiAnterior 
                    ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800 shadow-xs' 
                    : 'bg-slate-50 dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 opacity-60'"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div
                            class="p-2 rounded-full shrink-0"
                            :class="temPeiAnterior 
                                ? 'bg-emerald-100 dark:bg-emerald-900 text-[#006633] dark:text-emerald-400' 
                                : 'bg-slate-200 dark:bg-zinc-700 text-slate-400 dark:text-zinc-500'"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-xs font-bold uppercase tracking-wider"
                                :class="temPeiAnterior ? 'text-emerald-950 dark:text-emerald-300' : 'text-slate-600 dark:text-zinc-400'"
                            >
                                Importar Dados de PEI Anterior
                            </h3>
                            <p
                                class="text-xs mt-0.5"
                                :class="temPeiAnterior ? 'text-emerald-800 dark:text-emerald-400' : 'text-slate-500 dark:text-zinc-500'"
                            >
                                <template v-if="temPeiAnterior">
                                    PEI anterior localizado: <strong>{{ alunoSelecionado.ultimo_pei.codigo }}</strong> (Ano: {{ alunoSelecionado.ultimo_pei.ano_letivo }}). Utilize as opções ao lado para auto-preencher.
                                </template>
                                <template v-else-if="form.aluno_id">
                                    Este estudante não possui nenhum PEI anterior registrado.
                                </template>
                                <template v-else>
                                    Selecione um estudante no campo abaixo para consultar e importar histórico anterior.
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- botões para cópia de seções específicas -->
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button
                            type="button"
                            @click="copiarFichaAlunoAnterior"
                            :disabled="!temPeiAnterior"
                            :class="[
                                'px-3 py-1.5 text-xs font-semibold rounded-sm border transition-colors flex items-center space-x-1.5',
                                temPeiAnterior
                                    ? 'bg-white hover:bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-emerald-300 dark:border-emerald-700 shadow-xs cursor-pointer'
                                    : 'bg-slate-100 text-slate-400 border-slate-200 dark:bg-zinc-800 dark:text-zinc-600 dark:border-zinc-700 cursor-not-allowed'
                            ]"
                        >
                            <span>Copiar Ficha do Aluno</span>
                        </button>

                        <button
                            type="button"
                            @click="copiarFichaPeiAnterior"
                            :disabled="!temPeiAnterior"
                            :class="[
                                'px-3 py-1.5 text-xs font-semibold rounded-sm border transition-colors flex items-center space-x-1.5',
                                temPeiAnterior
                                    ? 'bg-white hover:bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-emerald-300 dark:border-emerald-700 shadow-xs cursor-pointer'
                                    : 'bg-slate-100 text-slate-400 border-slate-200 dark:bg-zinc-800 dark:text-zinc-600 dark:border-zinc-700 cursor-not-allowed'
                            ]"
                        >
                            <span>Copiar Ficha do PEI</span>
                        </button>

                        <button
                            type="button"
                            @click="copiarTudoAnterior"
                            :disabled="!temPeiAnterior"
                            :class="[
                                'px-3.5 py-1.5 text-xs font-semibold rounded-sm shadow-xs transition-colors flex items-center space-x-1.5',
                                temPeiAnterior
                                    ? 'bg-[#006633] hover:bg-emerald-800 text-white cursor-pointer'
                                    : 'bg-slate-300 text-slate-500 dark:bg-zinc-700 dark:text-zinc-500 cursor-not-allowed'
                            ]"
                        >
                            <span>Copiar Tudo</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 1. dados de identificação geral do pei -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2">
                    1. Identificação Geral
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <!-- seleção do discente -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Estudante <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.aluno_id"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">Selecione o estudante...</option>
                            <option
                                v-for="aluno in alunosDisponiveis"
                                :key="aluno.id"
                                :value="aluno.id"
                            >
                                {{ aluno.nome }} (Matrícula: {{ aluno.matricula }})
                            </option>
                        </select>
                        <div v-if="form.errors.aluno_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.aluno_id }}
                        </div>
                    </div>

                    <!-- curso e turma do discente -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Curso / Turma
                        </label>
                        <select
                            v-model="form.turma_id"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">Selecione o curso...</option>
                            <option
                                v-for="turma in turmasDisponiveis"
                                :key="turma.id"
                                :value="turma.id"
                            >
                                {{ turma.nome_curso }} ({{ turma.ano_letivo }})
                            </option>
                        </select>
                        <div v-if="form.errors.turma_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.turma_id }}
                        </div>
                    </div>

                    <!-- ano letivo de vigência -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Ano Letivo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model="form.ano_letivo"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.ano_letivo" class="text-xs text-red-600 mt-1">
                            {{ form.errors.ano_letivo }}
                        </div>
                    </div>

                    <!-- data de elaboração do pei -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Data de Elaboração <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.data_elaboracao"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.data_elaboracao" class="text-xs text-red-600 mt-1">
                            {{ form.errors.data_elaboracao }}
                        </div>
                    </div>

                    <!-- diagnóstico e deficiências vinculadas -->
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Diagnóstico / Necessidades Específicas
                        </label>
                        <input
                            type="text"
                            v-model="form.diagnostico"
                            placeholder="Puxado automaticamente das deficiências do aluno ou preenchido livremente"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                    </div>
                </div>

                <!-- responsáveis pela elaboração da ficha inicial -->
                <div class="pt-4 border-t border-slate-200 dark:border-zinc-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase">
                                Responsáveis pela Elaboração
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-zinc-400">
                                Associe usuários do sistema ou digite nomes de profissionais externos (ex: psicólogos, terapeutas, família).
                            </span>
                        </div>
                        <button
                            type="button"
                            @click="addResponsavel"
                            class="text-xs font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                        >
                            + Adicionar Responsável
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <div
                            v-for="(resp, rIdx) in form.responsaveis"
                            :key="rIdx"
                            class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-2.5 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 text-xs"
                        >
                            <!-- tipo de responsável: usuário cadastrado ou externo -->
                            <div class="flex items-center space-x-3 shrink-0">
                                <label class="inline-flex items-center space-x-1 cursor-pointer">
                                    <input
                                        type="radio"
                                        :name="`tipo_resp_${rIdx}`"
                                        value="usuario"
                                        v-model="resp.tipo"
                                        class="text-[#006633] focus:ring-emerald-600 h-3.5 w-3.5"
                                    />
                                    <span>Usuário do Sistema</span>
                                </label>
                                <label class="inline-flex items-center space-x-1 cursor-pointer">
                                    <input
                                        type="radio"
                                        :name="`tipo_resp_${rIdx}`"
                                        value="externo"
                                        v-model="resp.tipo"
                                        class="text-[#006633] focus:ring-emerald-600 h-3.5 w-3.5"
                                    />
                                    <span>Pessoa Externa</span>
                                </label>
                            </div>

                            <!-- campo dinâmico de seleção ou preenchimento de nome externo -->
                            <div class="flex-1 w-full">
                                <select
                                    v-if="resp.tipo === 'usuario'"
                                    v-model="resp.user_id"
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                >
                                    <option value="">Selecione um usuário...</option>
                                    <option
                                        v-for="u in usuariosDisponiveis"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }} ({{ u.cargo || 'Geral' }} - {{ u.email }})
                                    </option>
                                </select>
                                <input
                                    v-else
                                    type="text"
                                    v-model="resp.nome_externo"
                                    placeholder="Nome completo e função da pessoa externa..."
                                    class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                            </div>

                            <button
                                v-if="form.responsaveis.length > 1"
                                type="button"
                                @click="removeResponsavel(rIdx)"
                                class="text-slate-400 hover:text-red-600 px-1 text-sm font-bold"
                                title="Remover"
                            >
                                &times;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. ficha do discente (6 campos pedagógicos e comportamentais) -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2">
                        2. Ficha do Aluno
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Informações comportamentais, hábitos, rotina e estratégias de aprendizagem individualizadas.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                    <!-- 1. atividades de tempo livre -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            O que gosta de fazer no tempo livre?
                        </label>
                        <textarea
                            v-model="form.tempo_livre"
                            rows="2"
                            placeholder="Descreva interesses, jogos, hobbies, preferências..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 2. prática de esportes e atividades físicas -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Gosta de praticar esporte?
                        </label>
                        <textarea
                            v-model="form.pratica_esporte"
                            rows="2"
                            placeholder="Modalidades esportivas, frequência, acompanhamentos..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 3. características pedagógicas e comportamentais -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Características do discente:
                        </label>
                        <textarea
                            v-model="form.caracteristicas_discente"
                            rows="3"
                            placeholder="Comportamento, foco, temperamento, perfil cognitivo, sensibilidades..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 4. organização e cumprimento de prazos -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?
                        </label>
                        <textarea
                            v-model="form.organizacao_e_prazos"
                            rows="2"
                            placeholder="Pontualidade, rotina de estudos, organização com prazos e materiais..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 5. interação social e comunicação -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Apresenta dificuldade na interação social?
                        </label>
                        <textarea
                            v-model="form.dificuldade_interacao_social"
                            rows="2"
                            placeholder="Relação com colegas, professores, dinâmicas em grupo ou em duplas..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 6. sugestões de recursos e estratégias -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Sugestões de recursos ou estratégias que possam ser utilizadas para auxiliar no processo de aprendizagem:
                        </label>
                        <textarea
                            v-model="form.sugestoes_recursos_estrategias"
                            rows="3"
                            placeholder="Adaptações de material, tempo adicional, ambiente silencioso, tecnologia assistiva, enunciados objetivos..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- 3. ficha do pei: identificações com relação ao estudante -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-6">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2">
                        3. Ficha do PEI - O que identificamos com relação ao(à) estudante?
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Insira as observações diagnósticas, habilidades, dificuldades e fatores de influência internos e externos.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <!-- habilidades identificadas -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Suas Habilidades
                            </span>
                            <button
                                type="button"
                                @click="addItem('habilidades')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.habilidades"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.habilidades[idx]"
                                    placeholder="Descreva uma habilidade..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.habilidades.length > 1"
                                    type="button"
                                    @click="removeItem('habilidades', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- dificuldades identificadas -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Suas Dificuldades
                            </span>
                            <button
                                type="button"
                                @click="addItem('dificuldades')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.dificuldades"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.dificuldades[idx]"
                                    placeholder="Descreva uma dificuldade..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.dificuldades.length > 1"
                                    type="button"
                                    @click="removeItem('dificuldades', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- dificultadores internos do campus -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Dificultadores internos do campus
                            </span>
                            <button
                                type="button"
                                @click="addItem('dificultadores_internos')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.dificultadores_internos"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.dificultadores_internos[idx]"
                                    placeholder="Item dificultador interno..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.dificultadores_internos.length > 1"
                                    type="button"
                                    @click="removeItem('dificultadores_internos', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- facilitadores internos do campus -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Facilitadores internos do campus
                            </span>
                            <button
                                type="button"
                                @click="addItem('facilitadores_internos')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.facilitadores_internos"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.facilitadores_internos[idx]"
                                    placeholder="Item facilitador interno..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.facilitadores_internos.length > 1"
                                    type="button"
                                    @click="removeItem('facilitadores_internos', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- dificultadores externos ao campus -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Dificultadores externos ao campus
                            </span>
                            <button
                                type="button"
                                @click="addItem('dificultadores_externos')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.dificultadores_externos"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.dificultadores_externos[idx]"
                                    placeholder="Item dificultador externo..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.dificultadores_externos.length > 1"
                                    type="button"
                                    @click="removeItem('dificultadores_externos', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- facilitadores externos ao campus -->
                    <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                Facilitadores externos ao campus
                            </span>
                            <button
                                type="button"
                                @click="addItem('facilitadores_externos')"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in form.facilitadores_externos"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    v-model="form.facilitadores_externos[idx]"
                                    placeholder="Item facilitador externo..."
                                    class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                />
                                <button
                                    v-if="form.facilitadores_externos.length > 1"
                                    type="button"
                                    @click="removeItem('facilitadores_externos', idx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. ficha do pei: possibilidades de intervenção pedagógica -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            4. Ficha do PEI - Possibilidades de Intervenção
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Mapeamento de demandas, encaminhamentos e seus respectivos responsáveis.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="addIntervencao"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-800 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 transition-colors"
                    >
                        + Adicionar Linha de Intervenção
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px]">
                            <tr>
                                <th class="px-3 py-2 w-1/3">Demandas</th>
                                <th class="px-3 py-2 w-1/3">Encaminhamento(s) / Solução(s) / Estratégia(s)</th>
                                <th class="px-3 py-2 w-1/4">Responsável(s)</th>
                                <th class="px-3 py-2 text-right w-12">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="(interv, iIdx) in form.intervencoes"
                                :key="iIdx"
                                class="align-top"
                            >
                                <td class="p-2">
                                    <textarea
                                        v-model="interv.demanda"
                                        rows="2"
                                        placeholder="Demanda identificada..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    ></textarea>
                                </td>
                                <td class="p-2">
                                    <textarea
                                        v-model="interv.encaminhamento_estrategia"
                                        rows="2"
                                        placeholder="Encaminhamentos e estratégias pedagógicas..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    ></textarea>
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        v-model="interv.responsavel"
                                        placeholder="Ex: NAI, Docentes, Família"
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    />
                                </td>
                                <td class="p-2 text-right">
                                    <button
                                        v-if="form.intervencoes.length > 1"
                                        type="button"
                                        @click="removeIntervencao(iIdx)"
                                        class="text-slate-400 hover:text-red-600 text-sm font-bold p-1"
                                        title="Remover linha"
                                    >
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- botões de ação do formulário -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                <Link
                    :href="route('peis.index')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                >
                    Cancelar
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                >
                    Salvar Ficha do Aluno, Ficha do PEI e Criar PEI
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
