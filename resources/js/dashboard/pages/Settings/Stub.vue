<script setup lang="ts">
import PageHeader from '@dashboard/components/home/PageHeader.vue';

defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div>
        <PageHeader :title="title" :subtitle="description ?? 'Configuration page — coming soon.'" />
        <div class="card text-center py-16 text-surface-500">
            <i class="pi pi-cog text-5xl text-surface-300 mb-3"></i>
            <p>This settings page is not yet implemented. The backend and data model are ready; UI lands in a later iteration.</p>
        </div>
    </div>
</template>
