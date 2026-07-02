<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    cursos: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const eliminar = (curso) => {
    if (confirm(`¿Eliminar el curso del ${curso.fecha_inicio} al ${curso.fecha_fin}?`)) {
        router.delete(route('cursos.destroy', curso.id));
    }
};
</script>

<template>
    <Head title="Cursos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Cursos</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="mb-4 flex items-center justify-end">
                        <Link :href="route('cursos.create')">
                            <PrimaryButton type="button">Nuevo curso</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Fechas</th>
                                <th class="py-2 pr-4">Instructor</th>
                                <th class="py-2 pr-4">Vehículo</th>
                                <th class="py-2 pr-4">Tipo de curso</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="curso in cursos.data" :key="curso.id" class="border-b">
                                <td class="py-2 pr-4">{{ curso.fecha_inicio }} - {{ curso.fecha_fin }}</td>
                                <td class="py-2 pr-4">{{ curso.instructor?.nombre }} {{ curso.instructor?.apellido }}</td>
                                <td class="py-2 pr-4">{{ curso.vehiculo?.placa }}</td>
                                <td class="py-2 pr-4">{{ curso.tipo_curso?.nombre }}</td>
                                <td class="py-2 pr-4">{{ curso.estado_curso }}</td>
                                <td class="flex gap-2 py-2 pr-4">
                                    <Link :href="route('cursos.edit', curso.id)" class="text-indigo-600 hover:underline">
                                        Editar
                                    </Link>
                                    <DangerButton type="button" @click="eliminar(curso)">Eliminar</DangerButton>
                                </td>
                            </tr>
                            <tr v-if="cursos.data.length === 0">
                                <td colspan="6" class="py-4 text-center text-gray-500">No hay cursos registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="cursos.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
