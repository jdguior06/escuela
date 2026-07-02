<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pagos: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const estadoPago = ref(props.filters.estado_pago ?? '');

const filtrar = () => {
    router.get(route('pagos.listado'), { estado_pago: estadoPago.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Pagos</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="filtrar" class="mb-4 flex gap-2">
                        <SelectInput v-model="estadoPago" class="w-64">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="revertido">Revertido</option>
                            <option value="anulado">Anulado</option>
                        </SelectInput>
                        <PrimaryButton>Filtrar</PrimaryButton>
                    </form>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Estudiante</th>
                                <th class="py-2 pr-4">Curso</th>
                                <th class="py-2 pr-4">Cuota</th>
                                <th class="py-2 pr-4">Monto</th>
                                <th class="py-2 pr-4">Método</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pago in pagos.data" :key="pago.id" class="border-b">
                                <td class="py-2 pr-4">{{ pago.fecha }}</td>
                                <td class="py-2 pr-4">
                                    {{ pago.inscripcion?.estudiante ? `${pago.inscripcion.estudiante.nombre} ${pago.inscripcion.estudiante.apellido}` : '—' }}
                                </td>
                                <td class="py-2 pr-4">{{ pago.inscripcion?.curso?.tipo_curso?.nombre }}</td>
                                <td class="py-2 pr-4">{{ pago.nro_cuota }}</td>
                                <td class="py-2 pr-4">{{ pago.monto }} Bs.</td>
                                <td class="py-2 pr-4">{{ pago.metodo_pago?.nombre }}</td>
                                <td class="py-2 pr-4">{{ pago.estado_pago }}</td>
                                <td class="py-2 pr-4">
                                    <a
                                        v-if="pago.estado_pago === 'pagado'"
                                        :href="route('pagos.recibo', pago.id)"
                                        class="text-indigo-600 hover:underline"
                                        target="_blank"
                                    >
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="pagos.data.length === 0">
                                <td colspan="8" class="py-4 text-center text-gray-500">No hay pagos registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="pagos.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
