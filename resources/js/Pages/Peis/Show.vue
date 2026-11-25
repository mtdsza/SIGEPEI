<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RelatorioPeiModal from '@/Components/RelatorioPeiModal.vue';
import ImprimirDisciplinaPromptModal from '@/Components/ImprimirDisciplinaPromptModal.vue';
import { confirmDiscard, useUnsavedChanges } from '@/composables/useUnsavedChanges';
import {
    ESTRATEGIAS_ENSINO_OPCOES,
    ESTRATEGIAS_AVALIACAO_OPCOES,
    TEMPO_OPCOES,
    AVALIACAO_DESEMPENHO_OPCOES,
} from '@/constants/peiProfessorOptions';

const props = defineProps({
    pei: {
        type: Object,
        required: true,
    },
    usuariosDisponiveis: {
        type: Array,
        default: () => [],
    },
});

// controle de aba principal ativa ('ficha_aluno', 'ficha_pei', 'nai', 'apoio_{id}', 'novo_apoio', 'disciplinas')
const activeTab = ref('ficha_aluno');

// controle de visibilidade do quadro de resumo superior
const showQuadroResumo = ref(true);

// controle do trimestre ativo para a aba do nai
const activeNaiTrimestre = ref(1);

// controle de trimestre ativo por equipe de apoio: { [apoioId]: 1|2|3 }
const activeApoioTrimestres = ref({});
props.pei.equipes_apoio?.forEach(a => {
    activeApoioTrimestres.value[a.id] = 1;
});

// modal de edição da ficha do discente
const showFichaAlunoModal = ref(false);
const fichaAlunoForm = useForm({
    tempo_livre: '',
    pratica_esporte: '',
    caracteristicas_discente: '',
    organizacao_e_prazos: '',
    dificuldade_interacao_social: '',
    sugestoes_recursos_estrategias: '',
});

const openFichaAlunoModal = () => {
    fichaAlunoForm.tempo_livre = props.pei.ficha_aluno?.tempo_livre || '';
    fichaAlunoForm.pratica_esporte = props.pei.ficha_aluno?.pratica_esporte || '';
    fichaAlunoForm.caracteristicas_discente = props.pei.ficha_aluno?.caracteristicas_discente || '';
    fichaAlunoForm.organizacao_e_prazos = props.pei.ficha_aluno?.organizacao_e_prazos || '';
    fichaAlunoForm.dificuldade_interacao_social = props.pei.ficha_aluno?.dificuldade_interacao_social || '';
    fichaAlunoForm.sugestoes_recursos_estrategias = props.pei.ficha_aluno?.sugestoes_recursos_estrategias || '';
    fichaAlunoForm.defaults();
    showFichaAlunoModal.value = true;
};

const closeFichaAlunoModal = () => {
    if (!confirmDiscard(fichaAlunoForm.isDirty)) return;
    showFichaAlunoModal.value = false;
};

const submitFichaAlunoForm = () => {
    fichaAlunoForm.post(route('peis.update-ficha-aluno', props.pei.id), {
        onSuccess: () => {
            showFichaAlunoModal.value = false;
        },
    });
};

// modal de edição da ficha do pei e intervenções
const showFichaPeiModal = ref(false);
const fichaPeiForm = useForm({
    diagnostico: '',
    responsaveis: [],
    habilidades: [],
    dificuldades: [],
    dificultadores_internos: [],
    facilitadores_internos: [],
    dificultadores_externos: [],
    facilitadores_externos: [],
    intervencoes: [],
});

const openFichaPeiModal = () => {
    fichaPeiForm.diagnostico = props.pei.diagnostico || '';
    fichaPeiForm.responsaveis = props.pei.responsaveis_ficha_inicial?.map(r => ({
        tipo: r.user_id ? 'usuario' : 'externo',
        user_id: r.user_id ? String(r.user_id) : '',
        nome_externo: r.nome_externo || '',
    })) || [];
    if (fichaPeiForm.responsaveis.length === 0) {
        fichaPeiForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
    }

    fichaPeiForm.habilidades = props.pei.identificacoes?.habilidades?.length ? [...props.pei.identificacoes.habilidades] : [''];
    fichaPeiForm.dificuldades = props.pei.identificacoes?.dificuldades?.length ? [...props.pei.identificacoes.dificuldades] : [''];
    fichaPeiForm.dificultadores_internos = props.pei.identificacoes?.dificultadores_internos?.length ? [...props.pei.identificacoes.dificultadores_internos] : [''];
    fichaPeiForm.facilitadores_internos = props.pei.identificacoes?.facilitadores_internos?.length ? [...props.pei.identificacoes.facilitadores_internos] : [''];
    fichaPeiForm.dificultadores_externos = props.pei.identificacoes?.dificultadores_externos?.length ? [...props.pei.identificacoes.dificultadores_externos] : [''];
    fichaPeiForm.facilitadores_externos = props.pei.identificacoes?.facilitadores_externos?.length ? [...props.pei.identificacoes.facilitadores_externos] : [''];

    fichaPeiForm.intervencoes = props.pei.intervencoes?.length
        ? props.pei.intervencoes.map(i => ({
            demanda: i.demanda,
            encaminhamento_estrategia: i.encaminhamento_estrategia,
            responsavel: i.responsavel,
        }))
        : [{ demanda: '', encaminhamento_estrategia: '', responsavel: '' }];

    fichaPeiForm.defaults();
    showFichaPeiModal.value = true;
};

const closeFichaPeiModal = () => {
    if (!confirmDiscard(fichaPeiForm.isDirty)) return;
    showFichaPeiModal.value = false;
};

const addFichaPeiResponsavel = () => {
    fichaPeiForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
};

const removeFichaPeiResponsavel = (idx) => {
    fichaPeiForm.responsaveis.splice(idx, 1);
};

const addFichaPeiItem = (key) => {
    fichaPeiForm[key].push('');
};

const removeFichaPeiItem = (key, idx) => {
    fichaPeiForm[key].splice(idx, 1);
};

const addFichaPeiIntervencao = () => {
    fichaPeiForm.intervencoes.push({ demanda: '', encaminhamento_estrategia: '', responsavel: '' });
};

const removeFichaPeiIntervencao = (idx) => {
    fichaPeiForm.intervencoes.splice(idx, 1);
};

const submitFichaPeiForm = () => {
    fichaPeiForm.post(route('peis.update-ficha-pei', props.pei.id), {
        onSuccess: () => {
            showFichaPeiModal.value = false;
        },
    });
};

// modal de acompanhamento e ações trimestrais do nai
const showNaiModal = ref(false);
const naiForm = useForm({
    trimestre: 1,
    responsaveis: [],
    atividades: [],
});

const openNaiModal = (trimestreNum) => {
    naiForm.trimestre = trimestreNum;
    const trimestreData = props.pei.nai_trimestres[trimestreNum] || { responsaveis: [], atividades: [] };
    
    let respList = trimestreData.responsaveis;
    if (!respList || respList.length === 0) {
        const nai1 = props.pei.nai_trimestres[1];
        if (nai1 && nai1.responsaveis && nai1.responsaveis.length > 0) {
            respList = nai1.responsaveis;
        } else if (props.pei.responsaveis_ficha_inicial && props.pei.responsaveis_ficha_inicial.length > 0) {
            respList = props.pei.responsaveis_ficha_inicial;
        }
    }

    naiForm.responsaveis = (respList || []).map(r => ({
        tipo: r.user_id ? 'usuario' : 'externo',
        user_id: r.user_id || '',
        nome_externo: r.nome_externo || '',
    }));
    if (naiForm.responsaveis.length === 0) {
        naiForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
    }

    naiForm.atividades = trimestreData.atividades.map(a => ({
        objetivos: a.objetivos,
        recursos_acoes: a.recursos_acoes,
        tempo: a.tempo,
        avaliacao_resultados: a.avaliacao_resultados || '',
        sugestoes_aproveitamento: a.sugestoes_aproveitamento || '',
    }));
    if (naiForm.atividades.length === 0) {
        naiForm.atividades.push({
            objetivos: '',
            recursos_acoes: '',
            tempo: '',
            avaliacao_resultados: '',
            sugestoes_aproveitamento: '',
        });
    }

    naiForm.defaults();
    showNaiModal.value = true;
};

const closeNaiModal = () => {
    if (!confirmDiscard(naiForm.isDirty)) return;
    showNaiModal.value = false;
};

const copiarResponsaveisNai1 = () => {
    const nai1 = props.pei.nai_trimestres[1];
    let source = nai1?.responsaveis?.length ? nai1.responsaveis : props.pei.responsaveis_ficha_inicial;
    if (source && source.length > 0) {
        naiForm.responsaveis = source.map(r => ({
            tipo: r.user_id ? 'usuario' : 'externo',
            user_id: r.user_id || '',
            nome_externo: r.nome_externo || '',
        }));
    }
};

const addNaiResponsavel = () => {
    naiForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
};

const removeNaiResponsavel = (index) => {
    naiForm.responsaveis.splice(index, 1);
};

const addNaiAtividade = () => {
    naiForm.atividades.push({
        objetivos: '',
        recursos_acoes: '',
        tempo: '',
        avaliacao_resultados: '',
        sugestoes_aproveitamento: '',
    });
};

const removeNaiAtividade = (index) => {
    naiForm.atividades.splice(index, 1);
};

const submitNaiForm = () => {
    naiForm.post(route('peis.update-nai', props.pei.id), {
        onSuccess: () => {
            showNaiModal.value = false;
        },
    });
};

// cadastro de nova equipe de apoio multidisciplinar
const novoApoioForm = useForm({
    nome: '',
});

const submitNovoApoio = () => {
    novoApoioForm.post(route('peis.store-apoio', props.pei.id), {
        onSuccess: () => {
            novoApoioForm.reset();
            // seleciona a nova equipe de apoio cadastrada
            if (props.pei.equipes_apoio?.length > 0) {
                const ultimo = props.pei.equipes_apoio[props.pei.equipes_apoio.length - 1];
                activeTab.value = `apoio_${ultimo.id}`;
            }
        },
    });
};

// modal de edição do nome da equipe de apoio
const showEditApoioModal = ref(false);
const editingApoio = ref(null);
const editApoioForm = useForm({
    nome: '',
});

const openEditApoio = (apoio) => {
    editingApoio.value = apoio;
    editApoioForm.nome = apoio.nome;
    editApoioForm.defaults();
    showEditApoioModal.value = true;
};

const closeEditApoioModal = () => {
    if (!confirmDiscard(editApoioForm.isDirty)) return;
    showEditApoioModal.value = false;
};

const submitEditApoio = () => {
    editApoioForm.put(route('peis.update-apoio', [props.pei.id, editingApoio.value.id]), {
        onSuccess: () => {
            showEditApoioModal.value = false;
        },
    });
};

const deleteApoio = (apoio) => {
    if (confirm(`Deseja realmente remover a equipe de apoio '${apoio.nome}' e toda a sua aba do PEI?`)) {
        router.delete(route('peis.destroy-apoio', [props.pei.id, apoio.id]), {
            onSuccess: () => {
                activeTab.value = 'ficha_aluno';
            },
        });
    }
};

// modal de ações e acompanhamentos trimestrais da equipe de apoio
const showApoioTrimestreModal = ref(false);
const currentApoio = ref(null);

const apoioTrimestreForm = useForm({
    trimestre: 1,
    responsaveis: [],
    atividades: [],
});

const openApoioTrimestreModal = (apoio, trimestreNum) => {
    currentApoio.value = apoio;
    apoioTrimestreForm.trimestre = trimestreNum;
    
    const triData = apoio.trimestres?.[trimestreNum] || { responsaveis: [], atividades: [] };

    apoioTrimestreForm.responsaveis = triData.responsaveis.map(r => ({
        tipo: r.user_id ? 'usuario' : 'externo',
        user_id: r.user_id || '',
        nome_externo: r.nome_externo || '',
    }));
    if (apoioTrimestreForm.responsaveis.length === 0) {
        apoioTrimestreForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
    }

    apoioTrimestreForm.atividades = triData.atividades.map(a => ({
        objetivos: a.objetivos,
        recursos_acoes: a.recursos_acoes,
        tempo: a.tempo,
        avaliacao_resultados: a.avaliacao_resultados || '',
        sugestoes_aproveitamento: a.sugestoes_aproveitamento || '',
    }));
    if (apoioTrimestreForm.atividades.length === 0) {
        apoioTrimestreForm.atividades.push({
            objetivos: '',
            recursos_acoes: '',
            tempo: '',
            avaliacao_resultados: '',
            sugestoes_aproveitamento: '',
        });
    }

    apoioTrimestreForm.defaults();
    showApoioTrimestreModal.value = true;
};

const closeApoioTrimestreModal = () => {
    if (!confirmDiscard(apoioTrimestreForm.isDirty)) return;
    showApoioTrimestreModal.value = false;
};

const addApoioTriResponsavel = () => {
    apoioTrimestreForm.responsaveis.push({ tipo: 'usuario', user_id: '', nome_externo: '' });
};

const removeApoioTriResponsavel = (index) => {
    apoioTrimestreForm.responsaveis.splice(index, 1);
};

const addApoioTriAtividade = () => {
    apoioTrimestreForm.atividades.push({
        objetivos: '',
        recursos_acoes: '',
        tempo: '',
        avaliacao_resultados: '',
        sugestoes_aproveitamento: '',
    });
};

const removeApoioTriAtividade = (index) => {
    apoioTrimestreForm.atividades.splice(index, 1);
};

const submitApoioTrimestreForm = () => {
    apoioTrimestreForm.post(route('peis.update-apoio-trimestre', [props.pei.id, currentApoio.value.id]), {
        onSuccess: () => {
            showApoioTrimestreModal.value = false;
        },
    });
};

// acompanhamento de disciplinas curriculares e trimestres
const selectedDisciplinaId = ref(props.pei.disciplinas?.[0]?.id || null);
const activeDisciplinaTrimestres = ref({});

const getActiveDisciplinaTrimestre = (disciplinaId) => {
    if (!activeDisciplinaTrimestres.value[disciplinaId]) {
        activeDisciplinaTrimestres.value[disciplinaId] = 1;
    }
    return activeDisciplinaTrimestres.value[disciplinaId];
};

const setActiveDisciplinaTrimestre = (disciplinaId, trimestreNum) => {
    activeDisciplinaTrimestres.value[disciplinaId] = trimestreNum;
};

const showDisciplinaTrimestreModal = ref(false);
const currentDisciplina = ref(null);

const disciplinaTrimestreForm = useForm({
    trimestre: 1,
    grau_autonomia: [],
    especifique_outros: '',
    planejamentos: [],
    avaliacoes: [],
});

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

const openDisciplinaTrimestreModal = (disciplina, trimestreNum) => {
    currentDisciplina.value = disciplina;
    disciplinaTrimestreForm.trimestre = trimestreNum;

    const triData = disciplina.trimestres?.[trimestreNum] || {
        planejamentos: [],
        grau_autonomia: [],
        especifique_outros: '',
        avaliacoes: {},
    };

    disciplinaTrimestreForm.grau_autonomia = Array.isArray(triData.grau_autonomia) ? [...triData.grau_autonomia] : [];
    disciplinaTrimestreForm.especifique_outros = triData.especifique_outros || '';

    // parte 1: planejamentos pedagógicos e adaptações
    disciplinaTrimestreForm.planejamentos = (triData.planejamentos || []).map(p => {
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
    if (disciplinaTrimestreForm.planejamentos.length === 0) {
        disciplinaTrimestreForm.planejamentos.push(defaultPlanejamento());
    }

    // parte 2: instrumentos e notas de avaliação
    const tipos = ['prova_escrita', 'trabalhos', 'prova_pratica', 'outros', 'prova_oral'];
    disciplinaTrimestreForm.avaliacoes = tipos.map(tipo => {
        const av = triData.avaliacoes?.[tipo] || {};
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

    disciplinaTrimestreForm.defaults();
    showDisciplinaTrimestreModal.value = true;
};

const closeDisciplinaTrimestreModal = () => {
    if (!confirmDiscard(disciplinaTrimestreForm.isDirty)) return;
    showDisciplinaTrimestreModal.value = false;
};

const addPlanejamentoRow = () => {
    disciplinaTrimestreForm.planejamentos.push(defaultPlanejamento());
};

const removePlanejamentoRow = (index) => {
    disciplinaTrimestreForm.planejamentos.splice(index, 1);
};

// modais de impressão de relatórios
const showRelatorioPeiModal = ref(false);
const showImprimirDisciplinaPrompt = ref(false);
const disciplinaRecemSalva = ref(null);

const submitDisciplinaTrimestreForm = () => {
    const discSalva = currentDisciplina.value
        ? {
            id: currentDisciplina.value.id,
            codigo: currentDisciplina.value.codigo,
            nome: currentDisciplina.value.nome,
            trimestre: disciplinaTrimestreForm.trimestre,
        }
        : null;

    disciplinaTrimestreForm.post(route('peis.update-disciplina-trimestre', [props.pei.id, currentDisciplina.value.id]), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showDisciplinaTrimestreModal.value = false;
            if (discSalva) {
                disciplinaRecemSalva.value = discSalva;
                showImprimirDisciplinaPrompt.value = true;
            }
        },
    });
};

useUnsavedChanges(() => (
    (showFichaAlunoModal.value && fichaAlunoForm.isDirty) ||
    (showFichaPeiModal.value && fichaPeiForm.isDirty) ||
    (showNaiModal.value && naiForm.isDirty) ||
    (showEditApoioModal.value && editApoioForm.isDirty) ||
    (showApoioTrimestreModal.value && apoioTrimestreForm.isDirty) ||
    (showDisciplinaTrimestreModal.value && disciplinaTrimestreForm.isDirty) ||
    novoApoioForm.isDirty
));
</script>

<template>
    <Head :title="`PEI - ${pei.codigo} (${pei.aluno.nome})`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-2.5">
                    <span class="font-mono text-sm font-bold text-[#006633] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2.5 py-1 rounded border border-emerald-200 dark:border-emerald-800">
                        {{ pei.codigo }}
                    </span>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ pei.aluno.nome }}
                    </h1>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        @click="showRelatorioPeiModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm shadow-xs transition-colors cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Imprimir Relatório</span>
                    </button>
                    <Link
                        :href="route('peis.index')"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                    >
                        Voltar para Lista
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- quadro resumo do pei (exibido no topo em todas as abas, colapsável) -->
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm overflow-hidden transition-all">
                <!-- barra de título e controle de expansão do resumo -->
                <div class="flex items-center justify-between px-5 py-2.5 bg-slate-50 dark:bg-zinc-900/70 border-b border-slate-200 dark:border-zinc-700">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                            Detalhes Gerais do PEI
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-zinc-400">
                            (Matrícula, Diagnóstico, Turma, Elaboração e Responsáveis)
                        </span>
                    </div>
                    <button
                        type="button"
                        @click="showQuadroResumo = !showQuadroResumo"
                        class="inline-flex items-center space-x-1 px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-600 text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer shadow-2xs"
                    >
                        <span>{{ showQuadroResumo ? 'Ocultar Detalhes' : 'Exibir Detalhes' }}</span>
                        <svg
                            class="w-3.5 h-3.5 transition-transform duration-200"
                            :class="{ 'rotate-180': !showQuadroResumo }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                </div>

                <!-- conteúdo detalhado do quadro de resumo -->
                <div v-show="showQuadroResumo" class="p-5 space-y-4 text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <!-- matrícula -->
                        <div>
                            <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase">Matrícula:</span>
                            <span class="text-slate-900 dark:text-white font-mono font-semibold text-sm">
                                {{ pei.aluno.matricula }}
                            </span>
                        </div>

                        <!-- curso e turma -->
                        <div class="sm:col-span-2 lg:col-span-2">
                            <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase">Curso / Turma:</span>
                            <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                {{ pei.turma.nome_curso }}
                            </span>
                        </div>

                        <!-- ano letivo -->
                        <div>
                            <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase">Ano Letivo:</span>
                            <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                {{ pei.ano_letivo }}
                            </span>
                        </div>

                        <!-- data de nascimento -->
                        <div>
                            <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase">Data de Nascimento:</span>
                            <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                {{ pei.aluno.data_nascimento }}
                            </span>
                        </div>

                        <!-- data de elaboração e idade na elaboração -->
                        <div>
                            <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase">Data de Elaboração:</span>
                            <span class="text-slate-900 dark:text-white font-semibold text-sm">
                                {{ pei.data_elaboracao }}
                            </span>
                            <span class="block text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                Idade na elaboração: <strong class="text-slate-800 dark:text-zinc-200">{{ pei.idade_na_elaboracao }}</strong>
                            </span>
                        </div>
                    </div>

                    <!-- diagnóstico e deficiências -->
                    <div class="pt-3 border-t border-slate-100 dark:border-zinc-700">
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase mb-1">
                            Diagnóstico / Deficiências:
                        </span>
                        <div class="p-2.5 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 font-semibold text-slate-900 dark:text-zinc-100">
                            {{ pei.diagnostico || 'Nenhum diagnóstico informado no PEI.' }}
                        </div>
                    </div>

                    <!-- responsáveis pela elaboração -->
                    <div class="pt-3 border-t border-slate-100 dark:border-zinc-700">
                        <span class="block text-slate-500 dark:text-zinc-400 font-medium text-[11px] uppercase mb-1.5">
                            Responsáveis pela Elaboração:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="resp in pei.responsaveis_ficha_inicial"
                                :key="resp.id"
                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 dark:bg-zinc-700 text-slate-800 dark:text-zinc-200 border border-slate-300 dark:border-zinc-600"
                            >
                                <svg class="w-3.5 h-3.5 mr-1 text-slate-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ resp.nome }}
                            </span>
                            <span v-if="pei.responsaveis_ficha_inicial.length === 0" class="text-slate-500 dark:text-zinc-400 italic">
                                Nenhum responsável registrado.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- navegação por abas do pei -->
            <div class="border-b border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-t-sm px-4 pt-3 flex space-x-1.5 overflow-x-auto text-xs font-semibold">
                <!-- 1. ficha do discente -->
                <button
                    type="button"
                    @click="activeTab = 'ficha_aluno'"
                    :class="[
                        'px-4 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer uppercase',
                        activeTab === 'ficha_aluno'
                            ? 'border-[#006633] dark:border-emerald-500 text-[#006633] dark:text-emerald-400 font-bold'
                            : 'border-transparent text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
                    ]"
                >
                    FICHA DO ALUNO
                </button>

                <!-- 2. ficha do pei -->
                <button
                    type="button"
                    @click="activeTab = 'ficha_pei'"
                    :class="[
                        'px-4 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer uppercase',
                        activeTab === 'ficha_pei'
                            ? 'border-[#006633] dark:border-emerald-500 text-[#006633] dark:text-emerald-400 font-bold'
                            : 'border-transparent text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
                    ]"
                >
                    FICHA DO PEI
                </button>

                <!-- 3. nai (trimestres) -->
                <button
                    type="button"
                    @click="activeTab = 'nai'"
                    :class="[
                        'px-4 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer uppercase',
                        activeTab === 'nai'
                            ? 'border-[#006633] dark:border-emerald-500 text-[#006633] dark:text-emerald-400 font-bold'
                            : 'border-transparent text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
                    ]"
                >
                    NAI (TRIMESTRES)
                </button>

                <!-- 4. abas das equipes de apoio cadastradas -->
                <button
                    v-for="apoio in pei.equipes_apoio"
                    :key="apoio.id"
                    type="button"
                    @click="activeTab = `apoio_${apoio.id}`"
                    :class="[
                        'px-4 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer uppercase flex items-center space-x-1.5',
                        activeTab === `apoio_${apoio.id}`
                            ? 'border-[#006633] dark:border-emerald-500 text-[#006633] dark:text-emerald-400 font-bold'
                            : 'border-transparent text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
                    ]"
                >
                    <span>{{ apoio.nome }}</span>
                </button>

                <!-- 5. aba para adicionar nova equipe de apoio -->
                <button
                    v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                    type="button"
                    @click="activeTab = 'novo_apoio'"
                    :class="[
                        'px-3.5 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer flex items-center space-x-1 font-bold',
                        activeTab === 'novo_apoio'
                            ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/30'
                            : 'border-transparent text-[#006633] dark:text-emerald-400 hover:bg-slate-50 dark:hover:bg-zinc-700/50'
                    ]"
                >
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ NOVA EQUIPE DE APOIO</span>
                </button>

                <!-- 6. disciplinas curriculares -->
                <button
                    type="button"
                    @click="activeTab = 'disciplinas'"
                    :class="[
                        'px-4 py-2.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer uppercase',
                        activeTab === 'disciplinas'
                            ? 'border-[#006633] dark:border-emerald-500 text-[#006633] dark:text-emerald-400 font-bold'
                            : 'border-transparent text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
                    ]"
                >
                    DISCIPLINAS
                </button>
            </div>

            <!-- ============================================== -->
            <!-- 1. aba: ficha do discente -->
            <!-- ============================================== -->
            <div v-if="activeTab === 'ficha_aluno'" class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200 dark:border-zinc-700 gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Ficha do Aluno
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Perfil comportamental, rotina e estratégias pedagógicas do estudante neste PEI.
                            </p>
                        </div>
                        <button
                            v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                            type="button"
                            @click="openFichaAlunoModal"
                            class="inline-flex items-center px-3.5 py-1.5 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Ficha do Aluno
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                        <!-- 1. atividades de tempo livre -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                O que gosta de fazer no tempo livre?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ pei.ficha_aluno?.tempo_livre || 'Não informado neste PEI.' }}
                            </p>
                        </div>

                        <!-- 2. prática de esportes e atividades físicas -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Gosta de praticar esporte?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ pei.ficha_aluno?.pratica_esporte || 'Não informado neste PEI.' }}
                            </p>
                        </div>

                        <!-- 3. características pedagógicas e comportamentais -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5 md:col-span-2">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Características do discente:
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ pei.ficha_aluno?.caracteristicas_discente || 'Não informado neste PEI.' }}
                            </p>
                        </div>

                        <!-- 4. organização e cumprimento de prazos -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ pei.ficha_aluno?.organizacao_e_prazos || 'Não informado neste PEI.' }}
                            </p>
                        </div>

                        <!-- 5. interação social e comunicação -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm space-y-1.5">
                            <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] tracking-wide">
                                Apresenta dificuldade na interação social?
                            </div>
                            <p class="text-slate-700 dark:text-zinc-300 leading-relaxed">
                                {{ pei.ficha_aluno?.dificuldade_interacao_social || 'Não informado neste PEI.' }}
                            </p>
                        </div>

                        <!-- 6. sugestões de recursos e estratégias -->
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/60 rounded-sm space-y-1.5 md:col-span-2">
                            <div class="font-bold text-emerald-900 dark:text-emerald-300 uppercase text-[11px] tracking-wide">
                                Sugestões de recursos ou estratégias que possam ser utilizadas para auxiliar no processo de aprendizagem:
                            </div>
                            <p class="text-slate-800 dark:text-zinc-200 leading-relaxed font-medium">
                                {{ pei.ficha_aluno?.sugestoes_recursos_estrategias || 'Não informado neste PEI.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 2. aba: ficha do pei -->
            <!-- ============================================== -->
            <div v-if="activeTab === 'ficha_pei'" class="space-y-6">
                <!-- cabeçalho da aba ficha do pei -->
                <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-200 dark:border-zinc-700 gap-2 mb-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Ficha do PEI
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                                Identificações diagnósticas, fatores de influência pedagógica e possibilidades de intervenção.
                            </p>
                        </div>
                        <button
                            v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                            type="button"
                            @click="openFichaPeiModal"
                            class="inline-flex items-center px-3.5 py-1.5 bg-[#006633] dark:bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Ficha do PEI
                        </button>
                    </div>

                    <!-- identificações com relação ao estudante -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">
                            O que identificamos com relação ao(à) estudante?
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Suas Habilidades:</div>
                                <ul v-if="pei.identificacoes.habilidades.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(h, idx) in pei.identificacoes.habilidades" :key="idx">{{ h }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Suas Dificuldades:</div>
                                <ul v-if="pei.identificacoes.dificuldades.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(d, idx) in pei.identificacoes.dificuldades" :key="idx">{{ d }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Dificultadores internos do campus:</div>
                                <ul v-if="pei.identificacoes.dificultadores_internos.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(di, idx) in pei.identificacoes.dificultadores_internos" :key="idx">{{ di }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Facilitadores internos do campus:</div>
                                <ul v-if="pei.identificacoes.facilitadores_internos.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(fi, idx) in pei.identificacoes.facilitadores_internos" :key="idx">{{ fi }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Dificultadores externos ao campus:</div>
                                <ul v-if="pei.identificacoes.dificultadores_externos.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(de, idx) in pei.identificacoes.dificultadores_externos" :key="idx">{{ de }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                                <div class="font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide mb-2">Facilitadores externos ao campus:</div>
                                <ul v-if="pei.identificacoes.facilitadores_externos.length > 0" class="list-disc list-inside space-y-1 text-slate-700 dark:text-zinc-300">
                                    <li v-for="(fe, idx) in pei.identificacoes.facilitadores_externos" :key="idx">{{ fe }}</li>
                                </ul>
                                <p v-else class="text-slate-500 dark:text-zinc-400 italic">Nenhum item registrado.</p>
                            </div>
                        </div>
                    </div>

                    <!-- possibilidades de intervenção pedagógica -->
                    <div class="pt-6 border-t border-slate-200 dark:border-zinc-700 space-y-4 mt-6">
                        <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">
                            Quais as possibilidades de intervenção?
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                                <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[11px]">
                                    <tr>
                                        <th class="px-4 py-3 w-1/3">Demandas</th>
                                        <th class="px-4 py-3 w-1/3">Encaminhamento(s) / Solução(s) / Estratégia(s)</th>
                                        <th class="px-4 py-3 w-1/3">Responsável(s)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    <tr
                                        v-for="interv in pei.intervencoes"
                                        :key="interv.id"
                                        class="hover:bg-slate-50 dark:hover:bg-zinc-700/40"
                                    >
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ interv.demanda }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-zinc-200">{{ interv.encaminhamento_estrategia }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-zinc-400 font-medium">{{ interv.responsavel }}</td>
                                    </tr>
                                    <tr v-if="pei.intervencoes.length === 0">
                                        <td colspan="3" class="px-4 py-6 text-center text-slate-500 dark:text-zinc-400">
                                            Nenhuma intervenção registrada.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 3. aba: acompanhamento trimestral do nai -->
            <!-- ============================================== -->
            <div v-if="activeTab === 'nai'" class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 mb-4">
                        <div class="flex space-x-2">
                            <button
                                v-for="t in [1, 2, 3]"
                                :key="t"
                                type="button"
                                @click="activeNaiTrimestre = t"
                                :class="[
                                    'px-4 py-1.5 text-xs font-bold rounded-sm border transition-colors cursor-pointer',
                                    activeNaiTrimestre === t
                                        ? 'bg-[#006633] text-white border-[#006633]'
                                        : 'bg-slate-50 dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border-slate-300 dark:border-zinc-700 hover:bg-slate-100'
                                ]"
                            >
                                {{ t }}º Trimestre
                            </button>
                        </div>

                        <button
                            v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'"
                            type="button"
                            @click="openNaiModal(activeNaiTrimestre)"
                            class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors"
                        >
                            Editar / Registrar Ações ({{ activeNaiTrimestre }}º Trimestre)
                        </button>
                    </div>

                    <div class="space-y-5">
                        <div class="p-3 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 text-xs">
                            <span class="block font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] mb-1.5">
                                Responsáveis pelo atendimento do NAI ({{ activeNaiTrimestre }}º Trimestre):
                            </span>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="resp in pei.nai_trimestres[activeNaiTrimestre]?.responsaveis || []"
                                    :key="resp.id"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-300 dark:border-zinc-600"
                                >
                                    {{ resp.nome }}
                                </span>
                                <span v-if="!pei.nai_trimestres[activeNaiTrimestre]?.responsaveis?.length" class="text-slate-500 dark:text-zinc-400 italic">
                                    Nenhum responsável registrado para este trimestre.
                                </span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">
                                Ações / Atividades desenvolvidas pelo NAI em atendimento à(s) demanda(s) do(a) estudante
                            </h3>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                                    <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[10px]">
                                        <tr>
                                            <th class="px-3 py-2.5 w-1/5">Objetivos</th>
                                            <th class="px-3 py-2.5 w-1/4">Recursos Disponibilizados / Ações Executadas</th>
                                            <th class="px-3 py-2.5 w-24">Tempo</th>
                                            <th class="px-3 py-2.5 w-1/5">Avaliação de Desempenho / Resultados</th>
                                            <th class="px-3 py-2.5 w-1/5">Sugestões para Melhor Aproveitamento</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                        <tr
                                            v-for="atv in pei.nai_trimestres[activeNaiTrimestre]?.atividades || []"
                                            :key="atv.id"
                                            class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 align-top"
                                        >
                                            <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white">{{ atv.objetivos }}</td>
                                            <td class="px-3 py-2.5">{{ atv.recursos_acoes }}</td>
                                            <td class="px-3 py-2.5 font-mono text-[11px]">{{ atv.tempo }}</td>
                                            <td class="px-3 py-2.5 text-slate-600 dark:text-zinc-300">{{ atv.avaliacao_resultados || '-' }}</td>
                                            <td class="px-3 py-2.5 text-slate-600 dark:text-zinc-300">{{ atv.sugestoes_aproveitamento || '-' }}</td>
                                        </tr>
                                        <tr v-if="!pei.nai_trimestres[activeNaiTrimestre]?.atividades?.length">
                                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400">
                                                Nenhuma ação/atividade registrada pelo NAI para o {{ activeNaiTrimestre }}º Trimestre até o momento.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 4. abas dinâmicas: equipes de apoio multidisciplinar -->
            <!-- ============================================== -->
            <template v-for="apoio in pei.equipes_apoio" :key="apoio.id">
                <div v-if="activeTab === `apoio_${apoio.id}`" class="space-y-6">
                    <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-5 space-y-5">
                        <!-- cabeçalho e ações da equipe de apoio -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-4 gap-3">
                            <div>
                                <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider block">
                                    Acompanhamento Especializado
                                </span>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                    {{ apoio.nome }}
                                </h2>
                            </div>

                            <div v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'" class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    @click="openEditApoio(apoio)"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600 transition-colors"
                                >
                                    Renomear Equipe
                                </button>
                                <button
                                    type="button"
                                    @click="deleteApoio(apoio)"
                                    class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 text-red-800 dark:text-red-300 text-xs font-semibold rounded-sm border border-red-300 dark:border-red-800 transition-colors"
                                >
                                    Remover Aba
                                </button>
                            </div>
                        </div>

                        <!-- sub-abas de trimestres da equipe de apoio -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3 gap-3">
                            <div class="flex space-x-2">
                                <button
                                    v-for="t in [1, 2, 3]"
                                    :key="t"
                                    type="button"
                                    @click="activeApoioTrimestres[apoio.id] = t"
                                    :class="[
                                        'px-4 py-1.5 text-xs font-bold rounded-sm border transition-colors cursor-pointer',
                                        (activeApoioTrimestres[apoio.id] || 1) === t
                                            ? 'bg-[#006633] text-white border-[#006633]'
                                            : 'bg-slate-50 dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border-slate-300 dark:border-zinc-700 hover:bg-slate-100'
                                    ]"
                                >
                                    {{ t }}º Trimestre
                                </button>
                            </div>

                            <button
                                v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI' || apoio.trimestres?.[activeApoioTrimestres[apoio.id] || 1]?.responsaveis?.some(r => r.user_id === $page.props.auth?.user?.id)"
                                type="button"
                                @click="openApoioTrimestreModal(apoio, activeApoioTrimestres[apoio.id] || 1)"
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors"
                            >
                                Registrar / Editar Ações ({{ activeApoioTrimestres[apoio.id] || 1 }}º Trimestre)
                            </button>
                        </div>

                        <!-- conteúdo das ações trimestrais da equipe de apoio -->
                        <div class="space-y-4">
                            <!-- responsáveis pelas ações do trimestre -->
                            <div class="p-3 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 text-xs">
                                <span class="block font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px] mb-1.5">
                                    Responsáveis pelo atendimento de {{ apoio.nome }} ({{ activeApoioTrimestres[apoio.id] || 1 }}º Trimestre):
                                </span>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="resp in apoio.trimestres?.[activeApoioTrimestres[apoio.id] || 1]?.responsaveis || []"
                                        :key="resp.id"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-300 dark:border-zinc-600"
                                    >
                                        {{ resp.nome }}
                                    </span>
                                    <span v-if="!apoio.trimestres?.[activeApoioTrimestres[apoio.id] || 1]?.responsaveis?.length" class="text-slate-500 dark:text-zinc-400 italic">
                                        Nenhum responsável registrado para este trimestre.
                                    </span>
                                </div>
                            </div>

                            <!-- tabela de ações pedagógicas desenvolvidas -->
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">
                                    Quais as ações/atividades desenvolvidas pela equipe de apoio em atendimento à(s) demanda(s) do(a) estudante?
                                </h3>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                                        <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[10px]">
                                            <tr>
                                                <th class="px-3 py-2.5 w-1/5">Objetivos</th>
                                                <th class="px-3 py-2.5 w-1/4">Recursos Disponibilizados / Ações Executadas</th>
                                                <th class="px-3 py-2.5 w-24">Tempo</th>
                                                <th class="px-3 py-2.5 w-1/5">Avaliação de Desempenho / Resultados</th>
                                                <th class="px-3 py-2.5 w-1/5">Sugestões para Melhor Aproveitamento</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                            <tr
                                                v-for="atv in apoio.trimestres?.[activeApoioTrimestres[apoio.id] || 1]?.atividades || []"
                                                :key="atv.id"
                                                class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 align-top"
                                            >
                                                <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white">{{ atv.objetivos }}</td>
                                                <td class="px-3 py-2.5">{{ atv.recursos_acoes }}</td>
                                                <td class="px-3 py-2.5 font-mono text-[11px]">{{ atv.tempo }}</td>
                                                <td class="px-3 py-2.5 text-slate-600 dark:text-zinc-300">{{ atv.avaliacao_resultados || '-' }}</td>
                                                <td class="px-3 py-2.5 text-slate-600 dark:text-zinc-300">{{ atv.sugestoes_aproveitamento || '-' }}</td>
                                            </tr>
                                            <tr v-if="!apoio.trimestres?.[activeApoioTrimestres[apoio.id] || 1]?.atividades?.length">
                                                <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400">
                                                    Nenhuma ação/atividade registrada por {{ apoio.nome }} para o {{ activeApoioTrimestres[apoio.id] || 1 }}º Trimestre até o momento.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ============================================== -->
            <!-- 5. aba: cadastrar nova equipe de apoio -->
            <!-- ============================================== -->
            <div v-if="activeTab === 'novo_apoio'" class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6 space-y-6">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 pb-2">
                        Adicionar Nova Equipe de Apoio ao PEI
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Cada equipe de apoio adicionada ganha sua própria aba dedicada no PEI com acompanhamento por trimestres para todo o ano letivo.
                    </p>
                </div>

                <form @submit.prevent="submitNovoApoio" class="space-y-4 max-w-xl text-xs">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome da Equipe de Apoio / Tipo de Apoio <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="novoApoioForm.nome"
                            required
                            placeholder="Ex: Apoio Pedagógico, Apoio Psicológico, Monitoria de Programação, etc."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="novoApoioForm.errors.nome" class="text-xs text-red-600 mt-1">
                            {{ novoApoioForm.errors.nome }}
                        </div>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="novoApoioForm.processing"
                            class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            + Criar Aba da Equipe de Apoio
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- 6. aba: acompanhamento das disciplinas curriculares -->
            <!-- ============================================== -->
            <div v-if="activeTab === 'disciplinas'" class="space-y-4">
                <div v-if="!pei.disciplinas || pei.disciplinas.length === 0" class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-12 text-center space-y-3">
                    <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                        Nenhuma Disciplina Cadastrada
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-md mx-auto">
                        A turma vinculada a este PEI ({{ pei.turma.nome_curso }}) ainda não possui disciplinas cadastradas no sistema.
                    </p>
                </div>

                <!-- layout em colunas divididas para disciplinas -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                    <!-- coluna lateral de listagem de disciplinas -->
                    <div class="lg:col-span-4 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-sm p-4 space-y-3">
                        <div class="border-b border-slate-200 dark:border-zinc-700 pb-2.5 flex items-center justify-between">
                            <div>
                                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Disciplinas da Turma
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    {{ pei.turma.nome_curso }}
                                </p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 rounded">
                                {{ pei.disciplinas.length }} disc.
                            </span>
                        </div>

                        <div class="space-y-2">
                            <button
                                v-for="disc in pei.disciplinas"
                                :key="disc.id"
                                type="button"
                                @click="selectedDisciplinaId = disc.id"
                                :class="[
                                    'w-full text-left p-3 rounded-sm border transition-all text-xs flex flex-col gap-1.5',
                                    (selectedDisciplinaId || pei.disciplinas[0].id) === disc.id
                                        ? 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-500 dark:border-emerald-600 ring-1 ring-emerald-500/20'
                                        : 'bg-white dark:bg-zinc-900/60 border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600'
                                ]"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span class="font-mono text-[10px] font-bold text-slate-500 dark:text-zinc-400 bg-slate-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">
                                            {{ disc.codigo }}
                                        </span>
                                        {{ disc.nome }}
                                    </span>
                                    <span
                                        v-if="disc.pode_editar"
                                        class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                    >
                                        Edição
                                    </span>
                                    <span
                                        v-else
                                        class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700"
                                    >
                                        Leitura
                                    </span>
                                </div>

                                <div class="text-[11px] text-slate-600 dark:text-zinc-400 flex items-start gap-1">
                                    <span class="font-semibold text-slate-500 dark:text-zinc-500 shrink-0">Docentes:</span>
                                    <span class="truncate">
                                        <template v-if="disc.professores && disc.professores.length > 0">
                                            {{ disc.professores.map(p => p.name + (p.is_substituto ? ' (Subst.)' : '')).join(', ') }}
                                        </template>
                                        <template v-else>
                                            <span class="italic text-slate-400">Nenhum professor atribuído</span>
                                        </template>
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- painel principal com planejamento e avaliações da disciplina -->
                    <div
                        v-if="pei.disciplinas.find(d => d.id === (selectedDisciplinaId || pei.disciplinas[0].id))"
                        class="lg:col-span-8 space-y-5"
                    >
                        <div
                            v-for="disc in [pei.disciplinas.find(d => d.id === (selectedDisciplinaId || pei.disciplinas[0].id))]"
                            :key="disc.id"
                            class="space-y-5"
                        >
                            <!-- cabeçalho da disciplina e docentes responsáveis -->
                            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-sm p-5 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 dark:border-zinc-700 pb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-[#006633] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                                {{ disc.codigo }}
                                            </span>
                                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                                {{ disc.nome }}
                                            </h2>
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-x-2">
                                            <span class="font-semibold text-slate-700 dark:text-zinc-300">Docente(s) Responsável(is):</span>
                                            <span v-if="disc.professores && disc.professores.length > 0">
                                                {{ disc.professores.map(p => p.name + (p.is_substituto ? ' [Substituto]' : ' [Titular]')).join(', ') }}
                                            </span>
                                            <span v-else class="italic text-slate-400">Nenhum professor vinculado nesta turma.</span>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <span
                                            v-if="disc.pode_editar"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edição Habilitada
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-zinc-400 border border-slate-200 dark:border-zinc-600"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Modo Visualização
                                        </span>
                                    </div>
                                </div>

                                <!-- sub-abas de trimestres da disciplina -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                                    <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-900 rounded-sm border border-slate-200 dark:border-zinc-700">
                                        <button
                                            v-for="t in [1, 2, 3]"
                                            :key="t"
                                            type="button"
                                            @click="setActiveDisciplinaTrimestre(disc.id, t)"
                                            :class="[
                                                'px-4 py-1.5 text-xs font-semibold rounded-sm transition-colors',
                                                getActiveDisciplinaTrimestre(disc.id) === t
                                                    ? 'bg-white dark:bg-zinc-800 text-[#006633] dark:text-emerald-400 shadow-xs border border-slate-200 dark:border-zinc-700'
                                                    : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'
                                            ]"
                                        >
                                            {{ t }}º Trimestre
                                        </button>
                                    </div>

                                    <button
                                        v-if="disc.pode_editar"
                                        type="button"
                                        @click="openDisciplinaTrimestreModal(disc, getActiveDisciplinaTrimestre(disc.id))"
                                        class="inline-flex items-center px-3.5 py-1.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm transition-colors shadow-sm"
                                    >
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        Registrar / Editar Planejamento e Avaliações ({{ getActiveDisciplinaTrimestre(disc.id) }}º Trimestre)
                                    </button>
                                </div>
                            </div>

                            <!-- conteúdo do trimestre ativo da disciplina -->
                            <div
                                v-for="t in [getActiveDisciplinaTrimestre(disc.id)]"
                                :key="t"
                                class="space-y-5"
                            >
                                <!-- parte 1: planejamento pedagógico e adaptação curricular -->
                                <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/50 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                Parte 1: Planejamento Pedagógico e Adaptação Curricular
                                            </h3>
                                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                                Objetivos, conteúdos, metodologias, prazos e avaliação de resultados da disciplina no {{ t }}º Trimestre.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs text-slate-700 dark:text-zinc-200 divide-y divide-slate-200 dark:divide-zinc-700">
                                            <thead class="bg-slate-100 dark:bg-zinc-900/60 font-semibold text-slate-600 dark:text-zinc-300 uppercase text-[10px] tracking-wider">
                                                <tr>
                                                    <th class="px-3 py-2.5 w-1/6">Objetivos</th>
                                                    <th class="px-3 py-2.5 w-1/6">Conteúdos</th>
                                                    <th class="px-3 py-2.5 w-[28%]">Metodologias / Estratégias / Recursos</th>
                                                    <th class="px-3 py-2.5 w-[10%]">Tempo</th>
                                                    <th class="px-3 py-2.5 w-[20%]">Avaliação de Desempenho / Resultados</th>
                                                    <th class="px-3 py-2.5 w-[10%]">Sugestões</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
                                                <tr
                                                    v-for="(plan, pIdx) in (disc.trimestres[t]?.planejamentos || [])"
                                                    :key="pIdx"
                                                    class="hover:bg-slate-50 dark:hover:bg-zinc-700/40 align-top"
                                                >
                                                    <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white whitespace-pre-line">{{ plan.objetivos }}</td>
                                                    <td class="px-3 py-2.5 whitespace-pre-line">{{ plan.conteudos }}</td>
                                                    <td class="px-3 py-2.5">
                                                        <div v-if="(plan.estrategias_ensino && plan.estrategias_ensino.length > 0) || plan.estrategias_ensino_outros || (plan.estrategias_avaliacao && plan.estrategias_avaliacao.length > 0) || plan.estrategias_avaliacao_outros" class="space-y-2">
                                                            <div v-if="(plan.estrategias_ensino && plan.estrategias_ensino.length > 0) || plan.estrategias_ensino_outros">
                                                                <span class="font-bold text-[10px] uppercase text-emerald-800 dark:text-emerald-400 tracking-wider block">1. Estratégias de ensino:</span>
                                                                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700 dark:text-zinc-300 mt-0.5">
                                                                    <li v-for="e in plan.estrategias_ensino" :key="e">{{ e }}</li>
                                                                    <li v-if="plan.estrategias_ensino_outros" class="italic">Outras: {{ plan.estrategias_ensino_outros }}</li>
                                                                </ul>
                                                            </div>
                                                            <div v-if="(plan.estrategias_avaliacao && plan.estrategias_avaliacao.length > 0) || plan.estrategias_avaliacao_outros">
                                                                <span class="font-bold text-[10px] uppercase text-emerald-800 dark:text-emerald-400 tracking-wider block">2. Estratégias e condições de avaliação:</span>
                                                                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700 dark:text-zinc-300 mt-0.5">
                                                                    <li v-for="a in plan.estrategias_avaliacao" :key="a">{{ a }}</li>
                                                                    <li v-if="plan.estrategias_avaliacao_outros" class="italic">Outras: {{ plan.estrategias_avaliacao_outros }}</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div v-else class="whitespace-pre-line">{{ plan.metodologias_recursos || '-' }}</div>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-[11px] text-slate-700 dark:text-zinc-300">
                                                        <span v-if="plan.tempo_opcao">
                                                            {{ plan.tempo_opcao === 'Outro' ? (plan.tempo_outro || 'Outro') : plan.tempo_opcao }}
                                                        </span>
                                                        <span v-else>{{ plan.tempo || '-' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <div v-if="(plan.avaliacao_desempenho && plan.avaliacao_desempenho.length > 0) || plan.avaliacao_desempenho_outros" class="space-y-1">
                                                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700 dark:text-zinc-300">
                                                                <li v-for="d in plan.avaliacao_desempenho" :key="d">{{ d }}</li>
                                                            </ul>
                                                            <p v-if="plan.avaliacao_desempenho_outros" class="text-[11px] text-slate-600 dark:text-zinc-400 italic pt-1 border-t border-slate-100 dark:border-zinc-800">
                                                                <strong>Outras:</strong> {{ plan.avaliacao_desempenho_outros }}
                                                            </p>
                                                        </div>
                                                        <div v-else class="whitespace-pre-line">{{ plan.avaliacao_resultados || '-' }}</div>
                                                    </td>
                                                    <td class="px-3 py-2.5 whitespace-pre-line text-slate-600 dark:text-zinc-400">{{ plan.sugestoes_aproveitamento || '-' }}</td>
                                                </tr>
                                                <tr v-if="!disc.trimestres[t]?.planejamentos || disc.trimestres[t].planejamentos.length === 0">
                                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400">
                                                        Nenhum planejamento registrado para esta disciplina no {{ t }}º Trimestre.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- parte 2: instrumentos de avaliação adotados -->
                                <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-sm p-5 space-y-4">
                                    <div class="border-b border-slate-200 dark:border-zinc-700 pb-3">
                                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                            Parte 2: Instrumentos de Avaliação Adotados no Processo de Ensino e Aprendizagem
                                        </h3>
                                        <!-- aviso sobre preenchimento das adaptações de avaliação -->
                                        <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-sm flex items-center gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>
                                                <strong>Nota ao Docente:</strong> Enviar cópia das atividades avaliativas realizadas para o e-mail do NAI.
                                            </span>
                                        </div>
                                    </div>

                                    <!-- grid de instrumentos de avaliação com notas e valores -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- avaliação: prova escrita -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold flex items-center justify-between border-b border-slate-200 dark:border-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', disc.trimestres[t]?.avaliacoes?.prova_escrita?.adotado ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.adotado ? '✓' : '' }}
                                                    </span>
                                                    Prova escrita
                                                </span>
                                                <span v-if="disc.trimestres[t]?.avaliacoes?.prova_escrita?.adotado" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase">Adotado</span>
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 space-y-1.5 divide-y divide-slate-100 dark:divide-zinc-700/60 font-mono text-[11px]">
                                                <div class="flex justify-between items-center pt-1 first:pt-0">
                                                    <span class="text-slate-500">1º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.valor_1 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.nota_1 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">2º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.valor_2 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.nota_2 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">3º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.valor_3 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_escrita?.nota_3 || '-' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- avaliação: trabalhos individuais ou em grupo -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold flex items-center justify-between border-b border-slate-200 dark:border-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', disc.trimestres[t]?.avaliacoes?.trabalhos?.adotado ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ disc.trimestres[t]?.avaliacoes?.trabalhos?.adotado ? '✓' : '' }}
                                                    </span>
                                                    Trabalhos individuais / em grupo / seminários
                                                </span>
                                                <span v-if="disc.trimestres[t]?.avaliacoes?.trabalhos?.adotado" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase">Adotado</span>
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 space-y-1.5 divide-y divide-slate-100 dark:divide-zinc-700/60 font-mono text-[11px]">
                                                <div class="flex justify-between items-center pt-1 first:pt-0">
                                                    <span class="text-slate-500">1º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.valor_1 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.nota_1 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">2º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.valor_2 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.nota_2 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">3º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.valor_3 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.trabalhos?.nota_3 || '-' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- avaliação: prova prática -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold flex items-center justify-between border-b border-slate-200 dark:border-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', disc.trimestres[t]?.avaliacoes?.prova_pratica?.adotado ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.adotado ? '✓' : '' }}
                                                    </span>
                                                    Prova Prática
                                                </span>
                                                <span v-if="disc.trimestres[t]?.avaliacoes?.prova_pratica?.adotado" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase">Adotado</span>
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 space-y-1.5 divide-y divide-slate-100 dark:divide-zinc-700/60 font-mono text-[11px]">
                                                <div class="flex justify-between items-center pt-1 first:pt-0">
                                                    <span class="text-slate-500">1º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.valor_1 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.nota_1 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">2º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.valor_2 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.nota_2 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">3º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.valor_3 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_pratica?.nota_3 || '-' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- avaliação: outros instrumentos específicos -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold flex items-center justify-between border-b border-slate-200 dark:border-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', disc.trimestres[t]?.avaliacoes?.outros?.adotado ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ disc.trimestres[t]?.avaliacoes?.outros?.adotado ? '✓' : '' }}
                                                    </span>
                                                    Outros
                                                    <span v-if="disc.trimestres[t]?.especifique_outros" class="font-normal italic text-slate-500 dark:text-zinc-400">
                                                        ({{ disc.trimestres[t]?.especifique_outros }})
                                                    </span>
                                                </span>
                                                <span v-if="disc.trimestres[t]?.avaliacoes?.outros?.adotado" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase">Adotado</span>
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 space-y-1.5 divide-y divide-slate-100 dark:divide-zinc-700/60 font-mono text-[11px]">
                                                <div class="flex justify-between items-center pt-1 first:pt-0">
                                                    <span class="text-slate-500">1º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.valor_1 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.nota_1 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">2º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.valor_2 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.nota_2 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">3º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.valor_3 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.outros?.nota_3 || '-' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- avaliação: prova oral -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold flex items-center justify-between border-b border-slate-200 dark:border-zinc-700">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', disc.trimestres[t]?.avaliacoes?.prova_oral?.adotado ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ disc.trimestres[t]?.avaliacoes?.prova_oral?.adotado ? '✓' : '' }}
                                                    </span>
                                                    Prova oral
                                                </span>
                                                <span v-if="disc.trimestres[t]?.avaliacoes?.prova_oral?.adotado" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase">Adotado</span>
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 space-y-1.5 divide-y divide-slate-100 dark:divide-zinc-700/60 font-mono text-[11px]">
                                                <div class="flex justify-between items-center pt-1 first:pt-0">
                                                    <span class="text-slate-500">1º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.valor_1 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.nota_1 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">2º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.valor_2 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.nota_2 || '-' }}</strong></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-slate-500">3º valor: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.valor_3 || '-' }}</strong></span>
                                                    <span class="text-slate-800 dark:text-zinc-200">nota do discente: <strong>{{ disc.trimestres[t]?.avaliacoes?.prova_oral?.nota_3 || '-' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- grau de autonomia do discente para realizar as atividades -->
                                        <div class="border border-slate-200 dark:border-zinc-700 rounded-sm overflow-hidden text-xs flex flex-col">
                                            <div class="bg-slate-100 dark:bg-zinc-900 px-3 py-2 font-semibold border-b border-slate-200 dark:border-zinc-700 text-slate-800 dark:text-zinc-200">
                                                Grau de autonomia para realizar as atividades:
                                            </div>
                                            <div class="p-3 bg-white dark:bg-zinc-800 grid grid-cols-2 gap-2 flex-1 items-center">
                                                <div
                                                    v-for="opt in [
                                                        { key: 'muito_suporte', label: 'Muito suporte' },
                                                        { key: 'pouco_suporte', label: 'Pouco suporte' },
                                                        { key: 'alta_compreensao', label: 'Alta compreensão' },
                                                        { key: 'pouca_compreensao', label: 'Pouca compreensão' }
                                                    ]"
                                                    :key="opt.key"
                                                    :class="[
                                                        'p-2 rounded border flex items-center gap-2 text-xs transition-colors',
                                                        (disc.trimestres[t]?.grau_autonomia || []).includes(opt.key)
                                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-900 dark:text-emerald-200 font-semibold'
                                                            : 'bg-slate-50/50 dark:bg-zinc-900/40 border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400'
                                                    ]"
                                                >
                                                    <span :class="['w-4 h-4 rounded-xs border flex items-center justify-center font-bold text-[10px]', (disc.trimestres[t]?.grau_autonomia || []).includes(opt.key) ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800']">
                                                        {{ (disc.trimestres[t]?.grau_autonomia || []).includes(opt.key) ? '✓' : '' }}
                                                    </span>
                                                    <span>{{ opt.label }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de edição do nome da equipe de apoio -->
        <!-- ============================================== -->
        <div
            v-if="showEditApoioModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Renomear Equipe de Apoio
                    </h3>
                    <button
                        type="button"
                        @click="closeEditApoioModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-lg leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitEditApoio" class="space-y-4 text-xs">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome da Equipe / Tipo de Apoio <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="editApoioForm.nome"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeEditApoioModal"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="editApoioForm.processing"
                            class="px-4 py-1.5 bg-[#006633] hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm"
                        >
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de registro e edição de ações trimestrais da equipe de apoio -->
        <!-- ============================================== -->
        <div
            v-if="showApoioTrimestreModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xl max-w-3xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Registrar Ações de {{ currentApoio?.nome }} - {{ apoioTrimestreForm.trimestre }}º Trimestre
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ pei.aluno.nome }} ({{ pei.codigo }})
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="closeApoioTrimestreModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-lg leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitApoioTrimestreForm" class="space-y-5 text-xs">
                    <!-- responsáveis pelas ações deste trimestre -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px]">
                                Responsáveis pelo Atendimento ({{ apoioTrimestreForm.trimestre }}º Trimestre):
                            </span>
                            <button
                                type="button"
                                @click="addApoioTriResponsavel"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar Responsável
                            </button>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(resp, rIdx) in apoioTrimestreForm.responsaveis"
                                :key="rIdx"
                                class="flex items-center gap-2 p-2 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700"
                            >
                                <select
                                    v-model="resp.tipo"
                                    class="px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                >
                                    <option value="usuario">Usuário do Sistema</option>
                                    <option value="externo">Pessoa Externa</option>
                                </select>

                                <select
                                    v-if="resp.tipo === 'usuario'"
                                    v-model="resp.user_id"
                                    class="flex-1 px-2.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                >
                                    <option value="">Selecione o usuário...</option>
                                    <option
                                        v-for="u in usuariosDisponiveis"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }} ({{ u.cargo || 'Geral' }})
                                    </option>
                                </select>
                                <input
                                    v-else
                                    type="text"
                                    v-model="resp.nome_externo"
                                    placeholder="Nome e função da pessoa externa..."
                                    class="flex-1 px-2.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                />

                                <button
                                    v-if="apoioTrimestreForm.responsaveis.length > 1"
                                    type="button"
                                    @click="removeApoioTriResponsavel(rIdx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ações e atividades desenvolvidas no trimestre -->
                    <div class="space-y-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px]">
                                Ações / Atividades Desenvolvidas pela Equipe:
                            </span>
                            <button
                                type="button"
                                @click="addApoioTriAtividade"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar Ação/Atividade
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(atv, aIdx) in apoioTrimestreForm.atividades"
                                :key="aIdx"
                                class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 space-y-2.5"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-700 dark:text-zinc-300 text-xs">
                                        Atividade #{{ aIdx + 1 }}
                                    </span>
                                    <button
                                        v-if="apoioTrimestreForm.atividades.length > 1"
                                        type="button"
                                        @click="removeApoioTriAtividade(aIdx)"
                                        class="text-red-600 dark:text-red-400 text-xs hover:underline"
                                    >
                                        Remover
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Objetivos <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="atv.objetivos"
                                            required
                                            rows="2"
                                            placeholder="Objetivos do acompanhamento..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Recursos / Ações Executadas <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="atv.recursos_acoes"
                                            required
                                            rows="2"
                                            placeholder="Recursos disponibilizados ou ações executadas..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Tempo <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.tempo"
                                            required
                                            placeholder="Ex: 2h semanais / Trimestral"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Avaliação de Desempenho / Resultados
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.avaliacao_resultados"
                                            placeholder="Resultados observados..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Sugestões para Melhor Aproveitamento
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.sugestoes_aproveitamento"
                                            placeholder="Sugestões de continuidade..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeApoioTrimestreModal"
                            class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="apoioTrimestreForm.processing"
                            class="px-4 py-1.5 bg-[#006633] hover:bg-emerald-800 text-white text-xs font-semibold rounded-sm shadow-sm"
                        >
                            Salvar Ações do Trimestre
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de registro e edição de ações trimestrais do nai -->
        <!-- ============================================== -->
        <div
            v-if="showNaiModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-xl max-w-3xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Registrar Ações do NAI - {{ naiForm.trimestre }}º Trimestre
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ pei.aluno.nome }} ({{ pei.codigo }})
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="closeNaiModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-lg leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitNaiForm" class="space-y-5 text-xs">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px]">
                                Responsáveis pelo Atendimento ({{ naiForm.trimestre }}º Trimestre):
                            </span>
                            <div class="flex items-center space-x-3">
                                <button
                                    v-if="naiForm.trimestre > 1"
                                    type="button"
                                    @click="copiarResponsaveisNai1"
                                    class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline cursor-pointer"
                                >
                                    Copiar responsáveis do 1º Trimestre
                                </button>
                                <button
                                    type="button"
                                    @click="addNaiResponsavel"
                                    class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline cursor-pointer"
                                >
                                    + Adicionar Responsável
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(resp, rIdx) in naiForm.responsaveis"
                                :key="rIdx"
                                class="flex items-center gap-2 p-2 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700"
                            >
                                <select
                                    v-model="resp.tipo"
                                    class="px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                >
                                    <option value="usuario">Usuário do Sistema</option>
                                    <option value="externo">Pessoa Externa</option>
                                </select>

                                <select
                                    v-if="resp.tipo === 'usuario'"
                                    v-model="resp.user_id"
                                    class="flex-1 px-2.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                >
                                    <option value="">Selecione o usuário...</option>
                                    <option
                                        v-for="u in usuariosDisponiveis"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }} ({{ u.cargo || 'Geral' }})
                                    </option>
                                </select>
                                <input
                                    v-else
                                    type="text"
                                    v-model="resp.nome_externo"
                                    placeholder="Nome da pessoa externa..."
                                    class="flex-1 px-2.5 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs"
                                />

                                <button
                                    v-if="naiForm.responsaveis.length > 1"
                                    type="button"
                                    @click="removeNaiResponsavel(rIdx)"
                                    class="text-slate-400 hover:text-red-600 text-sm font-bold px-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase text-[11px]">
                                Ações / Atividades Desenvolvidas:
                            </span>
                            <button
                                type="button"
                                @click="addNaiAtividade"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar Ação/Atividade
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(atv, aIdx) in naiForm.atividades"
                                :key="aIdx"
                                class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded border border-slate-200 dark:border-zinc-700 space-y-2.5"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-700 dark:text-zinc-300 text-xs">
                                        Atividade #{{ aIdx + 1 }}
                                    </span>
                                    <button
                                        v-if="naiForm.atividades.length > 1"
                                        type="button"
                                        @click="removeNaiAtividade(aIdx)"
                                        class="text-red-600 dark:text-red-400 text-xs hover:underline"
                                    >
                                        Remover
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Objetivos <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="atv.objetivos"
                                            required
                                            rows="2"
                                            placeholder="Objetivos pedagógicos e inclusivos..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Recursos / Ações Executadas <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="atv.recursos_acoes"
                                            required
                                            rows="2"
                                            placeholder="Recursos disponibilizados ou ações executadas..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Tempo <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.tempo"
                                            required
                                            placeholder="Ex: Quinzenal / 1º Trimestre"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Avaliação de Desempenho / Resultados
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.avaliacao_resultados"
                                            placeholder="Resultados observados..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Sugestões para Melhor Aproveitamento
                                        </label>
                                        <input
                                            type="text"
                                            v-model="atv.sugestoes_aproveitamento"
                                            placeholder="Sugestões de continuidade..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeNaiModal"
                            class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="naiForm.processing"
                            class="px-4 py-1.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            Salvar Ações do NAI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de planejamento e avaliação trimestral da disciplina -->
        <!-- ============================================== -->
        <div
            v-if="showDisciplinaTrimestreModal && currentDisciplina"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-5xl w-full p-6 space-y-6 max-h-[90vh] overflow-y-auto my-8">
                <!-- cabeçalho do modal de disciplina -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#006633] dark:text-emerald-400">
                            {{ disciplinaTrimestreForm.trimestre }}º Trimestre • Adaptação Curricular & Avaliação
                        </span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            [{{ currentDisciplina.codigo }}] {{ currentDisciplina.nome }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeDisciplinaTrimestreModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitDisciplinaTrimestreForm" class="space-y-6 text-xs">
                    <!-- ============================================== -->
                    <!-- parte 1: planejamento pedagógico e adaptações curriculares -->
                    <!-- ============================================== -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Parte 1: Planejamento Pedagógico / Adaptação Curricular
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                    Insira as linhas de planejamento e intervenções pedagógicas propostas para a disciplina.
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="addPlanejamentoRow"
                                class="inline-flex items-center px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-[#006633] dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 font-semibold text-xs rounded border border-emerald-300 dark:border-emerald-700 transition-colors"
                            >
                                + Adicionar Linha
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div
                                v-for="(plan, pIdx) in disciplinaTrimestreForm.planejamentos"
                                :key="pIdx"
                                class="p-4 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-4"
                            >
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                                    <span class="font-bold text-slate-800 dark:text-zinc-200 text-xs">
                                        Item de Planejamento #{{ pIdx + 1 }}
                                    </span>
                                    <button
                                        v-if="disciplinaTrimestreForm.planejamentos.length > 1"
                                        type="button"
                                        @click="removePlanejamentoRow(pIdx)"
                                        class="text-red-600 dark:text-red-400 text-xs font-semibold hover:underline"
                                    >
                                        Remover
                                    </button>
                                </div>

                                <!-- objetivos e conteúdos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Objetivos <span class="text-slate-400 font-normal lowercase">(acadêmicos)</span> <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="plan.objetivos"
                                            required
                                            rows="2"
                                            placeholder="Descreva os objetivos acadêmicos esperados..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                            Conteúdos <span class="text-slate-400 font-normal lowercase">(o que aprender/ensinar?)</span> <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="plan.conteudos"
                                            required
                                            rows="2"
                                            placeholder="Conteúdos curriculares priorizados..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- metodologias / estratégias / recursos -->
                                <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-4">
                                    <div class="border-b border-slate-200 dark:border-zinc-700 pb-2">
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                            Metodologias / estratégias / recursos
                                        </h5>
                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                            Como ensinar? O que usar para ensinar e como avaliar?
                                        </p>
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
                                                placeholder="Descreva outras estratégias de ensino..."
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
                                                placeholder="Descreva outras estratégias/condições de avaliação..."
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- tempo (prazo de realização de trabalho) -->
                                <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase">
                                        Tempo <span class="text-slate-400 font-normal lowercase">(prazo de realização de trabalho?)</span> <span class="text-red-500">*</span>
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
                                                :name="`tempo_opcao_${pIdx}`"
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

                                <!-- avaliação de desempenho / resultados -->
                                <div class="p-3.5 bg-white dark:bg-zinc-800 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-3">
                                    <div class="flex items-baseline justify-between border-b border-slate-200 dark:border-zinc-700 pb-2">
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                Avaliação de desempenho / Resultados
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
                                            placeholder="Descreva outras observações específicas sobre o desempenho..."
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- sugestões para melhor aproveitamento -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                                        Sugestões para melhor aproveitamento do discente
                                    </label>
                                    <textarea
                                        v-model="plan.sugestoes_aproveitamento"
                                        rows="2"
                                        placeholder="Sugestões e encaminhamentos para os próximos períodos..."
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
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Parte 2: Instrumentos de Avaliação Adotados
                            </h4>
                            <!-- aviso sobre preenchimento das adaptações de avaliação -->
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
                                v-for="(av, avIdx) in disciplinaTrimestreForm.avaliacoes"
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
                                    <span v-else-if="av.tipo === 'prova_pratica'">Prova Prática</span>
                                    <span v-else-if="av.tipo === 'outros'">Outros, quais? Especifique</span>
                                    <span v-else-if="av.tipo === 'prova_oral'">Prova oral</span>
                                </label>

                                <div v-if="av.tipo === 'outros' && av.adotado" class="pt-1">
                                    <input
                                        type="text"
                                        v-model="disciplinaTrimestreForm.especifique_outros"
                                        placeholder="Especifique o tipo de avaliação..."
                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>

                                <div v-if="av.adotado" class="space-y-2 pt-1 border-t border-slate-200 dark:border-zinc-800">
                                    <div
                                        v-for="num in [1, 2, 3]"
                                        :key="num"
                                        class="grid grid-cols-2 gap-2"
                                    >
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-zinc-400 uppercase">
                                                {{ num }}º Valor
                                            </label>
                                            <input
                                                type="text"
                                                v-model="av['valor_' + num]"
                                                placeholder="Ex: 10.0"
                                                class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs font-mono focus:ring-1 focus:ring-emerald-600"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-zinc-400 uppercase">
                                                Nota do Discente
                                            </label>
                                            <input
                                                type="text"
                                                v-model="av['nota_' + num]"
                                                placeholder="Ex: 8.5"
                                                class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs font-mono focus:ring-1 focus:ring-emerald-600"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- grau de autonomia do discente -->
                        <div class="p-4 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                            <label class="block text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase">
                                Grau de autonomia para realizar as atividades:
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                <label
                                    v-for="opt in [
                                        { key: 'muito_suporte', label: 'Muito suporte' },
                                        { key: 'pouco_suporte', label: 'Pouco suporte' },
                                        { key: 'alta_compreensao', label: 'Alta compreensão' },
                                        { key: 'pouca_compreensao', label: 'Pouca compreensão' }
                                    ]"
                                    :key="opt.key"
                                    class="flex items-center space-x-2.5 text-xs text-slate-700 dark:text-zinc-300 cursor-pointer select-none bg-white dark:bg-zinc-800 p-2.5 rounded border border-slate-200 dark:border-zinc-700"
                                >
                                    <input
                                        type="checkbox"
                                        :value="opt.key"
                                        v-model="disciplinaTrimestreForm.grau_autonomia"
                                        class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                                    />
                                    <span>{{ opt.label }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- botões de ação do modal -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeDisciplinaTrimestreModal"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="disciplinaTrimestreForm.processing"
                            class="px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            Salvar Planejamento e Avaliações ({{ disciplinaTrimestreForm.trimestre }}º Trimestre)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de edição da ficha do discente -->
        <!-- ============================================== -->
        <div
            v-if="showFichaAlunoModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-3xl w-full p-6 space-y-6 max-h-[90vh] overflow-y-auto my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#006633] dark:text-emerald-400">
                            PEI • {{ pei.codigo }}
                        </span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Editar Ficha do Aluno - {{ pei.aluno.nome }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeFichaAlunoModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitFichaAlunoForm" class="space-y-4 text-xs">
                    <!-- 1. atividades de tempo livre -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            O que gosta de fazer no tempo livre?
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.tempo_livre"
                            rows="2"
                            placeholder="Descreva interesses, jogos, hobbies..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 2. prática de esportes e atividades físicas -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Gosta de praticar esporte?
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.pratica_esporte"
                            rows="2"
                            placeholder="Modalidades esportivas, frequência, preferências..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 3. características pedagógicas e comportamentais -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Características do discente:
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.caracteristicas_discente"
                            rows="3"
                            placeholder="Comportamento, foco, temperamento, perfil cognitivo..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 4. organização e cumprimento de prazos -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.organizacao_e_prazos"
                            rows="2"
                            placeholder="Pontualidade, rotina de estudos, uso de agendas/aplicativos..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 5. interação social e comunicação -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Apresenta dificuldade na interação social?
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.dificuldade_interacao_social"
                            rows="2"
                            placeholder="Relação com colegas, professores, trabalho em grupo..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- 6. sugestões de recursos e estratégias -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Sugestões de recursos ou estratégias que possam ser utilizadas para auxiliar no processo de aprendizagem:
                        </label>
                        <textarea
                            v-model="fichaAlunoForm.sugestoes_recursos_estrategias"
                            rows="3"
                            placeholder="Adaptações de material, tempo adicional, ambiente silencioso, tecnologia assistiva..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeFichaAlunoModal"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="fichaAlunoForm.processing"
                            class="px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            Salvar Ficha do Aluno
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- modal de edição da ficha do pei e intervenções -->
        <!-- ============================================== -->
        <div
            v-if="showFichaPeiModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-sm shadow-2xl max-w-4xl w-full p-6 space-y-6 max-h-[90vh] overflow-y-auto my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-700 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#006633] dark:text-emerald-400">
                            PEI • {{ pei.codigo }}
                        </span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Editar Ficha do PEI - {{ pei.aluno.nome }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeFichaPeiModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitFichaPeiForm" class="space-y-6 text-xs">
                    <!-- diagnóstico e necessidades específicas -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Diagnóstico / Necessidades Específicas
                        </label>
                        <input
                            type="text"
                            v-model="fichaPeiForm.diagnostico"
                            placeholder="Ex: [F84.0] Transtorno do Espectro Autista..."
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                    </div>

                    <!-- responsáveis pela elaboração da ficha -->
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase">
                                Responsáveis pela Elaboração:
                            </span>
                            <button
                                type="button"
                                @click="addFichaPeiResponsavel"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar Responsável
                            </button>
                        </div>

                        <div
                            v-for="(resp, rIdx) in fichaPeiForm.responsaveis"
                            :key="rIdx"
                            class="flex items-center gap-3 bg-slate-50 dark:bg-zinc-900/60 p-2.5 rounded border border-slate-200 dark:border-zinc-700"
                        >
                            <select
                                v-model="resp.tipo"
                                class="w-32 px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                            >
                                <option value="usuario">Usuário do Sistema</option>
                                <option value="externo">Pessoa Externa</option>
                            </select>

                            <select
                                v-if="resp.tipo === 'usuario'"
                                v-model="resp.user_id"
                                class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                            >
                                <option value="">Selecione o usuário...</option>
                                <option
                                    v-for="u in usuariosDisponiveis"
                                    :key="u.id"
                                    :value="String(u.id)"
                                >
                                    {{ u.name }} ({{ u.cargo }})
                                </option>
                            </select>

                            <input
                                v-else
                                type="text"
                                v-model="resp.nome_externo"
                                placeholder="Nome e cargo/vínculo da pessoa externa..."
                                class="flex-1 px-2.5 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                            />

                            <button
                                v-if="fichaPeiForm.responsaveis.length > 1"
                                type="button"
                                @click="removeFichaPeiResponsavel(rIdx)"
                                class="text-slate-400 hover:text-red-600 px-1 text-sm font-bold"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    <!-- 6 categorias de identificação pedagógica e fatores contextuais -->
                    <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-zinc-700">
                        <h4 class="font-bold text-slate-800 dark:text-zinc-200 uppercase">
                            O que identificamos com relação ao(à) estudante?
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- habilidades identificadas -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Suas Habilidades:</span>
                                    <button type="button" @click="addFichaPeiItem('habilidades')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(h, idx) in fichaPeiForm.habilidades" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.habilidades[idx]" placeholder="Descreva uma habilidade..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.habilidades.length > 1" type="button" @click="removeFichaPeiItem('habilidades', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>

                            <!-- dificuldades identificadas -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Suas Dificuldades:</span>
                                    <button type="button" @click="addFichaPeiItem('dificuldades')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(d, idx) in fichaPeiForm.dificuldades" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.dificuldades[idx]" placeholder="Descreva uma dificuldade..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.dificuldades.length > 1" type="button" @click="removeFichaPeiItem('dificuldades', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>

                            <!-- dificultadores internos do campus -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Dificultadores Internos:</span>
                                    <button type="button" @click="addFichaPeiItem('dificultadores_internos')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(di, idx) in fichaPeiForm.dificultadores_internos" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.dificultadores_internos[idx]" placeholder="Ex: Aulas expositivas sem recursos visuais..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.dificultadores_internos.length > 1" type="button" @click="removeFichaPeiItem('dificultadores_internos', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>

                            <!-- facilitadores internos do campus -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Facilitadores Internos:</span>
                                    <button type="button" @click="addFichaPeiItem('facilitadores_internos')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(fi, idx) in fichaPeiForm.facilitadores_internos" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.facilitadores_internos[idx]" placeholder="Ex: Fonte ampliada, fones abafadores..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.facilitadores_internos.length > 1" type="button" @click="removeFichaPeiItem('facilitadores_internos', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>

                            <!-- dificultadores externos ao campus -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Dificultadores Externos:</span>
                                    <button type="button" @click="addFichaPeiItem('dificultadores_externos')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(de, idx) in fichaPeiForm.dificultadores_externos" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.dificultadores_externos[idx]" placeholder="Ex: Longo tempo de transporte..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.dificultadores_externos.length > 1" type="button" @click="removeFichaPeiItem('dificultadores_externos', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>

                            <!-- facilitadores externos ao campus -->
                            <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-300 uppercase">Facilitadores Externos:</span>
                                    <button type="button" @click="addFichaPeiItem('facilitadores_externos')" class="text-[#006633] dark:text-emerald-400 font-bold text-xs hover:underline">+ Item</button>
                                </div>
                                <div v-for="(fe, idx) in fichaPeiForm.facilitadores_externos" :key="idx" class="flex gap-2">
                                    <input type="text" v-model="fichaPeiForm.facilitadores_externos[idx]" placeholder="Ex: Apoio familiar, acompanhamento psicológico..." class="flex-1 px-2 py-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                    <button v-if="fichaPeiForm.facilitadores_externos.length > 1" type="button" @click="removeFichaPeiItem('facilitadores_externos', idx)" class="text-slate-400 hover:text-red-600">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- propostas de intervenção pedagógica -->
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 uppercase">
                                Possibilidades de Intervenção:
                            </span>
                            <button
                                type="button"
                                @click="addFichaPeiIntervencao"
                                class="text-[11px] font-semibold text-[#006633] dark:text-emerald-400 hover:underline"
                            >
                                + Adicionar Intervenção
                            </button>
                        </div>

                        <div
                            v-for="(int, iIdx) in fichaPeiForm.intervencoes"
                            :key="iIdx"
                            class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-sm border border-slate-200 dark:border-zinc-700 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-700 dark:text-zinc-300 text-xs">Intervenção #{{ iIdx + 1 }}</span>
                                <button v-if="fichaPeiForm.intervencoes.length > 1" type="button" @click="removeFichaPeiIntervencao(iIdx)" class="text-xs text-red-600 hover:underline">Remover</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 uppercase">Demanda</label>
                                    <input type="text" v-model="int.demanda" placeholder="Demanda identificada..." class="w-full px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 uppercase">Encaminhamento / Estratégia</label>
                                    <input type="text" v-model="int.encaminhamento_estrategia" placeholder="Solução / Ação pedagógica..." class="w-full px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 uppercase">Responsável</label>
                                    <input type="text" v-model="int.responsavel" placeholder="Ex: Docentes, NAI..." class="w-full px-2 py-1.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <button
                            type="button"
                            @click="closeFichaPeiModal"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="fichaPeiForm.processing"
                            class="px-5 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                        >
                            Salvar Ficha do PEI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- modal de impressão do relatório completo / seletivo do pei -->
        <RelatorioPeiModal
            :show="showRelatorioPeiModal"
            :pei="pei"
            @close="showRelatorioPeiModal = false"
        />

        <!-- prompt de impressão após preenchimento de disciplina -->
        <ImprimirDisciplinaPromptModal
            :show="showImprimirDisciplinaPrompt"
            :pei-id="pei.id"
            :pei-codigo="pei.codigo"
            :aluno-nome="pei.aluno?.nome || ''"
            :disciplina-id="disciplinaRecemSalva?.id"
            :disciplina-codigo="disciplinaRecemSalva?.codigo || ''"
            :disciplina-nome="disciplinaRecemSalva?.nome || ''"
            :trimestre="disciplinaRecemSalva?.trimestre || 1"
            @close="showImprimirDisciplinaPrompt = false"
        />
    </AuthenticatedLayout>
</template>
