<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UsuarioForm from './Partials/UsuarioForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    nombre: '',
    apellido: '',
    nro_documento: '',
    correo: '',
    telefono: '',
    direccion: '',
    fecha_nacimiento: '',
    genero: '',
    estado_usuario: 'activo',
    rol_id: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('usuarios.store'));
};
</script>

<template>
    <Head title="Nuevo usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo usuario</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <UsuarioForm :form="form" :roles="roles" />

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <Link :href="route('usuarios.index')">
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
