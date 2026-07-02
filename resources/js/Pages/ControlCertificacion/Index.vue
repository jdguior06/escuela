<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    pendientes: {
        type: Array,
        required: true,
    },
    emitidas: {
        type: Array,
        required: true,
    },
});

const page = usePage();
</script>

<template>
    <Head title="Control y Certificación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Control y Certificación</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Pendientes de certificar</h3>
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Estudiante</th>
                                <th class="py-2 pr-4">Curso</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inscripcion in pendientes" :key="inscripcion.id" class="border-b">
                                <td class="py-2 pr-4">{{ inscripcion.estudiante?.nombre }} {{ inscripcion.estudiante?.apellido }}</td>
                                <td class="py-2 pr-4">{{ inscripcion.curso?.tipo_curso?.nombre }}</td>
                                <td class="py-2 pr-4">
                                    <Link :href="route('control-certificacion.create', inscripcion.id)" class="text-indigo-600 hover:underline">
                                        Certificar
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="pendientes.length === 0">
                                <td colspan="3" class="py-4 text-center text-gray-500">No hay inscripciones pendientes de certificar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Certificaciones emitidas</h3>
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Estudiante</th>
                                <th class="py-2 pr-4">Curso</th>
                                <th class="py-2 pr-4">Nota</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="certificacion in emitidas" :key="certificacion.id" class="border-b">
                                <td class="py-2 pr-4">
                                    {{ certificacion.inscripcion?.estudiante?.nombre }} {{ certificacion.inscripcion?.estudiante?.apellido }}
                                </td>
                                <td class="py-2 pr-4">{{ certificacion.inscripcion?.curso?.tipo_curso?.nombre }}</td>
                                <td class="py-2 pr-4">{{ certificacion.nota }}</td>
                                <td class="py-2 pr-4">{{ certificacion.estado_certificacion }}</td>
                                <td class="py-2 pr-4">{{ certificacion.fecha_emision }}</td>
                                <td class="py-2 pr-4">
                                    <a
                                        v-if="certificacion.estado_certificacion === 'aprobado'"
                                        :href="route('control-certificacion.pdf', certificacion.id)"
                                        class="text-indigo-600 hover:underline"
                                        target="_blank"
                                    >
                                        Descargar PDF
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="emitidas.length === 0">
                                <td colspan="6" class="py-4 text-center text-gray-500">Todavía no hay certificaciones emitidas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
