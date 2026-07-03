<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    menus: {
        type: Array,
        required: true,
    },
});

const page = usePage();

const gruposMenu = computed(() => {
    const grupos = new Map();

    for (const menu of props.menus) {
        const nombreGrupo = menu.grupo ?? 'General';

        if (!grupos.has(nombreGrupo)) {
            grupos.set(nombreGrupo, []);
        }

        grupos.get(nombreGrupo).push(menu);
    }

    return grupos;
});

function tieneMenu(rol, menuId) {
    return rol.menu_ids.includes(menuId);
}

function alCambiar(rol, menuId) {
    const menuIds = tieneMenu(rol, menuId)
        ? rol.menu_ids.filter((id) => id !== menuId)
        : [...rol.menu_ids, menuId];

    router.patch(route('roles.actualizarMenus', rol.id), { menu_ids: menuIds }, {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles — Matriz de acceso</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.status" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ page.props.flash.status }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <p class="mb-4 text-sm text-gray-500">
                        Marcá o desmarcá qué menús puede ver cada rol. Los cambios se guardan al instante.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Menú</th>
                                    <th v-for="rol in roles" :key="rol.id" class="px-2 py-2 text-center">
                                        {{ rol.nombre }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="[grupo, items] in gruposMenu" :key="grupo">
                                    <tr class="bg-gray-50">
                                        <td :colspan="1 + roles.length" class="py-1 pr-4 text-xs font-semibold uppercase text-gray-500">
                                            {{ grupo }}
                                        </td>
                                    </tr>
                                    <tr v-for="menu in items" :key="menu.id" class="border-b">
                                        <td class="py-2 pr-4">{{ menu.nombre }}</td>
                                        <td v-for="rol in roles" :key="rol.id" class="px-2 py-2 text-center">
                                            <Checkbox
                                                :checked="tieneMenu(rol, menu.id)"
                                                @update:checked="alCambiar(rol, menu.id)"
                                            />
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="roles.length === 0">
                                    <td :colspan="1 + roles.length" class="py-4 text-center text-gray-500">
                                        No hay roles registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
