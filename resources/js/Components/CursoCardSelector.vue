<script setup>
defineProps({
    cursos: {
        type: Array,
        required: true,
    },
});

const model = defineModel({ required: true });
</script>

<template>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <label
            v-for="curso in cursos"
            :key="curso.id"
            class="relative flex cursor-pointer flex-col rounded-lg border p-4 transition"
            :class="model == curso.id
                ? 'border-2 border-indigo-600 bg-indigo-50'
                : 'border border-gray-300 hover:border-indigo-300 hover:bg-gray-50'"
        >
            <input type="radio" :value="curso.id" v-model="model" class="sr-only" />

            <span class="font-semibold" :class="model == curso.id ? 'text-indigo-700' : 'text-gray-900'">
                {{ curso.tipo_curso?.nombre }}
            </span>

            <span class="mt-1 text-sm text-gray-600">
                {{ curso.fecha_inicio }} a {{ curso.fecha_fin }}
            </span>

            <span v-if="curso.franja_horaria" class="text-sm text-gray-600">
                {{ curso.franja_horaria.hora_inicio }} - {{ curso.franja_horaria.hora_fin }}
            </span>

            <span class="mt-2 text-base font-bold" :class="model == curso.id ? 'text-indigo-700' : 'text-indigo-600'">
                {{ curso.precio_final }} Bs.
            </span>
        </label>

        <p v-if="!cursos.length" class="text-sm text-gray-500">
            No hay cursos disponibles en este momento.
        </p>
    </div>
</template>
