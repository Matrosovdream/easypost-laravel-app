<script setup lang="ts">
import Tag from 'primevue/tag';

const props = defineProps<{ status: string }>();

function severity(): 'info' | 'success' | 'warn' | 'danger' | 'secondary' {
    switch (props.status) {
        case 'delivered':
        case 'purchased':
            return 'success';
        case 'pending_approval':
            return 'warn';
        case 'voided':
        case 'rate_declined':
        case 'rate_failed':
        case 'failed':
            return 'danger';
        case 'packed':
        case 'rated':
            return 'info';
        default:
            return 'secondary';
    }
}

function label(): string {
    return props.status.replace(/_/g, ' ');
}
</script>

<template>
    <Tag :value="label()" :severity="severity()" />
</template>
