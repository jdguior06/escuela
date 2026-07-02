<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="link.url === null"
                class="rounded-md border px-3 py-1 text-sm text-gray-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                preserve-state
                class="rounded-md border px-3 py-1 text-sm hover:bg-gray-100"
                :class="{ 'border-indigo-500 bg-indigo-50 text-indigo-600': link.active }"
                v-html="link.label"
            />
        </template>
    </div>
</template>
