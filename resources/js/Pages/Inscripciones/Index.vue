<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    inscripciones: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const esPrivilegiado = computed(() => ['Secretaria', 'Propietario'].includes(page.props.auth.rol));
</script>

<template>
    <Head title="Inscripciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Inscripciones</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="mb-4 flex items-center justify-end">
                        <Link :href="route('inscripciones.create')">
                            <PrimaryButton type="button">Nueva inscripción</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Curso</th>
                                <th v-if="esPrivilegiado" class="py-2 pr-4">Estudiante</th>
                                <th class="py-2 pr-4">Plan de pago</th>
                                <th class="py-2 pr-4">Monto total</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inscripcion in inscripciones.data" :key="inscripcion.id" class="border-b">
                                <td class="py-2 pr-4">{{ inscripcion.curso?.tipo_curso?.nombre }}</td>
                                <td v-if="esPrivilegiado" class="py-2 pr-4">
                                    {{ inscripcion.estudiante?.nombre }} {{ inscripcion.estudiante?.apellido }}
                                </td>
                                <td class="py-2 pr-4">{{ inscripcion.plan_pago?.nombre }}</td>
                                <td class="py-2 pr-4">{{ inscripcion.monto_total }}</td>
                                <td class="py-2 pr-4">{{ inscripcion.estado_inscripcion }}</td>
                                <td class="py-2 pr-4">{{ inscripcion.fecha_inscripcion }}</td>
                                <td class="py-2 pr-4">
                                    <Link :href="route('pagos.index', inscripcion.id)" class="text-indigo-600 hover:underline">
                                        Ver pagos
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="inscripciones.data.length === 0">
                                <td :colspan="esPrivilegiado ? 7 : 6" class="py-4 text-center text-gray-500">No hay inscripciones registradas.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="inscripciones.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
