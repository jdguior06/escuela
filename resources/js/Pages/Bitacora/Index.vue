<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    bitacora: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const tipoEvento = ref(props.filters.tipo_evento ?? '');

const filtrar = () => {
    router.get(route('bitacora.index'), { tipo_evento: tipoEvento.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Bitácora" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Bitácora</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="filtrar" class="mb-4 flex gap-2">
                        <SelectInput v-model="tipoEvento" class="w-64">
                            <option value="">Todos los eventos</option>
                            <option value="login_exitoso">Login exitoso</option>
                            <option value="login_fallido">Login fallido</option>
                            <option value="acceso_recurso">Acceso a recurso</option>
                        </SelectInput>
                        <PrimaryButton>Filtrar</PrimaryButton>
                    </form>

                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Usuario</th>
                                <th class="py-2 pr-4">Evento</th>
                                <th class="py-2 pr-4">Recurso</th>
                                <th class="py-2 pr-4">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="registro in bitacora.data" :key="registro.id" class="border-b">
                                <td class="py-2 pr-4">{{ registro.creado_en }}</td>
                                <td class="py-2 pr-4">
                                    {{ registro.usuario ? `${registro.usuario.nombre} ${registro.usuario.apellido}` : '—' }}
                                </td>
                                <td class="py-2 pr-4">{{ registro.tipo_evento }}</td>
                                <td class="py-2 pr-4">{{ registro.recurso }}</td>
                                <td class="py-2 pr-4">{{ registro.ip }}</td>
                            </tr>
                            <tr v-if="bitacora.data.length === 0">
                                <td colspan="5" class="py-4 text-center text-gray-500">No hay registros en la bitácora.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="bitacora.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
