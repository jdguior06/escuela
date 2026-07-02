<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PagoQr from '@/Components/PagoQr.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    inscripcion: {
        type: Object,
        required: true,
    },
    cuotas: {
        type: Array,
        required: true,
    },
    pagos: {
        type: Array,
        required: true,
    },
    metodosPago: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const qr = computed(() => page.props.flash?.qr);
const hayCuotasPendientes = computed(() => props.cuotas.some((c) => c.estado_cuota === 'pendiente'));

const form = useForm({
    inscripcion_id: props.inscripcion.id,
    metodo_id: props.metodosPago[0]?.id ?? '',
});

const pagar = () => {
    form.post(route('pagos.store'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pagos de la inscripción #{{ inscripcion.id }} — {{ inscripcion.curso?.tipo_curso?.nombre }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Plan de pago: {{ inscripcion.plan_pago?.nombre }}</h3>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Cuota</th>
                                <th class="py-2 pr-4">Monto</th>
                                <th class="py-2 pr-4">Vencimiento</th>
                                <th class="py-2 pr-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cuota in cuotas" :key="cuota.id" class="border-b">
                                <td class="py-2 pr-4">{{ cuota.nro_cuota }}</td>
                                <td class="py-2 pr-4">{{ cuota.monto }} Bs.</td>
                                <td class="py-2 pr-4">{{ cuota.fecha_vencimiento }}</td>
                                <td class="py-2 pr-4">{{ cuota.estado_cuota }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 class="mb-2 mt-6 font-semibold text-gray-800">Pagos registrados</h3>
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Cuota</th>
                                <th class="py-2 pr-4">Monto</th>
                                <th class="py-2 pr-4">Método</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pago in pagos" :key="pago.id" class="border-b">
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
                            <tr v-if="pagos.length === 0">
                                <td colspan="5" class="py-4 text-center text-gray-500">Todavía no hay pagos registrados.</td>
                            </tr>
                        </tbody>
                    </table>

                    <form v-if="hayCuotasPendientes" @submit.prevent="pagar" class="mt-6 flex items-end gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Método de pago</label>
                            <SelectInput class="mt-1" v-model="form.metodo_id">
                                <option v-for="metodo in metodosPago" :key="metodo.id" :value="metodo.id">
                                    {{ metodo.nombre }}
                                </option>
                            </SelectInput>
                        </div>
                        <PrimaryButton :disabled="form.processing">Pagar cuota</PrimaryButton>
                    </form>
                    <p v-else class="mt-6 text-sm text-gray-600">Todas las cuotas están pagadas.</p>

                    <PagoQr v-if="qr" :qr="qr" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
