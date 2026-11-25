<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AlunoAvatar from '@/Components/AlunoAvatar.vue';
import { useUnsavedChanges } from '@/composables/useUnsavedChanges';

const props = defineProps({
    aluno: {
        type: Object,
        required: true,
    },
    deficienciasDisponiveis: {
        type: Array,
        default: () => [],
    },
    turmasDisponiveis: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    _method: 'put',
    nome: props.aluno.nome,
    matricula: props.aluno.matricula,
    data_nascimento: props.aluno.data_nascimento,
    turma_id: props.aluno.turma_id || '',
    deficiencias: props.aluno.deficiencias_ids || [],
    foto: null,
    remover_foto: false,
});

useUnsavedChanges(form);

// gerenciamento da foto de perfil do discente
const TAMANHO_MAXIMO_ORIGINAL = 15 * 1024 * 1024;
const DIMENSAO_MAXIMA = 800;
const fotoInput = ref(null);
const previewUrl = ref(null);
const fotoErroLocal = ref('');
const processandoFoto = ref(false);

const fotoExibida = computed(() => {
    if (previewUrl.value) return previewUrl.value;
    if (form.remover_foto) return null;
    return props.aluno.foto_url;
});

const liberarPreview = () => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const selecionarFoto = () => {
    fotoInput.value?.click();
};

/**
 * reduz a imagem para no máximo DIMENSAO_MAXIMA pixels no maior lado e converte para jpeg,
 * mantendo o envio leve e compatível com o limite de upload do servidor.
 */
const redimensionarImagem = (arquivo) => new Promise((resolve, reject) => {
    const url = URL.createObjectURL(arquivo);
    const img = new Image();
    img.onload = () => {
        const escala = Math.min(1, DIMENSAO_MAXIMA / Math.max(img.width, img.height));
        const largura = Math.round(img.width * escala);
        const altura = Math.round(img.height * escala);
        const canvas = document.createElement('canvas');
        canvas.width = largura;
        canvas.height = altura;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, largura, altura);
        ctx.drawImage(img, 0, 0, largura, altura);
        URL.revokeObjectURL(url);
        canvas.toBlob((blob) => {
            if (!blob) return reject(new Error('Falha ao processar a imagem.'));
            const nome = (arquivo.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
            resolve(new File([blob], nome, { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.88);
    };
    img.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Não foi possível ler a imagem selecionada.'));
    };
    img.src = url;
});

const onFotoSelecionada = async (event) => {
    const arquivo = event.target.files?.[0];
    fotoErroLocal.value = '';
    if (!arquivo) return;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(arquivo.type)) {
        fotoErroLocal.value = 'Selecione uma imagem JPG, PNG ou WEBP.';
        event.target.value = '';
        return;
    }
    if (arquivo.size > TAMANHO_MAXIMO_ORIGINAL) {
        fotoErroLocal.value = 'A imagem selecionada é muito grande (máximo de 15 MB).';
        event.target.value = '';
        return;
    }

    processandoFoto.value = true;
    try {
        const redimensionada = await redimensionarImagem(arquivo);
        liberarPreview();
        form.foto = redimensionada;
        form.remover_foto = false;
        form.clearErrors('foto');
        previewUrl.value = URL.createObjectURL(redimensionada);
    } catch (e) {
        fotoErroLocal.value = e.message;
    } finally {
        processandoFoto.value = false;
        event.target.value = '';
    }
};

const removerFoto = () => {
    liberarPreview();
    form.foto = null;
    fotoErroLocal.value = '';
    if (fotoInput.value) fotoInput.value.value = '';
    // marca a remoção apenas se o aluno já possuir foto salva
    form.remover_foto = Boolean(props.aluno.foto_url);
};

const desfazerAlteracaoFoto = () => {
    liberarPreview();
    form.foto = null;
    form.remover_foto = false;
    fotoErroLocal.value = '';
    if (fotoInput.value) fotoInput.value.value = '';
};

const fotoAlterada = computed(() => Boolean(form.foto) || form.remover_foto);

onBeforeUnmount(liberarPreview);

const submit = () => {
    // envio via post com _method=put para suportar upload de arquivos (multipart)
    form.post(route('alunos.update', props.aluno.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Editar Aluno - ${aluno.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Editar Aluno - {{ aluno.nome }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Matrícula: {{ aluno.matricula }}
                    </p>
                </div>
                <Link
                    :href="route('alunos.show', aluno.id)"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm transition-colors"
                >
                    Voltar para Perfil
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- foto de perfil do discente -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 border-b border-slate-200 dark:border-zinc-700">
                    <div class="relative">
                        <AlunoAvatar :src="fotoExibida" :nome="form.nome || aluno.nome" size="lg" />
                        <span
                            v-if="fotoAlterada"
                            class="absolute -bottom-1 left-1/2 -translate-x-1/2 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide rounded-sm bg-amber-500 text-white shadow-sm whitespace-nowrap"
                        >
                            {{ form.remover_foto ? 'Será removida' : 'Não salva' }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase">
                                Foto de Perfil
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                                JPG, PNG ou WEBP. A imagem é ajustada automaticamente; prefira fotos com o rosto centralizado.
                            </p>
                        </div>

                        <input
                            ref="fotoInput"
                            id="aluno-foto-input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            @change="onFotoSelecionada"
                        />

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                id="aluno-foto-selecionar"
                                type="button"
                                @click="selecionarFoto"
                                :disabled="processandoFoto || form.processing"
                                class="inline-flex items-center px-3 py-1.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white text-xs font-semibold rounded-sm shadow-sm transition-colors disabled:opacity-50"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ processandoFoto ? 'Processando...' : (fotoExibida ? 'Trocar foto' : 'Enviar foto') }}
                            </button>
                            <button
                                v-if="fotoExibida"
                                id="aluno-foto-remover"
                                type="button"
                                @click="removerFoto"
                                class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 text-xs font-semibold rounded-sm border border-red-300 dark:border-red-800 transition-colors"
                            >
                                Remover
                            </button>
                            <button
                                v-if="fotoAlterada"
                                id="aluno-foto-desfazer"
                                type="button"
                                @click="desfazerAlteracaoFoto"
                                class="inline-flex items-center px-3 py-1.5 text-slate-600 dark:text-zinc-300 hover:underline text-xs font-semibold"
                            >
                                Desfazer
                            </button>
                        </div>

                        <div v-if="fotoErroLocal || form.errors.foto" class="text-xs text-red-600">
                            {{ fotoErroLocal || form.errors.foto }}
                        </div>
                        <div v-if="form.progress" class="w-48 h-1.5 bg-slate-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full bg-[#006633] transition-all" :style="{ width: `${form.progress.percentage}%` }"></div>
                        </div>
                    </div>
                </div>

                <!-- informações cadastrais básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.nome"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.nome" class="text-xs text-red-600 mt-1">
                            {{ form.errors.nome }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Matrícula <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.matricula"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.matricula" class="text-xs text-red-600 mt-1">
                            {{ form.errors.matricula }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Data de Nascimento <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.data_nascimento"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        />
                        <div v-if="form.errors.data_nascimento" class="text-xs text-red-600 mt-1">
                            {{ form.errors.data_nascimento }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">
                            Turma Atual
                        </label>
                        <select
                            v-model="form.turma_id"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">Selecione uma turma...</option>
                            <option
                                v-for="t in turmasDisponiveis"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.nome_curso }} ({{ t.ano_letivo }})
                            </option>
                        </select>
                        <div v-if="form.errors.turma_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.turma_id }}
                        </div>
                    </div>
                </div>

                <!-- seleção de transtornos e deficiências -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-2">
                        Transtornos / Deficiências Diagnosticadas
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-4 bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-700 rounded-sm">
                        <label
                            v-for="def in deficienciasDisponiveis"
                            :key="def.id"
                            class="flex items-center space-x-2.5 text-xs text-slate-700 dark:text-zinc-300 cursor-pointer select-none"
                        >
                            <input
                                type="checkbox"
                                :value="def.id"
                                v-model="form.deficiencias"
                                class="rounded-sm border-slate-300 dark:border-zinc-700 text-[#006633] focus:ring-emerald-600 dark:bg-zinc-900 h-4 w-4"
                            />
                            <span>
                                <span class="font-mono text-[11px] font-semibold text-slate-500 dark:text-zinc-400">[{{ def.codigo }}]</span>
                                {{ def.nome }}
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-700">
                    <Link
                        :href="route('alunos.show', aluno.id)"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-slate-700 dark:text-zinc-200 text-xs font-semibold rounded-sm border border-slate-300 dark:border-zinc-600"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || processandoFoto"
                        class="px-4 py-2 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white text-xs font-semibold rounded-sm shadow-sm disabled:opacity-50"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
