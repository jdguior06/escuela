<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    qr: {
        type: Object,
        required: true,
    },
});

const estado = ref('pendiente');
let intervalo;

async function consultarEstado() {
    const { data } = await axios.get(`/pagos/${props.qr.pago_id}/estado`);
    estado.value = data.estado_pago;
    if (estado.value !== 'pendiente') clearInterval(intervalo);
}

onMounted(() => {
    intervalo = setInterval(consultarEstado, 4000);
});
onUnmounted(() => clearInterval(intervalo));
</script>

<template>
    <div class="mt-6 rounded-md border p-4 text-center">
        <img :src="`data:image/png;base64,${qr.base64}`" alt="QR de pago" class="mx-auto max-w-xs" />
        <p v-if="estado === 'pendiente'" class="mt-2 text-sm text-gray-600">Esperando confirmación del banco...</p>
        <p v-else-if="estado === 'pagado'" class="mt-2 text-sm font-medium text-green-600">¡Pago confirmado!</p>
        <p v-else class="mt-2 text-sm font-medium text-red-600">Pago {{ estado }}.</p>
    </div>
</template>
