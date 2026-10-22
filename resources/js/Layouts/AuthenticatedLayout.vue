<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isDarkMode = ref(false);
const mobileMenuOpen = ref(false);

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

const isRouteActive = (pattern) => {
    try {
        return route().current(pattern);
    } catch {
        return false;
    }
};
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-slate-100 dark:bg-zinc-900 text-slate-800 dark:text-zinc-100 font-sans transition-colors duration-150">
        <!-- barra superior mobile com botão de menu e identidade -->
        <div class="md:hidden flex items-center justify-between px-4 py-3 bg-[#006633] dark:bg-black text-white border-b border-emerald-800 dark:border-zinc-800">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-lg tracking-wider">SIGEPEI</span>
            </div>
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="p-1.5 rounded text-white hover:bg-white/10 focus:outline-none focus:ring-1 focus:ring-white"
                aria-label="Abrir menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- barra lateral de navegação -->
        <aside
            :class="[
                'w-full md:w-64 shrink-0 flex flex-col justify-between z-30 transition-all duration-200',
                'bg-[#006633] dark:bg-black text-white border-r border-emerald-800/40 dark:border-zinc-800',
                'md:sticky md:top-0 md:h-screen md:overflow-hidden',
                mobileMenuOpen ? 'block' : 'hidden md:flex'
            ]"
        >
            <!-- cabeçalho da sidebar com logo e nome do sistema -->
            <div class="px-5 py-5 border-b border-emerald-700/50 dark:border-zinc-800 shrink-0">
                <Link :href="route('dashboard')" class="block">
                    <div class="text-2xl font-bold tracking-wider text-white">
                        SIGEPEI
                    </div>
                    <div class="text-xs text-emerald-100/80 dark:text-zinc-400 font-medium tracking-wide uppercase mt-0.5">
                        Gestão de PEIs
                    </div>
                </Link>
            </div>

            <!-- links de navegação do menu -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto min-h-0">
                    <!-- painel geral / dashboard -->
                    <div>
                        <Link
                            :href="route('dashboard')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('dashboard')
                                    ? 'bg-white/20 text-white font-semibold shadow-inner'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Início
                        </Link>
                    </div>

                    <!-- cadastros gerais do sistema -->
                    <div class="space-y-1">
                        <div class="px-3 text-[11px] font-semibold tracking-wider text-emerald-200/80 dark:text-zinc-400 uppercase">
                            Setor Geral
                        </div>
                        <Link
                            :href="route('peis.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('peis.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PEIs
                        </Link>
                        <Link
                            :href="route('alunos.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('alunos.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Alunos
                        </Link>
                        <Link
                            :href="route('turmas.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('turmas.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Turmas
                        </Link>
                    </div>

                    <!-- administração e auditoria -->
                    <div v-if="$page.props.auth?.user?.is_admin || $page.props.auth?.user?.cargo === 'NAI'" class="space-y-1">
                        <div class="px-3 text-[11px] font-semibold tracking-wider text-emerald-200/80 dark:text-zinc-400 uppercase">
                            Administração
                        </div>
                        <Link
                            :href="route('deficiencias.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('deficiencias.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Deficiências
                        </Link>
                        <Link
                            :href="route('disciplinas.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('disciplinas.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Disciplinas
                        </Link>
                        <Link
                            v-if="$page.props.auth?.user?.is_admin"
                            :href="route('usuarios.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('usuarios.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Usuários
                        </Link>
                        <Link
                            :href="route('calendario.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('calendario.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Calendário
                        </Link>
                        <Link
                            :href="route('auditoria.index')"
                            :class="[
                                'flex items-center px-3 py-2 text-sm font-medium rounded-sm transition-colors',
                                isRouteActive('auditoria.*')
                                    ? 'bg-white/20 text-white font-semibold'
                                    : 'text-emerald-50 hover:bg-white/10 dark:text-zinc-200 dark:hover:bg-zinc-800'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Auditoria
                        </Link>
                    </div>
                </nav>

            <!-- rodapé da sidebar com dados do usuário, tema e encerramento de sessão -->
            <div class="p-4 border-t border-emerald-700/50 dark:border-zinc-800 bg-black/10 dark:bg-black/40 space-y-3 shrink-0">
                <!-- dados do usuário autenticado -->
                <div class="flex items-center space-x-3 px-1">
                    <div class="w-8 h-8 rounded-sm bg-white/20 dark:bg-zinc-800 flex items-center justify-center text-sm font-semibold text-white">
                        {{ ($page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-semibold text-white truncate">
                            {{ $page.props.auth?.user?.name || 'Usuário do Sistema' }}
                        </div>
                        <div class="text-[11px] text-emerald-200/80 dark:text-zinc-400 truncate">
                            {{ $page.props.auth?.user?.email || 'usuario@sigepei.local' }}
                        </div>
                    </div>
                </div>

                <!-- controles de ação (tema e encerramento de sessão) -->
                <div class="pt-2 border-t border-emerald-700/30 dark:border-zinc-800/80 flex flex-col space-y-1.5">
                    <!-- alternador de tema claro/escuro -->
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-medium text-emerald-100 hover:text-white hover:bg-white/10 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800 rounded-sm transition-colors"
                        title="Alternar entre tema claro e escuro"
                    >
                        <span class="flex items-center">
                            <!-- ícone de tema claro ou escuro -->
                            <svg v-if="isDarkMode" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <span>{{ isDarkMode ? 'Modo Claro' : 'Modo Escuro' }}</span>
                        </span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-white/10 dark:bg-zinc-800 text-white/90">
                            {{ isDarkMode ? 'Escuro' : 'Claro' }}
                        </span>
                    </button>

                    <!-- botão de encerramento de sessão -->
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full flex items-center px-2.5 py-1.5 text-xs font-medium text-red-200 hover:text-white hover:bg-red-600/30 rounded-sm transition-colors text-left"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Deslogar
                    </Link>
                </div>
            </div>
        </aside>

        <!-- área principal de renderização da página -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- cabeçalho opcional da página -->
            <header v-if="$slots.header" class="bg-white dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700 px-6 py-4">
                <div class="max-w-7xl mx-auto">
                    <slot name="header" />
                </div>
            </header>

            <!-- conteúdo principal da página -->
            <main class="flex-1 p-4 md:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
