<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import TrackingStepper from '@web/components/tracking/TrackingStepper.vue';
import TrackingTimeline from '@web/components/tracking/TrackingTimeline.vue';
import { useSeo } from '@web/composables/useSeo';
import axios from 'axios';

type TrackerEvent = {
    status: string;
    message: string;
    location?: string | null;
    occurred_at: string;
};

type TrackerPayload = {
    code: string;
    carrier: string;
    status: 'pre_transit' | 'in_transit' | 'out_for_delivery' | 'delivered' | 'unknown';
    status_label: string;
    estimated_delivery_date?: string | null;
    tenant: { name: string; logo_url?: string | null; brand_color?: string | null };
    events: TrackerEvent[];
};

const route = useRoute();
const code = computed(() => String(route.params.code ?? ''));
const tracker = ref<TrackerPayload | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
let pollId: number | null = null;

useSeo({
    title: `Tracking ${code.value} — ShipDesk`,
    description: 'Follow your package in real time.',
    noindex: true,
});

async function fetchTracker() {
    try {
        const res = await axios.get<TrackerPayload>(`/rest/public/trackers/${code.value}`, {
            withCredentials: false,
            headers: { Accept: 'application/json' },
        });
        tracker.value = res.data;
        error.value = null;
    } catch (e: unknown) {
        const err = e as { response?: { status: number } };
        if (err.response?.status === 404) error.value = 'Tracking code not found.';
        else if (err.response?.status === 429) error.value = 'Too many requests. Try again in a minute.';
        else error.value = 'Could not load tracking info.';
    } finally {
        loading.value = false;
    }
}

function formatEta(iso: string | null | undefined): string {
    if (!iso) return 'TBD';
    return new Date(iso).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
}

onMounted(() => {
    fetchTracker();
    pollId = window.setInterval(fetchTracker, 60_000);
});

onUnmounted(() => {
    if (pollId !== null) window.clearInterval(pollId);
});
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div v-if="loading" class="flex justify-center py-16">
            <ProgressSpinner />
        </div>

        <Message v-else-if="error" severity="error" :closable="false">{{ error }}</Message>

        <div v-else-if="tracker" class="space-y-6">
            <div class="bg-white rounded-xl border border-surface-200 p-6">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-md flex items-center justify-center text-white font-bold"
                        :style="{ backgroundColor: tracker.tenant.brand_color || '#3b82f6' }"
                    >
                        <span v-if="!tracker.tenant.logo_url">{{ tracker.tenant.name.slice(0, 1) }}</span>
                        <img v-else :src="tracker.tenant.logo_url" :alt="tracker.tenant.name" class="w-full h-full object-contain rounded-md" />
                    </div>
                    <div>
                        <div class="text-sm text-surface-500">Shipment by</div>
                        <div class="font-semibold text-surface-900">{{ tracker.tenant.name }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-surface-200 p-6">
                <div class="text-sm text-surface-500">Tracking code</div>
                <div class="font-mono text-lg font-semibold text-surface-900">{{ tracker.code }}</div>
                <div class="mt-2 text-sm text-surface-600">
                    Carrier: <span class="font-medium">{{ tracker.carrier }}</span>
                </div>
                <div class="mt-1 text-sm text-surface-600">
                    Estimated delivery: <span class="font-medium">{{ formatEta(tracker.estimated_delivery_date) }}</span>
                </div>

                <div class="mt-6">
                    <TrackingStepper :status="tracker.status" />
                </div>

                <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm font-medium">
                    <i class="pi pi-circle-fill text-xs"></i>
                    {{ tracker.status_label }}
                </div>
            </div>

            <div class="bg-white rounded-xl border border-surface-200 p-6">
                <h2 class="font-semibold text-surface-900 mb-4">Event history</h2>
                <TrackingTimeline :events="tracker.events" />
                <p v-if="tracker.events.length === 0" class="text-sm text-surface-500">No events yet.</p>
            </div>
        </div>
    </div>
</template>
