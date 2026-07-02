<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    roles: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="rol in roles" :key="rol.id" class="border-b">
                                <td class="py-2 pr-4">{{ rol.nombre }}</td>
                                <td class="py-2 pr-4">{{ rol.descripcion }}</td>
                            </tr>
                            <tr v-if="roles.length === 0">
                                <td colspan="2" class="py-4 text-center text-gray-500">No hay roles registrados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
