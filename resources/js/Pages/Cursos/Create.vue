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
    instructores: {
        type: Array,
        required: true,
    },
    vehiculos: {
        type: Array,
        required: true,
    },
    tiposCurso: {
        type: Array,
        required: true,
    },
    franjasHorarias: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    fecha_inicio: '',
    fecha_fin: '',
    precio_final: '',
    instructor_id: '',
    vehiculo_id: '',
    tipo_curso_id: '',
    franja_horaria_id: '',
});

const submit = () => {
    form.post(route('cursos.store'));
};
</script>

<template>
    <Head title="Nuevo curso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo curso</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="fecha_inicio" value="Fecha de inicio" />
                            <TextInput id="fecha_inicio" type="date" class="mt-1 block w-full" v-model="form.fecha_inicio" required autofocus />
                            <InputError class="mt-2" :message="form.errors.fecha_inicio" />
                        </div>

                        <div>
                            <InputLabel for="fecha_fin" value="Fecha de fin" />
                            <TextInput id="fecha_fin" type="date" class="mt-1 block w-full" v-model="form.fecha_fin" required />
                            <InputError class="mt-2" :message="form.errors.fecha_fin" />
                        </div>

                        <div>
                            <InputLabel for="precio_final" value="Precio final" />
                            <TextInput id="precio_final" type="number" step="0.01" min="0" class="mt-1 block w-full" v-model="form.precio_final" required />
                            <InputError class="mt-2" :message="form.errors.precio_final" />
                        </div>

                        <div>
                            <InputLabel for="instructor_id" value="Instructor" />
                            <SelectInput id="instructor_id" class="mt-1 block w-full" v-model="form.instructor_id">
                                <option value="" disabled>Selecciona un instructor</option>
                                <option v-for="instructor in instructores" :key="instructor.id" :value="instructor.id">
                                    {{ instructor.nombre }} {{ instructor.apellido }}
                                </option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.instructor_id" />
                        </div>

                        <div>
                            <InputLabel for="vehiculo_id" value="Vehículo" />
                            <SelectInput id="vehiculo_id" class="mt-1 block w-full" v-model="form.vehiculo_id">
                                <option value="" disabled>Selecciona un vehículo</option>
                                <option v-for="vehiculo in vehiculos" :key="vehiculo.id" :value="vehiculo.id">
                                    {{ vehiculo.placa }} — {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                </option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.vehiculo_id" />
                        </div>

                        <div>
                            <InputLabel for="tipo_curso_id" value="Tipo de curso" />
                            <SelectInput id="tipo_curso_id" class="mt-1 block w-full" v-model="form.tipo_curso_id">
                                <option value="" disabled>Selecciona un tipo de curso</option>
                                <option v-for="tipoCurso in tiposCurso" :key="tipoCurso.id" :value="tipoCurso.id">{{ tipoCurso.nombre }}</option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.tipo_curso_id" />
                        </div>

                        <div>
                            <InputLabel for="franja_horaria_id" value="Franja horaria" />
                            <SelectInput id="franja_horaria_id" class="mt-1 block w-full" v-model="form.franja_horaria_id">
                                <option value="" disabled>Selecciona una franja horaria</option>
                                <option v-for="franja in franjasHorarias" :key="franja.id" :value="franja.id">
                                    {{ franja.hora_inicio }} - {{ franja.hora_fin }}
                                </option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.franja_horaria_id" />
                        </div>

                        <div class="col-span-full flex items-center justify-end gap-3">
                            <Link :href="route('cursos.index')">
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
