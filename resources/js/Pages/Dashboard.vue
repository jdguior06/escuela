<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { opcionesBase, escalasBase, paletaMarca } from '@/chartSetup';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Doughnut, Line } from 'vue-chartjs';

const props = defineProps({
    rol: {
        type: String,
        default: null,
    },
    datos: {
        type: Object,
        required: true,
    },
});

const page = usePage();

function formatearHora(hora) {
    return hora ? hora.slice(0, 5) : '';
}

const etiquetasEstadoCurso = {
    disponible: 'Disponible',
    reservado: 'Reservado',
    inscrito: 'Inscrito',
    cancelado: 'Cancelado',
};

const etiquetasEstadoCuota = {
    pendiente: 'Pendientes',
    pagada: 'Pagadas',
};

const etiquetasEstadoCertificacion = {
    aprobado: 'Aprobadas',
    reprobado: 'No aprobadas',
};

function doughnutDesde(objeto, etiquetas) {
    const claves = Object.keys(objeto ?? {});

    return {
        labels: claves.map((clave) => etiquetas[clave] ?? clave),
        datasets: [{ data: claves.map((clave) => objeto[clave]), backgroundColor: paletaMarca }],
    };
}

const cursosPorEstadoData = computed(() => doughnutDesde(props.datos.cursosPorEstado, etiquetasEstadoCurso));
const cuotasPorEstadoData = computed(() => doughnutDesde(props.datos.cuotasPorEstado, etiquetasEstadoCuota));
const certificacionesPorEstadoData = computed(() => doughnutDesde(props.datos.certificacionesPorEstado, etiquetasEstadoCertificacion));

const ingresosData = computed(() => ({
    labels: (props.datos.ingresosUltimosMeses ?? []).map((i) => i.mes),
    datasets: [
        {
            label: 'Ingresos (Bs.)',
            data: (props.datos.ingresosUltimosMeses ?? []).map((i) => i.total),
            borderColor: paletaMarca[0],
            backgroundColor: `${paletaMarca[0]}33`,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: paletaMarca[0],
        },
    ],
}));

const opcionesDoughnut = opcionesBase({ plugins: { legend: { position: 'bottom' } } });
const opcionesLinea = opcionesBase({ plugins: { legend: { display: false } }, scales: escalasBase() });

function hayDatos(objeto) {
    return objeto && Object.values(objeto).some((v) => v > 0);
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="font-serif text-lg font-semibold text-gray-800">
                        Hola, {{ page.props.auth.user.nombre }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Este es el resumen de tu actividad en Auto Escuela Automóvil Club Boliviano.
                    </p>
                </div>

                <!-- Propietario -->
                <template v-if="rol === 'Propietario'">
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Usuarios activos</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.usuariosActivos }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Cursos activos</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.cursosActivos }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Inscripciones este mes</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.inscripcionesMes }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Ingresos este mes</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ Number(datos.ingresosMes).toFixed(2) }} Bs.</p>
                        </div>
                    </div>
                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="bg-white p-6 shadow sm:rounded-lg lg:col-span-2">
                            <h3 class="mb-4 font-serif font-semibold text-gray-800">Ingresos últimos 6 meses</h3>
                            <div class="h-56">
                                <Line :data="ingresosData" :options="opcionesLinea" />
                            </div>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="mb-4 font-serif font-semibold text-gray-800">Cursos por estado</h3>
                            <div v-if="hayDatos(datos.cursosPorEstado)" class="h-56">
                                <Doughnut :data="cursosPorEstadoData" :options="opcionesDoughnut" />
                            </div>
                            <p v-else class="text-sm text-gray-500">No hay cursos registrados todavía.</p>
                        </div>
                    </div>
                </template>

                <!-- Secretaria -->
                <template v-else-if="rol === 'Secretaria'">
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Reservas pendientes</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.reservasPendientes }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Inscripciones de hoy</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.inscripcionesHoy }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Cursos disponibles</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.cursosDisponibles }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Pagos pendientes</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.pagosPendientes }}</p>
                        </div>
                    </div>
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Cursos por estado</h3>
                        <div v-if="hayDatos(datos.cursosPorEstado)" class="mx-auto h-56 max-w-xs">
                            <Doughnut :data="cursosPorEstadoData" :options="opcionesDoughnut" />
                        </div>
                        <p v-else class="text-sm text-gray-500">No hay cursos registrados todavía.</p>
                    </div>
                </template>

                <!-- Instructor -->
                <div v-else-if="rol === 'Instructor'" class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Cursos asignados</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.cursosAsignados }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Certificaciones pendientes de emitir</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.certificacionesPendientes }}</p>
                        </div>
                    </div>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="mb-2 font-serif font-semibold text-gray-800">Próxima clase</h3>
                            <p v-if="datos.proximaClase" class="text-sm text-gray-600">
                                {{ datos.proximaClase.tipo_curso.nombre }} —
                                {{ formatearHora(datos.proximaClase.franja_horaria.hora_inicio) }}-{{ formatearHora(datos.proximaClase.franja_horaria.hora_fin) }}
                            </p>
                            <p v-else class="text-sm text-gray-500">No tienes clases asignadas por el momento.</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="mb-4 font-serif font-semibold text-gray-800">Mis certificaciones</h3>
                            <div v-if="hayDatos(datos.certificacionesPorEstado)" class="mx-auto h-48 max-w-xs">
                                <Doughnut :data="certificacionesPorEstadoData" :options="opcionesDoughnut" />
                            </div>
                            <p v-else class="text-sm text-gray-500">Todavía no emitiste certificaciones.</p>
                        </div>
                    </div>
                </div>

                <!-- Estudiante -->
                <div v-else-if="rol === 'Estudiante'" class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Inscripciones activas</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.inscripcionesActivas }}</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <p class="text-sm text-gray-500">Cuotas pendientes de pago</p>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ datos.cuotasPendientes }}</p>
                        </div>
                    </div>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="mb-2 font-serif font-semibold text-gray-800">Mi próximo curso</h3>
                            <p v-if="datos.proximaInscripcion" class="text-sm text-gray-600">
                                {{ datos.proximaInscripcion.curso.tipo_curso.nombre }} —
                                {{ formatearHora(datos.proximaInscripcion.curso.franja_horaria.hora_inicio) }}-{{ formatearHora(datos.proximaInscripcion.curso.franja_horaria.hora_fin) }}
                            </p>
                            <p v-else class="text-sm text-gray-500">No tienes inscripciones activas.</p>
                        </div>
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="mb-4 font-serif font-semibold text-gray-800">Mis cuotas</h3>
                            <div v-if="hayDatos(datos.cuotasPorEstado)" class="mx-auto h-48 max-w-xs">
                                <Doughnut :data="cuotasPorEstadoData" :options="opcionesDoughnut" />
                            </div>
                            <p v-else class="text-sm text-gray-500">Todavía no tienes cuotas generadas.</p>
                        </div>
                    </div>
                    <div v-if="datos.certificacionAprobada" class="bg-white p-6 shadow sm:rounded-lg">
                        <p class="text-sm font-medium text-green-700">✓ Tienes una certificación aprobada disponible para descargar.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
