<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { trackersApi, type TrackerDetail } from '@dashboard/api/data';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const t = ref<TrackerDetail | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    t.value = await trackersApi.show(Number(route.params.id));
}

async function remove(): Promise<void> {
    if (!t.value) return;
    if (!confirm('Delete this tracker? Webhooks will stop.')) return;
    busy.value = true;
    try {
        await trackersApi.delete(t.value.id);
        toast.success('Deleted');
        router.push('/dashboard/trackers');
    } catch { toast.error('Delete failed.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="t">
        <PageHeader :title="`Tracker ${t.tracking_code}`" :subtitle="t.carrier">
            <template #actions>
                <router-link to="/dashboard/trackers">
                    <Button label="Back" severity="secondary" text />
                </router-link>
                <a v-if="t.public_url" :href="t.public_url" target="_blank">
                    <Button label="Public page" severity="secondary" outlined icon="pi pi-external-link" />
                </a>
                <Button label="Delete" severity="danger" outlined :loading="busy" @click="remove" />
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Status</div>
                <div class="mt-2"><Tag :value="t.status" /></div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">ETA</div>
                <div class="mt-2 text-sm">{{ t.est_delivery_date ? new Date(t.est_delivery_date).toLocaleDateString() : '—' }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Shipment</div>
                <div class="mt-2 text-sm">
                    <router-link v-if="t.shipment_id" :to="`/dashboard/shipments/${t.shipment_id}`" class="text-primary-600 hover:underline">
                        #{{ t.shipment_id }}
                    </router-link>
                    <span v-else class="text-surface-500">Standalone</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold mb-3">Event history</h3>
            <Timeline v-if="t.events.length" :value="t.events">
                <template #content="slot">
                    <div class="pb-4">
                        <div class="text-sm font-medium">{{ slot.item.message }}</div>
                        <div class="text-xs text-surface-500">
                            {{ new Date(slot.item.event_datetime).toLocaleString() }}
                            <template v-if="slot.item.location">
                                · {{ [slot.item.location.city, slot.item.location.state].filter(Boolean).join(', ') }}
                            </template>
                        </div>
                    </div>
                </template>
            </Timeline>
            <p v-else class="text-sm text-surface-500">No events yet.</p>
        </div>
    </div>
</template>
