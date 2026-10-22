<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

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
    <Head title="Bem-vindo ao SIGEPEI" />

    <div class="min-h-screen bg-slate-100 dark:bg-zinc-900 text-slate-800 dark:text-zinc-100 font-sans transition-colors duration-150 flex flex-col justify-between">
        <!-- barra superior institucional -->
        <header class="bg-[#006633] dark:bg-black text-white border-b border-emerald-800 dark:border-zinc-800 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold tracking-wider">SIGEPEI</div>
                    <div class="text-[11px] text-emerald-100/80 dark:text-zinc-400 font-medium uppercase tracking-wide">
                        Sistema de Gerenciamento de Planos Educacionais Individualizados
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="px-3 py-1.5 bg-white/10 hover:bg-white/20 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-medium rounded-sm border border-white/20 dark:border-zinc-700 transition-colors"
                        title="Alternar tema claro/escuro"
                    >
                        {{ isDarkMode ? 'Modo Claro' : 'Modo Escuro' }}
                    </button>

                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="px-4 py-1.5 bg-white text-[#006633] dark:bg-emerald-600 dark:text-white font-semibold text-xs rounded-sm shadow-sm transition-colors hover:bg-slate-100"
                    >
                        Acessar Painel
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="px-4 py-1.5 bg-white text-[#006633] dark:bg-emerald-600 dark:text-white font-semibold text-xs rounded-sm shadow-sm transition-colors hover:bg-slate-100"
                    >
                        Entrar no Sistema
                    </Link>
                </div>
            </div>
        </header>

        <!-- banner central de apresentação do sistema -->
        <main class="flex-1 flex items-center justify-center p-6">
            <div class="max-w-3xl w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-8 md:p-12 text-center space-y-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-sm bg-[#006633]/10 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-[#006633] dark:text-emerald-400 mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        SIGEPEI • 2.0
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-zinc-300 max-w-xl mx-auto">
                        Plataforma de gestão integrada de Planos Educacionais Individualizados (PEI), acompanhamento do NAI, equipes de apoio e adaptações curriculares para discentes.
                    </p>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="w-full sm:w-auto px-6 py-2.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white font-semibold text-xs uppercase tracking-wider rounded-sm shadow-sm transition-colors"
                    >
                        Ir para o Painel de Controle
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="w-full sm:w-auto px-6 py-2.5 bg-[#006633] hover:bg-emerald-800 dark:bg-emerald-700 text-white font-semibold text-xs uppercase tracking-wider rounded-sm shadow-sm transition-colors"
                    >
                        Acessar com Credenciais
                    </Link>
                </div>
            </div>
        </main>

        <!-- rodapé institucional -->
        <footer class="bg-white dark:bg-zinc-800 border-t border-slate-200 dark:border-zinc-700 py-4 px-6 text-center text-xs text-slate-500 dark:text-zinc-400">
            SIGEPEI — Sistema de Gestão Educacional Inclusiva • Uso Institucional
        </footer>
    </div>
</template>
