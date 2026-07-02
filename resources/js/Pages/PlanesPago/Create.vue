<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    numero_cuotas: 1,
    estado: 'activo',
});

const submit = () => {
    form.post(route('planes-pago.store'));
};
</script>

<template>
    <Head title="Nuevo plan de pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo plan de pago</h2>
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
                            <InputLabel for="numero_cuotas" value="Número de cuotas" />
                            <TextInput id="numero_cuotas" type="number" min="1" class="mt-1 block w-full" v-model="form.numero_cuotas" required />
                            <InputError class="mt-2" :message="form.errors.numero_cuotas" />
                        </div>

                        <div>
                            <InputLabel for="estado" value="Estado" />
                            <SelectInput id="estado" class="mt-1 block w-full" v-model="form.estado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.estado" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('planes-pago.index')">
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
