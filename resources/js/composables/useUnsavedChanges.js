import { onBeforeUnmount, onMounted, unref } from 'vue';
import { router } from '@inertiajs/vue3';

export const DEFAULT_LEAVE_MESSAGE =
    'Tem certeza que deseja sair da página? Há alterações não salvas que serão perdidas.';

export const DEFAULT_DISCARD_MESSAGE =
    'Tem certeza que deseja sair? Há alterações não salvas que serão perdidas.';

// registro compartilhado para evitar múltiplos alertas quando há mais de um formulário montado na mesma página
const checkers = new Set();
let removeInertiaListener = null;

const evaluateSource = (source) => {
    if (!source) return false;
    if (Array.isArray(source)) {
        return source.some(evaluateSource);
    }
    if (typeof source === 'function') {
        return Boolean(source());
    }
    const unwrapped = unref(source);
    if (unwrapped && typeof unwrapped === 'object' && 'isDirty' in unwrapped) {
        return Boolean(unwrapped.isDirty);
    }
    return Boolean(unwrapped);
};

const getActiveMessage = () => {
    for (const entry of checkers) {
        if (entry.isDirty()) {
            return entry.message || DEFAULT_LEAVE_MESSAGE;
        }
    }
    return null;
};

const handleBeforeUnload = (event) => {
    if (getActiveMessage()) {
        event.preventDefault();
        event.returnValue = '';
    }
};

const attachGlobalListeners = () => {
    if (removeInertiaListener !== null) return;

    window.addEventListener('beforeunload', handleBeforeUnload);
    removeInertiaListener = router.on('before', (event) => {
        const message = getActiveMessage();
        if (!message) return;

        const visit = event.detail.visit;
        const isLogout = visit.url.pathname.endsWith('/logout');
        const isLeavingPage = visit.method === 'get' || isLogout;

        if (isLeavingPage) {
            return confirm(message);
        }
    });
};

const detachGlobalListenersIfEmpty = () => {
    if (checkers.size > 0 || removeInertiaListener === null) return;

    window.removeEventListener('beforeunload', handleBeforeUnload);
    removeInertiaListener();
    removeInertiaListener = null;
};

/**
 * protege a página contra saída acidental (fechar aba, recarregar ou navegar pelo inertia)
 * enquanto houver alterações não salvas em um ou mais formulários.
 */
export function useUnsavedChanges(source, options = {}) {
    const entry = {
        isDirty: () => evaluateSource(source),
        message: options.message || DEFAULT_LEAVE_MESSAGE,
    };

    onMounted(() => {
        checkers.add(entry);
        attachGlobalListeners();
    });

    onBeforeUnmount(() => {
        checkers.delete(entry);
        detachGlobalListenersIfEmpty();
    });
}

/**
 * confirma descarte de alterações ao fechar um modal de criação/edição.
 */
export function confirmDiscard(isDirty, message = DEFAULT_DISCARD_MESSAGE) {
    if (!isDirty) return true;
    return confirm(message);
}

