<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    nome: {
        type: String,
        default: '',
    },
    // tamanhos disponíveis: xs (24px), sm (32px), md (40px), lg (112px)
    size: {
        type: String,
        default: 'sm',
    },
});

// controla falhas de carregamento para exibir as iniciais como alternativa
const falhou = ref(false);
watch(() => props.src, () => {
    falhou.value = false;
});

const iniciais = computed(() => {
    const partes = (props.nome || '').trim().split(/\s+/).filter(Boolean);
    if (partes.length === 0) return '?';
    const primeira = partes[0][0];
    const ultima = partes.length > 1 ? partes[partes.length - 1][0] : '';
    return (primeira + ultima).toUpperCase();
});

const tamanhos = {
    xs: 'w-6 h-6 text-[9px]',
    sm: 'w-8 h-8 text-[11px]',
    md: 'w-10 h-10 text-xs',
    lg: 'w-28 h-28 text-2xl',
};

const classeTamanho = computed(() => tamanhos[props.size] || tamanhos.sm);
const mostrarFoto = computed(() => Boolean(props.src) && !falhou.value);
</script>

<template>
    <span
        :class="[
            'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full select-none',
            'ring-1 ring-slate-200 dark:ring-zinc-700',
            mostrarFoto ? 'bg-slate-100 dark:bg-zinc-900' : 'bg-emerald-100 dark:bg-emerald-950 text-[#006633] dark:text-emerald-300 font-bold',
            classeTamanho,
        ]"
        :title="nome"
    >
        <img
            v-if="mostrarFoto"
            :src="src"
            :alt="`Foto de ${nome}`"
            loading="lazy"
            class="h-full w-full object-cover"
            @error="falhou = true"
        />
        <span v-else aria-hidden="true">{{ iniciais }}</span>
    </span>
</template>
