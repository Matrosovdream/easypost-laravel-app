<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import CommandPalette from '@dashboard/components/shell/CommandPalette.vue';

const palette = ref<InstanceType<typeof CommandPalette> | null>(null);
</script>

<template>
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="text-7xl font-bold text-primary-500">404</div>
        <h1 class="mt-4 text-3xl font-bold text-surface-900">Page not found</h1>
        <p class="mt-2 text-surface-600">Try the command palette — ⌘K — or go back home.</p>

        <div class="mt-8 flex items-center justify-center gap-3">
            <router-link to="/dashboard">
                <Button label="Back to home" />
            </router-link>
            <Button label="Open search (⌘K)" severity="secondary" outlined @click="palette?.open()" />
        </div>

        <CommandPalette ref="palette" />
    </div>
</template>
