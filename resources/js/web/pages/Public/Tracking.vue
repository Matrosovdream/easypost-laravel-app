<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProgressSpinner from 'primevue/progressspinner';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
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

type ErrorKind = 'not_found' | 'rate_limited' | 'generic';

const route = useRoute();
const router = useRouter();
const code = computed(() => String(route.params.code ?? '').trim());

const tracker = ref<TrackerPayload | null>(null);
const loading = ref(true);
const errorKind = ref<ErrorKind | null>(null);
const errorMessage = ref<string | null>(null);
const searchInput = ref(code.value);
const searching = ref(false);
let pollId: number | null = null;

useSeo({
    title: code.value ? `Tracking ${code.value} — ShipDesk` : 'Track a shipment — ShipDesk',
    description: 'Follow your package in real time.',
    noindex: true,
});

async function fetchTracker() {
    if (!code.value) {
        loading.value = false;
        tracker.value = null;
        errorKind.value = null;
        return;
    }
    try {
        const res = await axios.get<TrackerPayload>(`/rest/public/trackers/${encodeURIComponent(code.value)}`, {
            withCredentials: false,
            headers: { Accept: 'application/json' },
        });
        tracker.value = res.data;
        errorKind.value = null;
        errorMessage.value = null;
    } catch (e: unknown) {
        tracker.value = null;
        const err = e as { response?: { status: number } };
        if (err.response?.status === 404) {
            errorKind.value = 'not_found';
            errorMessage.value = "We couldn't find that tracking code.";
        } else if (err.response?.status === 429) {
            errorKind.value = 'rate_limited';
            errorMessage.value = 'Too many requests. Try again in a minute.';
        } else {
            errorKind.value = 'generic';
            errorMessage.value = 'Could not load tracking info. Please try again.';
        }
    } finally {
        loading.value = false;
        searching.value = false;
    }
}

function formatEta(iso: string | null | undefined): string {
    if (!iso) return 'TBD';
    return new Date(iso).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
}

function submitSearch() {
    const next = searchInput.value.trim();
    if (!next || next === code.value) return;
    searching.value = true;
    router.push(`/track/${encodeURIComponent(next)}`);
}

function startPolling() {
    if (pollId !== null) window.clearInterval(pollId);
    pollId = window.setInterval(fetchTracker, 60_000);
}

onMounted(() => {
    fetchTracker();
    startPolling();
});

onUnmounted(() => {
    if (pollId !== null) window.clearInterval(pollId);
});

watch(
    () => code.value,
    (next) => {
        searchInput.value = next;
        loading.value = true;
        tracker.value = null;
        errorKind.value = null;
        fetchTracker();
        startPolling();
    },
);
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-6">
        <header class="text-center">
            <h1 class="text-2xl font-bold text-surface-900">Track your shipment</h1>
            <p class="mt-1 text-sm text-surface-600">
                Enter a tracking code to see real-time delivery progress.
            </p>
        </header>

        <form
            class="bg-white rounded-xl border border-surface-200 p-4 flex flex-col sm:flex-row gap-3"
            @submit.prevent="submitSearch"
        >
            <div class="relative flex-1">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-surface-400"></i>
                <InputText
                    v-model="searchInput"
                    placeholder="e.g. 1Z999AA10123456784"
                    class="w-full pl-9"
                    aria-label="Tracking code"
                    autocomplete="off"
                    spellcheck="false"
                />
            </div>
            <Button
                type="submit"
                label="Track"
                icon="pi pi-arrow-right"
                icon-pos="right"
                :loading="searching"
                :disabled="!searchInput.trim() || searchInput.trim() === code"
            />
        </form>

        <div v-if="loading" class="flex flex-col items-center py-16">
            <ProgressSpinner />
            <p class="mt-4 text-sm text-surface-500">Looking up <span class="font-mono">{{ code }}</span>…</p>
        </div>

        <template v-else-if="errorKind === 'not_found'">
            <div class="bg-white rounded-xl border border-surface-200 p-8 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="pi pi-exclamation-circle text-2xl"></i>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-surface-900">No tracking results</h2>
                <p class="mt-2 text-surface-600">
                    We couldn't find a shipment for
                    <span class="font-mono font-semibold text-surface-900 break-all">{{ code }}</span>.
                </p>

                <div class="mt-6 text-left bg-surface-50 border border-surface-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-surface-900">A few things to check</h3>
                    <ul class="mt-2 space-y-2 text-sm text-surface-600">
                        <li class="flex gap-2">
                            <i class="pi pi-check text-primary-500 mt-0.5"></i>
                            <span>Double-check the code for typos — extra spaces or zeros vs. the letter O.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="pi pi-check text-primary-500 mt-0.5"></i>
                            <span>If the label was just printed, it can take a few hours for the carrier to register the first scan.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="pi pi-check text-primary-500 mt-0.5"></i>
                            <span>Some carriers only expose tracking once the package has been picked up.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="mailto:help@shipdesk.local">
                        <Button label="Contact support" icon="pi pi-envelope" severity="secondary" outlined />
                    </a>
                    <router-link to="/">
                        <Button label="Back to home" icon="pi pi-home" text />
                    </router-link>
                </div>
            </div>
        </template>

        <Message v-else-if="errorKind" severity="error" :closable="false">
            {{ errorMessage }}
        </Message>

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
                <p v-if="tracker.events.length === 0" class="text-sm text-surface-500">
                    No events yet — the carrier hasn't scanned this package. Check back shortly.
                </p>
            </div>
        </div>

        <div v-else class="bg-white rounded-xl border border-surface-200 p-8 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                <i class="pi pi-send text-2xl"></i>
            </div>
            <h2 class="mt-4 text-xl font-semibold text-surface-900">Enter a tracking code</h2>
            <p class="mt-2 text-surface-600">
                Paste the tracking code from your shipping confirmation email above to get started.
            </p>
        </div>
    </div>
</template>
