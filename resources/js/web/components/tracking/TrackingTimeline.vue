<script setup lang="ts">
import Timeline from 'primevue/timeline';

type Event = {
    status: string;
    message: string;
    location?: string | null;
    occurred_at: string;
};

defineProps<{ events: Event[] }>();

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>

<template>
    <Timeline :value="events" class="w-full">
        <template #marker="slotProps">
            <div
                class="w-3 h-3 rounded-full"
                :class="slotProps.index === 0 ? 'bg-primary-500' : 'bg-surface-300'"
            ></div>
        </template>
        <template #content="slotProps">
            <div class="ml-2 pb-6">
                <div class="text-sm font-medium text-surface-900">{{ slotProps.item.message }}</div>
                <div class="text-xs text-surface-500">
                    {{ formatDate(slotProps.item.occurred_at) }}
                    <template v-if="slotProps.item.location"> · {{ slotProps.item.location }}</template>
                </div>
            </div>
        </template>
    </Timeline>
</template>
