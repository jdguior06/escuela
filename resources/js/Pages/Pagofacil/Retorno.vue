<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    pago: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <Head title="Resultado del pago" />

    <div class="mx-auto mt-16 max-w-lg text-center">
        <template v-if="pago?.estado_pago === 'pagado'">
            <h1 class="text-2xl font-bold text-green-600">¡Pago exitoso!</h1>
            <p class="mt-2 text-gray-600">Tu pedido #{{ pago.nro_pedido }} fue procesado correctamente.</p>
        </template>
        <template v-else-if="pago">
            <h1 class="text-2xl font-bold text-yellow-600">Pago en proceso</h1>
            <p class="mt-2 text-gray-600">Tu pago está siendo verificado. Te notificaremos cuando se confirme.</p>
        </template>
        <template v-else>
            <h1 class="text-2xl font-bold text-gray-600">Pedido no encontrado</h1>
        </template>
    </div>
</template>
