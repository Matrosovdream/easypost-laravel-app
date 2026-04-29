<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
}>();
</script>

<template>
    <header class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-surface-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-surface-600 mt-1">{{ subtitle }}</p>
        </div>
        <div class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
