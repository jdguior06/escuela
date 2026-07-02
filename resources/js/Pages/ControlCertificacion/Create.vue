<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    inscripcion: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    inscripcion_id: props.inscripcion.id,
    nota: '',
});

const submit = () => {
    form.post(route('control-certificacion.store'));
};
</script>

<template>
    <Head title="Certificar inscripción" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Certificar inscripción</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="mb-6 rounded-md bg-gray-50 p-4 text-sm">
                        <p><strong>Estudiante:</strong> {{ inscripcion.estudiante?.nombre }} {{ inscripcion.estudiante?.apellido }}</p>
                        <p><strong>C.I.:</strong> {{ inscripcion.estudiante?.nro_documento }}</p>
                        <p><strong>Curso:</strong> {{ inscripcion.curso?.tipo_curso?.nombre }}</p>
                        <p><strong>Período:</strong> {{ inscripcion.curso?.fecha_inicio }} a {{ inscripcion.curso?.fecha_fin }}</p>
                        <p><strong>Instructor:</strong> {{ inscripcion.curso?.instructor?.nombre }} {{ inscripcion.curso?.instructor?.apellido }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="nota" value="Nota (0-100)" />
                            <TextInput id="nota" type="number" min="0" max="100" step="0.1" class="mt-1 block w-full" v-model="form.nota" required autofocus />
                            <InputError class="mt-2" :message="form.errors.nota" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('control-certificacion.index')">
                                <SecondaryButton type="button">Cancelar</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">Registrar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
