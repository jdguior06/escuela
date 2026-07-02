<script setup>
import AppFooter from '@/Components/AppFooter.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PanelAccesibilidad from '@/Components/PanelAccesibilidad.vue';
import { useTema } from '@/composables/useTema';
import { Link } from '@inertiajs/vue3';

useTema();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div class="fixed right-4 top-4">
            <PanelAccesibilidad />
        </div>

        <div class="flex flex-col items-center gap-2">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 ring-4" :style="{ '--tw-ring-color': 'var(--color-accent)' }" />
            </Link>
            <span class="font-serif text-base font-semibold text-[var(--color-text)]">Automóvil Club Boliviano</span>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>

        <AppFooter class="mt-6 w-full sm:max-w-md" />
    </div>
</template>
