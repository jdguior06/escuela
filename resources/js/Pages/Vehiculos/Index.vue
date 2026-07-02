<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    vehiculos: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const search = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('vehiculos.index'), { search: search.value }, { preserveState: true, replace: true });
};

const darDeBaja = (vehiculo) => {
    if (confirm(`¿Dar de baja el vehículo con placa "${vehiculo.placa}"?`)) {
        router.delete(route('vehiculos.destroy', vehiculo.id));
    }
};
</script>

<template>
    <Head title="Vehículos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Vehículos</h2>
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
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <form @submit.prevent="buscar" class="flex gap-2">
                            <TextInput type="text" placeholder="Buscar por placa, marca o modelo..." class="w-72" v-model="search" />
                            <PrimaryButton>Buscar</PrimaryButton>
                        </form>

                        <Link :href="route('vehiculos.create')">
                            <PrimaryButton type="button">Nuevo vehículo</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Placa</th>
                                <th class="py-2 pr-4">Marca</th>
                                <th class="py-2 pr-4">Modelo</th>
                                <th class="py-2 pr-4">Tipo</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vehiculo in vehiculos.data" :key="vehiculo.id" class="border-b">
                                <td class="py-2 pr-4">{{ vehiculo.placa }}</td>
                                <td class="py-2 pr-4">{{ vehiculo.marca }}</td>
                                <td class="py-2 pr-4">{{ vehiculo.modelo }}</td>
                                <td class="py-2 pr-4">{{ vehiculo.tipo_vehiculo?.nombre }}</td>
                                <td class="py-2 pr-4">{{ vehiculo.estado_vehiculo }}</td>
                                <td class="flex gap-2 py-2 pr-4">
                                    <Link :href="route('vehiculos.edit', vehiculo.id)" class="text-indigo-600 hover:underline">
                                        Editar
                                    </Link>
                                    <DangerButton
                                        v-if="vehiculo.estado_vehiculo !== 'inactivo'"
                                        type="button"
                                        @click="darDeBaja(vehiculo)"
                                    >
                                        Dar de baja
                                    </DangerButton>
                                </td>
                            </tr>
                            <tr v-if="vehiculos.data.length === 0">
                                <td colspan="6" class="py-4 text-center text-gray-500">No hay vehículos registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="vehiculos.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
