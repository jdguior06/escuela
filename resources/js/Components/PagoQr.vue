<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    qr: {
        type: Object,
        required: true,
    },
});

const estado = ref('pendiente');
const modalCerrado = ref(false);
let intervalo;

async function consultarEstado() {
    const { data } = await axios.get(`/pagos/${props.qr.pago_id}/estado`);
    estado.value = data.estado_pago;

    if (estado.value !== 'pendiente') {
        clearInterval(intervalo);
        router.reload({ only: ['pagos', 'cuotas'] });
    }
}

function alVolverVisible() {
    if (document.visibilityState === 'visible') {
        consultarEstado();
    }
}

onMounted(() => {
    intervalo = setInterval(consultarEstado, 4000);
    document.addEventListener('visibilitychange', alVolverVisible);
});
onUnmounted(() => {
    clearInterval(intervalo);
    document.removeEventListener('visibilitychange', alVolverVisible);
});
</script>

<template>
    <div class="mt-6 rounded-md border p-4 text-center">
        <img :src="`data:image/png;base64,${qr.base64}`" alt="QR de pago" class="mx-auto max-w-xs" />
        <p v-if="estado === 'pendiente'" class="mt-2 text-sm text-gray-600">Esperando confirmación del banco...</p>
        <p v-else-if="estado === 'pagado'" class="mt-2 text-sm font-medium text-green-600">¡Pago confirmado!</p>
        <p v-else class="mt-2 text-sm font-medium text-red-600">Pago {{ estado }}.</p>
    </div>

    <Modal :show="estado === 'pagado' && !modalCerrado" @close="modalCerrado = true">
        <div class="p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                <span class="text-2xl text-green-600" aria-hidden="true">✓</span>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">¡Pago realizado con éxito!</h2>
            <p class="mt-2 text-sm text-gray-600">Tu pago quedó registrado y confirmado.</p>

            <div class="mt-6 flex justify-center gap-3">
                <SecondaryButton type="button" @click="modalCerrado = true">Cerrar</SecondaryButton>
                <a :href="route('pagos.recibo', qr.pago_id)" target="_blank">
                    <PrimaryButton type="button">Descargar comprobante</PrimaryButton>
                </a>
            </div>
        </div>
    </Modal>
</template>
