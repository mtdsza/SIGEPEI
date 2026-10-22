<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Meu Perfil" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Meu Perfil de Usuário
                </h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                    Gerencie suas informações pessoais e credenciais de acesso ao sistema
                </p>
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-sm rounded-sm p-6">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
