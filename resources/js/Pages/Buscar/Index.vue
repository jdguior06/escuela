<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    q: {
        type: String,
        required: true,
    },
    resultados: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Buscar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Resultados para "{{ q }}"</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Cursos</h3>
                    <ul v-if="resultados.cursos.length" class="divide-y">
                        <li v-for="curso in resultados.cursos" :key="curso.id" class="py-2 text-sm">
                            {{ curso.tipo_curso?.nombre }} — {{ curso.fecha_inicio }} a {{ curso.fecha_fin }} ({{ curso.estado_curso }})
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">Sin resultados.</p>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Instructores</h3>
                    <ul v-if="resultados.instructores.length" class="divide-y">
                        <li v-for="instructor in resultados.instructores" :key="instructor.id" class="py-2 text-sm">
                            {{ instructor.nombre }} {{ instructor.apellido }} — {{ instructor.correo }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">Sin resultados.</p>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-2 font-semibold text-gray-800">Vehículos</h3>
                    <ul v-if="resultados.vehiculos.length" class="divide-y">
                        <li v-for="vehiculo in resultados.vehiculos" :key="vehiculo.id" class="py-2 text-sm">
                            {{ vehiculo.placa }} — {{ vehiculo.marca }} {{ vehiculo.modelo }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">Sin resultados.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
