<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    tiposVehiculo: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    nombre: '',
    descripcion: '',
    precio: '',
    estado_curso: 'activo',
    duracion_horas: '',
    tipo_vehiculo_id: '',
});

const submit = () => {
    form.post(route('tipos-curso.store'));
};
</script>

<template>
    <Head title="Nuevo tipo de curso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo tipo de curso</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
                            <InputError class="mt-2" :message="form.errors.nombre" />
                        </div>

                        <div>
                            <InputLabel for="descripcion" value="Descripción" />
                            <TextInput id="descripcion" type="text" class="mt-1 block w-full" v-model="form.descripcion" />
                            <InputError class="mt-2" :message="form.errors.descripcion" />
                        </div>

                        <div>
                            <InputLabel for="tipo_vehiculo_id" value="Tipo de vehículo" />
                            <SelectInput id="tipo_vehiculo_id" class="mt-1 block w-full" v-model="form.tipo_vehiculo_id">
                                <option value="" disabled>Selecciona un tipo</option>
                                <option v-for="tipo in tiposVehiculo" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.tipo_vehiculo_id" />
                        </div>

                        <div>
                            <InputLabel for="precio" value="Precio" />
                            <TextInput id="precio" type="number" step="0.01" min="0" class="mt-1 block w-full" v-model="form.precio" required />
                            <InputError class="mt-2" :message="form.errors.precio" />
                        </div>

                        <div>
                            <InputLabel for="duracion_horas" value="Duración (horas)" />
                            <TextInput id="duracion_horas" type="number" min="1" class="mt-1 block w-full" v-model="form.duracion_horas" required />
                            <InputError class="mt-2" :message="form.errors.duracion_horas" />
                        </div>

                        <div>
                            <InputLabel for="estado_curso" value="Estado" />
                            <SelectInput id="estado_curso" class="mt-1 block w-full" v-model="form.estado_curso">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.estado_curso" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('tipos-curso.index')">
                                <SecondaryButton type="button">Cancelar</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
