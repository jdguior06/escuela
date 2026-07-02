<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    metodosPago: {
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
    router.get(route('metodos-pago.index'), { search: search.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Métodos de pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Métodos de pago</h2>
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
                    <form @submit.prevent="buscar" class="mb-4 flex gap-2">
                        <TextInput type="text" placeholder="Buscar..." class="w-72" v-model="search" />
                        <PrimaryButton>Buscar</PrimaryButton>
                    </form>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="metodoPago in metodosPago.data" :key="metodoPago.id" class="border-b">
                                <td class="py-2 pr-4">{{ metodoPago.nombre }}</td>
                                <td class="py-2 pr-4">{{ metodoPago.descripcion }}</td>
                            </tr>
                            <tr v-if="metodosPago.data.length === 0">
                                <td colspan="2" class="py-4 text-center text-gray-500">No hay métodos de pago registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="metodosPago.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
