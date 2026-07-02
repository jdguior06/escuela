<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    franjasHorarias: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const eliminar = (franjaHoraria) => {
    if (confirm(`¿Eliminar la franja horaria ${franjaHoraria.hora_inicio}-${franjaHoraria.hora_fin}?`)) {
        router.delete(route('franjas-horarias.destroy', franjaHoraria.id));
    }
};
</script>

<template>
    <Head title="Franjas horarias" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Franjas horarias</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="mb-4 flex items-center justify-end">
                        <Link :href="route('franjas-horarias.create')">
                            <PrimaryButton type="button">Nueva franja horaria</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Hora inicio</th>
                                <th class="py-2 pr-4">Hora fin</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="franjaHoraria in franjasHorarias.data" :key="franjaHoraria.id" class="border-b">
                                <td class="py-2 pr-4">{{ franjaHoraria.hora_inicio }}</td>
                                <td class="py-2 pr-4">{{ franjaHoraria.hora_fin }}</td>
                                <td class="flex gap-2 py-2 pr-4">
                                    <Link :href="route('franjas-horarias.edit', franjaHoraria.id)" class="text-indigo-600 hover:underline">
                                        Editar
                                    </Link>
                                    <DangerButton type="button" @click="eliminar(franjaHoraria)">Eliminar</DangerButton>
                                </td>
                            </tr>
                            <tr v-if="franjasHorarias.data.length === 0">
                                <td colspan="3" class="py-4 text-center text-gray-500">No hay franjas horarias registradas.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="franjasHorarias.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
