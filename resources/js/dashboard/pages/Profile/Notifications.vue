<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ToggleSwitch from 'primevue/toggleswitch';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { profileApi } from '@dashboard/api/profile';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const prefs = ref<Record<string, boolean>>({});
const loading = ref(false);
const saving = ref(false);

const knownEvents: Array<{ key: string; label: string; description: string }> = [
    { key: 'email.shipment.delivered', label: 'Shipment delivered', description: 'Email when a shipment reaches its destination.' },
    { key: 'email.return.status', label: 'Return status', description: 'Email on return request approval/decline.' },
    { key: 'email.claim.status', label: 'Claim updates', description: 'Email on claim state transitions.' },
    { key: 'email.approval.requested', label: 'Approval requested', description: 'Email when a shipment needs your approval.' },
];

async function load(): Promise<void> {
    loading.value = true;
    try {
        prefs.value = { ...((await profileApi.notifications()).data) };
        for (const e of knownEvents) if (!(e.key in prefs.value)) prefs.value[e.key] = true;
    } finally { loading.value = false; }
}

async function save(): Promise<void> {
    saving.value = true;
    try {
        await profileApi.updateNotifications(prefs.value);
        toast.success('Preferences saved');
    } catch { toast.error('Could not save.'); }
    finally { saving.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Notification preferences" subtitle="Pick what we email you about." />

        <div class="card max-w-2xl">
            <div class="divide-y divide-surface-200">
                <div v-for="e in knownEvents" :key="e.key" class="py-4 flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="font-medium">{{ e.label }}</div>
                        <div class="text-sm text-surface-500">{{ e.description }}</div>
                    </div>
                    <ToggleSwitch v-model="prefs[e.key]" />
                </div>
            </div>
            <div class="flex justify-end pt-4">
                <Button label="Save preferences" :loading="saving" @click="save" />
            </div>
        </div>
    </div>
</template>
