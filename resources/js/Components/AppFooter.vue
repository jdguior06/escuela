<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <footer class="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-500">
        Visitas a esta página: {{ page.props.visitas }}
    </footer>
</template>
