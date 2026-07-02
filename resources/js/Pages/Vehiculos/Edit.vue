<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    vehiculo: {
        type: Object,
        required: true,
    },
    tiposVehiculo: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    placa: props.vehiculo.placa,
    marca: props.vehiculo.marca,
    modelo: props.vehiculo.modelo,
    estado_vehiculo: props.vehiculo.estado_vehiculo,
    fecha_mantenimiento: props.vehiculo.fecha_mantenimiento ?? '',
    tipo_vehiculo_id: props.vehiculo.tipo_vehiculo_id,
});

const submit = () => {
    form.put(route('vehiculos.update', props.vehiculo.id));
};
</script>

<template>
    <Head title="Editar vehículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar vehículo</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="placa" value="Placa" />
                            <TextInput id="placa" type="text" class="mt-1 block w-full" v-model="form.placa" required autofocus />
                            <InputError class="mt-2" :message="form.errors.placa" />
                        </div>

                        <div>
                            <InputLabel for="marca" value="Marca" />
                            <TextInput id="marca" type="text" class="mt-1 block w-full" v-model="form.marca" required />
                            <InputError class="mt-2" :message="form.errors.marca" />
                        </div>

                        <div>
                            <InputLabel for="modelo" value="Modelo" />
                            <TextInput id="modelo" type="text" class="mt-1 block w-full" v-model="form.modelo" required />
                            <InputError class="mt-2" :message="form.errors.modelo" />
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
                            <InputLabel for="estado_vehiculo" value="Estado" />
                            <SelectInput id="estado_vehiculo" class="mt-1 block w-full" v-model="form.estado_vehiculo">
                                <option value="disponible">Disponible</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="inactivo">Inactivo</option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.estado_vehiculo" />
                        </div>

                        <div>
                            <InputLabel for="fecha_mantenimiento" value="Fecha de mantenimiento" />
                            <TextInput id="fecha_mantenimiento" type="date" class="mt-1 block w-full" v-model="form.fecha_mantenimiento" />
                            <InputError class="mt-2" :message="form.errors.fecha_mantenimiento" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('vehiculos.index')">
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
