<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isDarkMode = ref(false);

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('sigepei_theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('sigepei_theme', 'light');
    }
};

onMounted(() => {
    const savedTheme = localStorage.getItem('sigepei_theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-slate-100 dark:bg-zinc-900 text-slate-800 dark:text-zinc-100 p-4 transition-colors duration-150 relative font-sans">
        <!-- alternador de tema claro/escuro superior -->
        <div class="absolute top-4 right-4">
            <button
                type="button"
                @click="toggleTheme"
                class="flex items-center space-x-1.5 px-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-sm text-xs font-medium text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 shadow-2xs transition-colors"
                title="Alternar tema claro/escuro"
            >
                <svg v-if="isDarkMode" class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span>{{ isDarkMode ? 'Modo Claro' : 'Modo Escuro' }}</span>
            </button>
        </div>

        <div class="w-full sm:max-w-md">
            <!-- cabeçalho com identidade visual do sistema -->
            <div class="bg-[#006633] dark:bg-black border border-emerald-800 dark:border-zinc-800 rounded-t-sm p-6 text-center shadow-sm">
                <Link href="/" class="inline-block">
                    <span class="text-3xl font-bold tracking-widest text-white block">
                        SIGEPEI
                    </span>
                    <span class="text-xs font-medium text-emerald-100/90 dark:text-zinc-400 uppercase tracking-wider block mt-1">
                        Sistema de Gerenciamento de PEIs
                    </span>
                </Link>
            </div>

            <!-- contêiner principal do formulário -->
            <div class="bg-white dark:bg-zinc-800 border-x border-b border-slate-200 dark:border-zinc-700 rounded-b-sm p-6 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
