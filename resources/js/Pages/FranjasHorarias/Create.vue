<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    hora_inicio: '',
    hora_fin: '',
});

const submit = () => {
    form.post(route('franjas-horarias.store'));
};
</script>

<template>
    <Head title="Nueva franja horaria" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nueva franja horaria</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="hora_inicio" value="Hora de inicio" />
                            <TextInput id="hora_inicio" type="time" class="mt-1 block w-full" v-model="form.hora_inicio" required autofocus />
                            <InputError class="mt-2" :message="form.errors.hora_inicio" />
                        </div>

                        <div>
                            <InputLabel for="hora_fin" value="Hora de fin" />
                            <TextInput id="hora_fin" type="time" class="mt-1 block w-full" v-model="form.hora_fin" required />
                            <InputError class="mt-2" :message="form.errors.hora_fin" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('franjas-horarias.index')">
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
