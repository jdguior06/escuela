<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CursoCardSelector from '@/Components/CursoCardSelector.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    reserva: {
        type: Object,
        default: null,
    },
    cursos: {
        type: Array,
        required: true,
    },
    estudiantes: {
        type: Array,
        required: true,
    },
    planesPago: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const esPrivilegiado = computed(() => ['Secretaria', 'Propietario'].includes(page.props.auth.rol));

const form = useForm({
    reserva_id: props.reserva?.id ?? '',
    curso_id: '',
    estudiante_id: '',
    plan_pago_id: '',
});

const submit = () => {
    form.post(route('inscripciones.store'));
};
</script>

<template>
    <Head title="Nueva inscripción" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nueva inscripción</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div v-if="reserva" class="rounded-md bg-gray-50 p-4 text-sm">
                            <p><strong>Curso:</strong> {{ reserva.curso?.tipo_curso?.nombre }}</p>
                            <p><strong>Fechas:</strong> {{ reserva.curso?.fecha_inicio }} a {{ reserva.curso?.fecha_fin }}</p>
                            <p><strong>Precio:</strong> {{ reserva.curso?.precio_final }} Bs.</p>
                        </div>

                        <div v-else>
                            <InputLabel for="curso_id" value="Curso disponible" />
                            <CursoCardSelector class="mt-2" :cursos="cursos" v-model="form.curso_id" />
                            <InputError class="mt-2" :message="form.errors.curso_id" />
                        </div>

                        <div v-if="esPrivilegiado && !reserva">
                            <InputLabel for="estudiante_id" value="Estudiante" />
                            <SelectInput id="estudiante_id" class="mt-1 block w-full" v-model="form.estudiante_id">
                                <option value="" disabled>Selecciona un estudiante</option>
                                <option v-for="estudiante in estudiantes" :key="estudiante.id" :value="estudiante.id">
                                    {{ estudiante.nombre }} {{ estudiante.apellido }}
                                </option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.estudiante_id" />
                        </div>

                        <div>
                            <InputLabel for="plan_pago_id" value="Plan de pago" />
                            <SelectInput id="plan_pago_id" class="mt-1 block w-full" v-model="form.plan_pago_id">
                                <option value="" disabled>Selecciona un plan de pago</option>
                                <option v-for="plan in planesPago" :key="plan.id" :value="plan.id">
                                    {{ plan.nombre }} ({{ plan.numero_cuotas }} cuota{{ plan.numero_cuotas > 1 ? 's' : '' }})
                                </option>
                            </SelectInput>
                            <InputError class="mt-2" :message="form.errors.plan_pago_id" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('inscripciones.index')">
                                <SecondaryButton type="button">Cancelar</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">Inscribir</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
