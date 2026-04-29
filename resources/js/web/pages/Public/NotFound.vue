<script setup lang="ts">
import Button from 'primevue/button';
import { useSeo } from '@web/composables/useSeo';

useSeo({
    title: 'Page not found — ShipDesk',
    description: 'That page does not exist.',
    noindex: true,
});
</script>

<template>
    <section class="py-32 bg-white">
        <div class="container mx-auto px-6 text-center max-w-xl">
            <div class="text-7xl font-bold text-primary-500">404</div>
            <h1 class="mt-4 text-3xl font-bold text-surface-900">Page not found</h1>
            <p class="mt-3 text-surface-600">
                The page you're looking for doesn't exist, or may have moved.
            </p>
            <div class="mt-8 flex items-center justify-center gap-3">
                <router-link to="/"><Button label="Back to home" /></router-link>
                <router-link to="/contact"><Button label="Contact us" severity="secondary" outlined /></router-link>
            </div>
        </div>
    </section>
</template>
