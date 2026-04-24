<script setup lang="ts">
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import Popover from 'primevue/popover';
import Badge from 'primevue/badge';
import { useNotificationsStore } from '@dashboard/stores/notifications';

const notifications = useNotificationsStore();
const op = ref<InstanceType<typeof Popover> | null>(null);

const unread = computed(() => notifications.unreadCount);

function toggle(event: Event): void {
    op.value?.toggle(event);
}

function markRead(): void {
    notifications.markAllRead();
}

function timeAgo(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    const days = Math.floor(hrs / 24);
    return `${days}d ago`;
}
</script>

<template>
    <div class="relative">
        <Button type="button" text severity="secondary" aria-label="Notifications" @click="toggle">
            <template #default>
                <i class="pi pi-bell text-lg"></i>
                <Badge
                    v-if="unread > 0"
                    :value="unread"
                    severity="danger"
                    class="absolute -top-1 -right-1"
                />
            </template>
        </Button>

        <Popover ref="op" class="w-80">
            <template #default>
                <div class="flex items-center justify-between px-1 pb-2 border-b border-surface-200 mb-2">
                    <h3 class="font-semibold text-surface-900">Notifications</h3>
                    <button v-if="unread > 0" class="text-xs text-primary-600 hover:underline" @click="markRead">
                        Mark all read
                    </button>
                </div>

                <p v-if="notifications.items.length === 0" class="px-1 py-8 text-center text-sm text-surface-500">
                    You're all caught up.
                </p>

                <ul v-else class="space-y-1 max-h-80 overflow-y-auto">
                    <li
                        v-for="n in notifications.items"
                        :key="n.id"
                        class="px-2 py-2 rounded-md hover:bg-surface-50"
                        :class="!n.read ? 'bg-primary-50/40' : ''"
                    >
                        <div class="text-sm font-medium text-surface-900">{{ n.title }}</div>
                        <div v-if="n.body" class="text-xs text-surface-600 mt-0.5">{{ n.body }}</div>
                        <div class="text-xs text-surface-400 mt-1">{{ timeAgo(n.occurred_at) }}</div>
                    </li>
                </ul>
            </template>
        </Popover>
    </div>
</template>
