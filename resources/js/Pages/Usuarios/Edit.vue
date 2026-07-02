<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UsuarioForm from './Partials/UsuarioForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    usuario: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    nombre: props.usuario.nombre,
    apellido: props.usuario.apellido,
    nro_documento: props.usuario.nro_documento,
    correo: props.usuario.correo,
    telefono: props.usuario.telefono,
    direccion: props.usuario.direccion,
    fecha_nacimiento: props.usuario.fecha_nacimiento,
    genero: props.usuario.genero ?? '',
    estado_usuario: props.usuario.estado_usuario,
    rol_id: props.usuario.rol_id,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('usuarios.update', props.usuario.id));
};
</script>

<template>
    <Head title="Editar usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar usuario</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <UsuarioForm :form="form" :roles="roles" :is-edit="true" />

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
