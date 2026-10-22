<script setup>
import { computed } from 'vue';

const props = defineProps({
    totalItems: {
        type: Number,
        required: true,
    },
    itemsPerPage: {
        type: Number,
        default: 10,
    },
    modelValue: {
        type: Number,
        default: 1,
    },
    itemLabel: {
        type: String,
        default: 'registros',
    },
});

const emit = defineEmits(['update:modelValue']);

const totalPages = computed(() => Math.ceil(props.totalItems / props.itemsPerPage) || 1);

const from = computed(() => {
    if (props.totalItems === 0) return 0;
    return (props.modelValue - 1) * props.itemsPerPage + 1;
});

const to = computed(() => {
    return Math.min(props.modelValue * props.itemsPerPage, props.totalItems);
});

const setPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        emit('update:modelValue', page);
    }
};

const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = props.modelValue;
    const delta = 2;
    const pages = [];

    for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
        pages.push(i);
    }
    return pages;
});
</script>

<template>
    <div
        v-if="totalItems > 0"
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/40 text-xs"
    >
        <div class="text-slate-500 dark:text-zinc-400">
            Exibindo <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ from }}</span> até <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ to }}</span> de <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ totalItems }}</span> {{ itemLabel }}
        </div>

        <div v-if="totalPages > 1" class="flex items-center space-x-1">
            <!-- botão para página anterior -->
            <button
                type="button"
                @click="setPage(modelValue - 1)"
                :disabled="modelValue <= 1"
                :class="[
                    'px-2.5 py-1.5 rounded border text-xs font-semibold transition-colors flex items-center',
                    modelValue <= 1
                        ? 'opacity-40 cursor-not-allowed border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-slate-400 dark:text-zinc-600'
                        : 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700 cursor-pointer'
                ]"
            >
                &larr; Anterior
            </button>

            <!-- atalho para primeira página -->
            <template v-if="visiblePages[0] > 1">
                <button
                    type="button"
                    @click="setPage(1)"
                    class="px-2.5 py-1.5 rounded border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 text-xs font-semibold cursor-pointer"
                >
                    1
                </button>
                <span v-if="visiblePages[0] > 2" class="px-1 text-slate-400 dark:text-zinc-600">...</span>
            </template>

            <!-- numeração de páginas visíveis -->
            <button
                v-for="p in visiblePages"
                :key="p"
                type="button"
                @click="setPage(p)"
                :class="[
                    'px-2.5 py-1.5 rounded border text-xs font-semibold transition-colors cursor-pointer',
                    modelValue === p
                        ? 'bg-[#006633] text-white border-[#006633] shadow-2xs'
                        : 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700'
                ]"
            >
                {{ p }}
            </button>

            <!-- atalho para última página -->
            <template v-if="visiblePages[visiblePages.length - 1] < totalPages">
                <span v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="px-1 text-slate-400 dark:text-zinc-600">...</span>
                <button
                    type="button"
                    @click="setPage(totalPages)"
                    class="px-2.5 py-1.5 rounded border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 text-xs font-semibold cursor-pointer"
                >
                    {{ totalPages }}
                </button>
            </template>

            <!-- botão para próxima página -->
            <button
                type="button"
                @click="setPage(modelValue + 1)"
                :disabled="modelValue >= totalPages"
                :class="[
                    'px-2.5 py-1.5 rounded border text-xs font-semibold transition-colors flex items-center',
                    modelValue >= totalPages
                        ? 'opacity-40 cursor-not-allowed border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-slate-400 dark:text-zinc-600'
                        : 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700 cursor-pointer'
                ]"
            >
                Próximo &rarr;
            </button>
        </div>
    </div>
</template>
