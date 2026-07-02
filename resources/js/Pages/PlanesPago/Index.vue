<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    planesPago: {
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
    router.get(route('planes-pago.index'), { search: search.value }, { preserveState: true, replace: true });
};

const darDeBaja = (planPago) => {
    if (confirm(`¿Dar de baja el plan de pago "${planPago.nombre}"?`)) {
        router.delete(route('planes-pago.destroy', planPago.id));
    }
};
</script>

<template>
    <Head title="Planes de pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Planes de pago</h2>
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
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <form @submit.prevent="buscar" class="flex gap-2">
                            <TextInput type="text" placeholder="Buscar..." class="w-72" v-model="search" />
                            <PrimaryButton>Buscar</PrimaryButton>
                        </form>

                        <Link :href="route('planes-pago.create')">
                            <PrimaryButton type="button">Nuevo plan de pago</PrimaryButton>
                        </Link>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Nro. cuotas</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="planPago in planesPago.data" :key="planPago.id" class="border-b">
                                <td class="py-2 pr-4">{{ planPago.nombre }}</td>
                                <td class="py-2 pr-4">{{ planPago.numero_cuotas }}</td>
                                <td class="py-2 pr-4">{{ planPago.estado }}</td>
                                <td class="flex gap-2 py-2 pr-4">
                                    <Link :href="route('planes-pago.edit', planPago.id)" class="text-indigo-600 hover:underline">
                                        Editar
                                    </Link>
                                    <DangerButton
                                        v-if="planPago.estado !== 'inactivo'"
                                        type="button"
                                        @click="darDeBaja(planPago)"
                                    >
                                        Dar de baja
                                    </DangerButton>
                                </td>
                            </tr>
                            <tr v-if="planesPago.data.length === 0">
                                <td colspan="4" class="py-4 text-center text-gray-500">No hay planes de pago registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="planesPago.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
