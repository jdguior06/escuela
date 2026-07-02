<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import { useTema } from '@/composables/useTema';

const { estado } = useTema();

const temas = [
    { valor: 'ninos', etiqueta: 'Niños', color: '#ea580c' },
    { valor: 'jovenes', etiqueta: 'Jóvenes', color: '#6d28d9' },
    { valor: 'adultos', etiqueta: 'Adultos', color: '#8b5a2b' },
];

const modos = [
    { valor: 'auto', etiqueta: 'Auto' },
    { valor: 'claro', etiqueta: 'Claro' },
    { valor: 'oscuro', etiqueta: 'Oscuro' },
];

const tamanos = [
    { valor: 'normal', etiqueta: 'A' },
    { valor: 'grande', etiqueta: 'A+' },
    { valor: 'xgrande', etiqueta: 'A++' },
];

function botonActivoClase(activo) {
    return activo
        ? 'border-2 border-indigo-600 bg-indigo-50 font-semibold text-indigo-700'
        : 'border border-gray-300 text-gray-600 hover:bg-gray-50';
}
</script>

<template>
    <Dropdown align="right" width="auto" content-classes="w-80 bg-white p-5">
        <template #trigger>
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                aria-label="Preferencias de accesibilidad"
            >
                <span aria-hidden="true">⚙️</span> Accesibilidad
            </button>
        </template>

        <template #content>
            <div class="space-y-5">
                <section>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Tema</h3>
                    <div class="flex gap-3">
                        <button
                            v-for="tema in temas"
                            :key="tema.valor"
                            type="button"
                            class="flex flex-col items-center gap-1"
                            @click="estado.tema = tema.valor"
                        >
                            <span
                                class="block h-8 w-8 rounded-full ring-offset-2"
                                :style="{
                                    backgroundColor: tema.color,
                                    boxShadow: estado.tema === tema.valor ? `0 0 0 2px white, 0 0 0 4px ${tema.color}` : 'none',
                                }"
                            />
                            <span class="text-xs text-gray-600">{{ tema.etiqueta }}</span>
                        </button>
                    </div>
                </section>

                <hr class="border-gray-200" />

                <section>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Modo</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="modo in modos"
                            :key="modo.valor"
                            type="button"
                            class="rounded-md px-2 py-1.5 text-sm transition"
                            :class="botonActivoClase(estado.modo === modo.valor)"
                            @click="estado.modo = modo.valor"
                        >
                            {{ modo.etiqueta }}
                        </button>
                    </div>
                </section>

                <hr class="border-gray-200" />

                <section>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Tamaño de letra</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="tamano in tamanos"
                            :key="tamano.valor"
                            type="button"
                            class="rounded-md px-2 py-1.5 text-sm transition"
                            :class="botonActivoClase(estado.tamano === tamano.valor)"
                            @click="estado.tamano = tamano.valor"
                        >
                            {{ tamano.etiqueta }}
                        </button>
                    </div>
                </section>

                <hr class="border-gray-200" />

                <section>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Contraste</h3>
                    <button
                        type="button"
                        class="w-full rounded-md px-2 py-1.5 text-sm transition"
                        :class="botonActivoClase(estado.contraste === 'alto')"
                        @click="estado.contraste = estado.contraste === 'alto' ? 'normal' : 'alto'"
                    >
                        Alto contraste: {{ estado.contraste === 'alto' ? 'activado' : 'desactivado' }}
                    </button>
                </section>
            </div>
        </template>
    </Dropdown>
</template>
