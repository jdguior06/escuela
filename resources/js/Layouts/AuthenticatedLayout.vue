<script setup>
import { computed, ref } from 'vue';
import AppFooter from '@/Components/AppFooter.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import PanelAccesibilidad from '@/Components/PanelAccesibilidad.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTema } from '@/composables/useTema';
import { Link, router, usePage } from '@inertiajs/vue3';

useTema();

const showingNavigationDropdown = ref(false);
const page = usePage();
const menu = page.props.menu ?? [];

// Agrupa los ítems de menú por `grupo` (Fase de rediseño: el menú del Propietario
// tenía 15 enlaces planos). Cada ítem sin grupo, o el único de su grupo para el
// rol actual, se muestra como enlace directo; el resto se agrupa en un Dropdown.
const gruposMenu = computed(() => {
    const orden = [];
    const porClave = new Map();

    for (const item of menu) {
        const clave = item.grupo ?? `__item_${item.id}`;
        if (!porClave.has(clave)) {
            porClave.set(clave, { grupo: item.grupo, items: [] });
            orden.push(clave);
        }
        porClave.get(clave).items.push(item);
    }

    return orden.map((clave) => porClave.get(clave));
});

const busqueda = ref('');
let debounceTimer = null;

const buscar = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        if (!busqueda.value) return;

        if (page.url.startsWith('/buscar')) {
            router.get(route('buscar'), { q: busqueda.value }, { preserveState: true, only: ['resultados', 'q'] });
        } else {
            router.get(route('buscar'), { q: busqueda.value });
        }
    }, 300);
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white print:hidden"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center gap-2">
                                <Link :href="route('dashboard')" class="flex items-center gap-2">
                                    <ApplicationLogo class="h-10 w-10 ring-2" :style="{ '--tw-ring-color': 'var(--color-accent)' }" />
                                    <span class="hidden font-serif text-sm font-semibold leading-tight text-[var(--color-text)] md:block">
                                        Automóvil Club<br />Boliviano
                                    </span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden sm:-my-px sm:ms-10 sm:flex sm:items-center sm:gap-6"
                            >
                                <template v-for="grupo in gruposMenu" :key="grupo.grupo ?? grupo.items[0].id">
                                    <NavLink
                                        v-if="grupo.items.length === 1"
                                        :href="grupo.items[0].ruta"
                                        :active="$page.url === grupo.items[0].ruta"
                                    >
                                        {{ grupo.items[0].nombre }}
                                    </NavLink>

                                    <Dropdown v-else align="left" width="auto">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1 border-b-2 py-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="grupo.items.some((i) => $page.url === i.ruta)
                                                    ? 'border-[var(--color-accent)] text-gray-900'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                            >
                                                {{ grupo.grupo }}
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>

                                        <template #content>
                                            <DropdownLink v-for="item in grupo.items" :key="item.id" :href="item.ruta">
                                                {{ item.nombre }}
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-4">
                            <TextInput
                                v-model="busqueda"
                                type="search"
                                placeholder="Buscar..."
                                class="w-56"
                                @input="buscar"
                            />

                            <!-- Panel de accesibilidad -->
                            <PanelAccesibilidad />

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <template v-for="grupo in gruposMenu" :key="grupo.grupo ?? grupo.items[0].id">
                            <div v-if="grupo.items.length > 1" class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ grupo.grupo }}
                            </div>
                            <ResponsiveNavLink
                                v-for="item in grupo.items"
                                :key="item.id"
                                :href="item.ruta"
                                :active="$page.url === item.ruta"
                            >
                                {{ item.nombre }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.correo }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white font-serif shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <div class="print:hidden">
                <AppFooter />
            </div>
        </div>
    </div>
</template>
