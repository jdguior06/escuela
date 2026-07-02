<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    nombre: user.nombre,
    apellido: user.apellido,
    correo: user.correo,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Información del perfil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualiza los datos de tu cuenta y tu correo electrónico.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="nombre" value="Nombre" />

                <TextInput
                    id="nombre"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nombre"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2" :message="form.errors.nombre" />
            </div>

            <div>
                <InputLabel for="apellido" value="Apellido" />

                <TextInput
                    id="apellido"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.apellido"
                    required
                    autocomplete="family-name"
                />

                <InputError class="mt-2" :message="form.errors.apellido" />
            </div>

            <div>
                <InputLabel for="correo" value="Correo electrónico" />

                <TextInput
                    id="correo"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.correo"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.correo" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
