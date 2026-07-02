<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    tiposCurso: {
        type: Array,
        required: true,
    },
    tiposVehiculo: {
        type: Array,
        required: true,
    },
    franjasHorarias: {
        type: Array,
        required: true,
    },
});

const page = usePage();

function formatearHora(hora) {
    return hora ? hora.slice(0, 5) : '';
}
</script>

<template>
    <Head title="Auto Escuela Automóvil Club Boliviano" />

    <div class="min-h-screen bg-[#FBF3EA] text-[#3A2F22]">
        <!-- Header -->
        <header class="border-b border-[#E4D3BE] bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <ApplicationLogo class="h-12 w-12 ring-2 ring-[#8B5A2B]" />
                    <div class="font-serif leading-tight">
                        <p class="text-lg font-semibold">Automóvil Club Boliviano</p>
                        <p class="text-xs text-[#6B5C47]">Auto Escuela · Santa Cruz - Bolivia</p>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-md bg-[#8B5A2B] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6E4620]"
                    >
                        Ir al panel
                    </Link>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#3A2F22] transition hover:text-[#8B5A2B]"
                        >
                            Iniciar sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-md bg-[#8B5A2B] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6E4620]"
                        >
                            Registrarme
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <p class="font-serif text-sm font-semibold uppercase tracking-widest text-[#8B5A2B]">Desde 1789</p>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight sm:text-5xl">
                Aprende a manejar con la escuela<br class="hidden sm:block" />
                de más trayectoria de Santa Cruz
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-base text-[#6B5C47]">
                Cursos para automóvil y motocicleta, con instructores certificados, vehículos propios y planes de
                pago flexibles. Regístrate y elige el curso que se ajuste a tu horario.
            </p>
            <div class="mt-8">
                <Link
                    v-if="!page.props.auth.user"
                    :href="route('register')"
                    class="inline-block rounded-md bg-[#8B5A2B] px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-[#6E4620]"
                >
                    Regístrate para inscribirte
                </Link>
            </div>
        </section>

        <!-- Nuestros cursos -->
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <h2 class="font-serif text-2xl font-bold">Nuestros cursos</h2>
            <p class="mt-1 text-sm text-[#6B5C47]">Precios y duración vigentes.</p>

            <div v-if="tiposCurso.length" class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="tipoCurso in tiposCurso"
                    :key="tipoCurso.id"
                    class="rounded-lg border border-[#E4D3BE] bg-white p-6 shadow-sm"
                >
                    <h3 class="font-serif text-lg font-semibold">{{ tipoCurso.nombre }}</h3>
                    <p class="mt-1 text-sm text-[#6B5C47]">{{ tipoCurso.descripcion }}</p>
                    <dl class="mt-4 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-[#6B5C47]">Tipo de vehículo</dt>
                            <dd class="font-medium">{{ tipoCurso.tipo_vehiculo?.nombre }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-[#6B5C47]">Duración</dt>
                            <dd class="font-medium">{{ tipoCurso.duracion_horas }} horas</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-[#6B5C47]">Precio</dt>
                            <dd class="font-semibold text-[#8B5A2B]">{{ Number(tipoCurso.precio).toFixed(2) }} Bs.</dd>
                        </div>
                    </dl>
                </div>
            </div>
            <p v-else class="mt-6 text-sm text-[#6B5C47]">Por el momento no hay cursos publicados.</p>
        </section>

        <!-- Horarios disponibles -->
        <section class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="font-serif text-2xl font-bold">Horarios disponibles</h2>
                <p class="mt-1 text-sm text-[#6B5C47]">Elige la franja horaria que mejor se acomode a tu día.</p>

                <div v-if="franjasHorarias.length" class="mt-6 flex flex-wrap gap-3">
                    <span
                        v-for="franja in franjasHorarias"
                        :key="franja.id"
                        class="rounded-full border border-[#E4D3BE] bg-[#FBF3EA] px-4 py-2 text-sm font-medium"
                    >
                        {{ formatearHora(franja.hora_inicio) }} – {{ formatearHora(franja.hora_fin) }}
                    </span>
                </div>
                <p v-else class="mt-6 text-sm text-[#6B5C47]">Por el momento no hay franjas horarias configuradas.</p>
            </div>
        </section>

        <!-- Tipos de vehículo -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="font-serif text-2xl font-bold">Tipos de vehículo</h2>
            <div v-if="tiposVehiculo.length" class="mt-6 flex flex-wrap gap-3">
                <span
                    v-for="tipoVehiculo in tiposVehiculo"
                    :key="tipoVehiculo.id"
                    class="rounded-full bg-[#5F6B3F] px-4 py-2 text-sm font-medium text-white"
                >
                    {{ tipoVehiculo.nombre }}
                </span>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-[#E4D3BE] bg-[#1E171A] py-8 text-center text-sm text-[#C7AD9E]">
            <p class="font-serif font-semibold text-white">Automóvil Club Boliviano</p>
            <p class="mt-1">Av. Pedro Rivera # 600, 3er anillo int. Entre Av. Alemana y Beni</p>
            <p>Casilla 1687 · Telf.: +(591-3) 341-8000, 342-5013</p>
        </footer>
    </div>
</template>
