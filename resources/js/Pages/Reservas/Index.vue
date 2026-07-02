<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    reservas: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const esPrivilegiado = computed(() => ['Secretaria', 'Propietario'].includes(page.props.auth.rol));

const cancelar = (reserva) => {
    if (confirm('¿Cancelar esta reserva?')) {
        router.delete(route('reservas.destroy', reserva.id));
    }
};
</script>

<template>
    <Head title="Reservas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Reservas</h2>
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
                        <Link :href="route('reservas.create')">
                            <PrimaryButton type="button">Nueva reserva</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Curso</th>
                                <th v-if="esPrivilegiado" class="py-2 pr-4">Estudiante</th>
                                <th class="py-2 pr-4">Vencimiento</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reserva in reservas.data" :key="reserva.id" class="border-b">
                                <td class="py-2 pr-4">
                                    {{ reserva.curso?.tipo_curso?.nombre }} ({{ reserva.curso?.fecha_inicio }} - {{ reserva.curso?.fecha_fin }})
                                </td>
                                <td v-if="esPrivilegiado" class="py-2 pr-4">{{ reserva.usuario?.nombre }} {{ reserva.usuario?.apellido }}</td>
                                <td class="py-2 pr-4">{{ reserva.fecha_vencimiento }}</td>
                                <td class="py-2 pr-4">{{ reserva.estado_reserva }}</td>
                                <td class="flex gap-2 py-2 pr-4">
                                    <template v-if="reserva.estado_reserva === 'pendiente'">
                                        <Link
                                            :href="route('inscripciones.create', { reserva_id: reserva.id })"
                                            class="text-indigo-600 hover:underline"
                                        >
                                            Inscribir
                                        </Link>
                                        <DangerButton type="button" @click="cancelar(reserva)">Cancelar</DangerButton>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="reservas.data.length === 0">
                                <td :colspan="esPrivilegiado ? 5 : 4" class="py-4 text-center text-gray-500">No hay reservas registradas.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="reservas.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
