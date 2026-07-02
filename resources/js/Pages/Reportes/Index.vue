<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { opcionesBase, escalasBase, paletaMarca } from '@/chartSetup';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';

const props = defineProps({
    cursosMasVendidos: {
        type: Array,
        required: true,
    },
    ingresosPorMes: {
        type: Array,
        required: true,
    },
    certificaciones: {
        type: Object,
        required: true,
    },
    recursosMasAccedidos: {
        type: Array,
        required: true,
    },
    rolesMasActivos: {
        type: Array,
        required: true,
    },
    pagosPorMetodo: {
        type: Array,
        required: true,
    },
    cursosPorTipoVehiculo: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const fechaDesde = ref(props.filters.fecha_desde ?? '');
const fechaHasta = ref(props.filters.fecha_hasta ?? '');

const filtrar = () => {
    router.get(
        route('reportes.index'),
        { fecha_desde: fechaDesde.value, fecha_hasta: fechaHasta.value },
        { preserveState: true, replace: true },
    );
};

const limpiar = () => {
    fechaDesde.value = '';
    fechaHasta.value = '';
    router.get(route('reportes.index'), {}, { preserveState: true, replace: true });
};

const imprimir = () => window.print();

const cursosData = computed(() => ({
    labels: props.cursosMasVendidos.map((c) => c.nombre),
    datasets: [{ label: 'Inscripciones', data: props.cursosMasVendidos.map((c) => c.total), backgroundColor: paletaMarca[0], borderRadius: 4 }],
}));

const ingresosData = computed(() => ({
    labels: props.ingresosPorMes.map((i) => i.mes),
    datasets: [
        {
            label: 'Ingresos (Bs.)',
            data: props.ingresosPorMes.map((i) => Number(i.total)),
            borderColor: paletaMarca[1],
            backgroundColor: `${paletaMarca[1]}33`,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: paletaMarca[1],
        },
    ],
}));

const reprobados = computed(() => Math.max(0, props.certificaciones.total - props.certificaciones.aprobados));
const certificacionesData = computed(() => ({
    labels: ['Aprobados', 'No aprobados'],
    datasets: [{ data: [props.certificaciones.aprobados, reprobados.value], backgroundColor: [paletaMarca[1], paletaMarca[2]] }],
}));

const recursosData = computed(() => ({
    labels: props.recursosMasAccedidos.map((r) => r.recurso),
    datasets: [{ label: 'Accesos', data: props.recursosMasAccedidos.map((r) => r.total), backgroundColor: paletaMarca[3], borderRadius: 4 }],
}));

const rolesData = computed(() => ({
    labels: props.rolesMasActivos.map((r) => r.nombre),
    datasets: [{ data: props.rolesMasActivos.map((r) => r.total), backgroundColor: paletaMarca }],
}));

const pagosPorMetodoData = computed(() => ({
    labels: props.pagosPorMetodo.map((p) => p.nombre),
    datasets: [{ data: props.pagosPorMetodo.map((p) => Number(p.total)), backgroundColor: paletaMarca }],
}));

const cursosPorTipoVehiculoData = computed(() => ({
    labels: props.cursosPorTipoVehiculo.map((c) => c.nombre),
    datasets: [{ data: props.cursosPorTipoVehiculo.map((c) => c.total), backgroundColor: paletaMarca }],
}));

const opcionesBarraHorizontal = opcionesBase({ indexAxis: 'y', plugins: { legend: { display: false } }, scales: escalasBase() });
const opcionesLinea = opcionesBase({ plugins: { legend: { display: false } }, scales: escalasBase() });
const opcionesDoughnut = opcionesBase({ plugins: { legend: { position: 'bottom' } } });
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Reportes y estadísticas</h2>
                <SecondaryButton type="button" class="print:hidden" @click="imprimir">Imprimir / Guardar PDF</SecondaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg print:hidden">
                    <form @submit.prevent="filtrar" class="flex flex-wrap items-end gap-4">
                        <div>
                            <InputLabel for="fecha_desde" value="Desde" />
                            <TextInput id="fecha_desde" type="date" class="mt-1" v-model="fechaDesde" />
                        </div>
                        <div>
                            <InputLabel for="fecha_hasta" value="Hasta" />
                            <TextInput id="fecha_hasta" type="date" class="mt-1" v-model="fechaHasta" />
                        </div>
                        <PrimaryButton>Filtrar</PrimaryButton>
                        <SecondaryButton type="button" @click="limpiar">Limpiar</SecondaryButton>
                    </form>
                    <p v-if="filters.fecha_desde || filters.fecha_hasta" class="mt-3 text-sm text-gray-500">
                        Mostrando datos
                        <template v-if="filters.fecha_desde"> desde {{ filters.fecha_desde }}</template>
                        <template v-if="filters.fecha_hasta"> hasta {{ filters.fecha_hasta }}</template>.
                    </p>
                </div>
                <p class="hidden text-sm text-gray-500 print:block">
                    <template v-if="filters.fecha_desde || filters.fecha_hasta">
                        Periodo:
                        <template v-if="filters.fecha_desde">desde {{ filters.fecha_desde }}</template>
                        <template v-if="filters.fecha_hasta"> hasta {{ filters.fecha_hasta }}</template>
                    </template>
                    <template v-else>Periodo: todos los datos registrados.</template>
                </p>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Cursos más vendidos</h3>
                        <div v-if="cursosMasVendidos.length" class="h-64">
                            <Bar :data="cursosData" :options="opcionesBarraHorizontal" />
                        </div>
                        <p v-else class="text-sm text-gray-500">No hay inscripciones registradas en el periodo.</p>
                    </div>

                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Ingresos por mes</h3>
                        <div v-if="ingresosPorMes.length" class="h-64">
                            <Line :data="ingresosData" :options="opcionesLinea" />
                        </div>
                        <p v-else class="text-sm text-gray-500">No hay pagos confirmados en el periodo.</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Tasa de aprobación</h3>
                        <div v-if="certificaciones.tasaAprobacion !== null" class="relative mx-auto h-56 max-w-xs">
                            <Doughnut :data="certificacionesData" :options="opcionesDoughnut" />
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-gray-800">{{ certificaciones.tasaAprobacion }}%</span>
                                <span class="text-xs text-gray-500">aprobación</span>
                            </div>
                        </div>
                        <p v-if="certificaciones.tasaAprobacion !== null" class="mt-3 text-center text-sm text-gray-500">
                            {{ certificaciones.aprobados }} aprobados de {{ certificaciones.total }} certificaciones en el periodo.
                        </p>
                        <p v-else class="text-sm text-gray-500">No hay certificaciones registradas en el periodo.</p>
                    </div>

                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Roles más activos</h3>
                        <div v-if="rolesMasActivos.length" class="mx-auto h-56 max-w-xs">
                            <Doughnut :data="rolesData" :options="opcionesDoughnut" />
                        </div>
                        <p v-else class="text-sm text-gray-500">Todavía no hay accesos registrados en el periodo.</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Pagos por método</h3>
                        <div v-if="pagosPorMetodo.length" class="mx-auto h-56 max-w-xs">
                            <Doughnut :data="pagosPorMetodoData" :options="opcionesDoughnut" />
                        </div>
                        <p v-else class="text-sm text-gray-500">No hay pagos confirmados en el periodo.</p>
                        <ul v-if="pagosPorMetodo.length" class="mt-3 space-y-1 text-sm text-gray-500">
                            <li v-for="metodo in pagosPorMetodo" :key="metodo.nombre">
                                {{ metodo.nombre }}: {{ metodo.cantidad }} pagos, {{ Number(metodo.total).toFixed(2) }} Bs.
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="mb-4 font-serif font-semibold text-gray-800">Cursos por tipo de vehículo</h3>
                        <div v-if="cursosPorTipoVehiculo.length" class="mx-auto h-56 max-w-xs">
                            <Doughnut :data="cursosPorTipoVehiculoData" :options="opcionesDoughnut" />
                        </div>
                        <p v-else class="text-sm text-gray-500">No hay inscripciones registradas en el periodo.</p>
                    </div>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="mb-4 font-serif font-semibold text-gray-800">Recursos más accedidos</h3>
                    <div v-if="recursosMasAccedidos.length" class="h-64">
                        <Bar :data="recursosData" :options="opcionesBarraHorizontal" />
                    </div>
                    <p v-else class="text-sm text-gray-500">Todavía no hay accesos registrados en el periodo.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
