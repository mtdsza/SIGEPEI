<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => null,
    },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-slate-200 dark:border-zinc-700 text-xs">
        <div v-if="meta" class="text-slate-500 dark:text-zinc-400">
            Exibindo <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ meta.from || 0 }}</span> até <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ meta.to || 0 }}</span> de <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ meta.total || 0 }}</span> registros
        </div>
        <div v-else class="text-slate-500 dark:text-zinc-400">
            Navegação entre páginas
        </div>

        <div class="flex items-center space-x-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-3 py-1.5 text-xs text-slate-400 dark:text-zinc-600 rounded border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 cursor-not-allowed select-none"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    :class="[
                        'px-3 py-1.5 text-xs font-semibold rounded border transition-colors cursor-pointer',
                        link.active
                            ? 'bg-[#006633] text-white border-[#006633] shadow-xs'
                            : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-300 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700'
                    ]"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
