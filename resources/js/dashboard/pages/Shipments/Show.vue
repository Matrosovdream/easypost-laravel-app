<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Button from 'primevue/button';
import Timeline from 'primevue/timeline';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import StatusPill from '@dashboard/components/shipments/StatusPill.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import type { ShipmentDetail } from '@dashboard/types/shipment';
import { useAuthStore } from '@dashboard/stores/auth';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const toast = useToast();

const detail = ref<ShipmentDetail | null>(null);
const loading = ref(true);
const busy = ref(false);
const err = ref<string | null>(null);

const isPackMode = computed(() => route.query.pack === '1');

function money(cents: number | null | undefined, fallback = '—'): string {
    if (cents == null) return fallback;
    return `$${(cents / 100).toFixed(2)}`;
}

async function load(): Promise<void> {
    loading.value = true;
    err.value = null;
    try {
        detail.value = await shipmentsApi.show(Number(route.params.id));
    } catch (e: unknown) {
        const r = e as { response?: { status: number } };
        if (r.response?.status === 403) router.replace('/dashboard/403');
        else if (r.response?.status === 404) router.replace('/dashboard/shipments');
        else err.value = 'Could not load shipment.';
    } finally {
        loading.value = false;
    }
}

async function buy(rateId: string): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        const res = await shipmentsApi.buy(detail.value.id, rateId);
        if (res.status === 'approval_required') {
            toast.warn('Approval requested', 'This rate exceeds your cap. A manager was notified.');
        } else {
            toast.success('Label purchased');
        }
        await load();
    } catch {
        toast.error('Could not purchase label.');
    } finally {
        busy.value = false;
    }
}

async function doPack(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        await shipmentsApi.pack(detail.value.id);
        toast.success('Marked packed');
        await load();
    } catch {
        toast.error('Could not mark packed.');
    } finally {
        busy.value = false;
    }
}

async function doVoid(): Promise<void> {
    if (!detail.value) return;
    const reason = window.prompt('Void reason (optional):') ?? undefined;
    busy.value = true;
    try {
        await shipmentsApi.void(detail.value.id, reason);
        toast.success('Shipment voided');
        await load();
    } catch {
        toast.error('Could not void shipment.');
    } finally {
        busy.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="text-surface-500">Loading…</div>
    <Message v-else-if="err" severity="error" :closable="false">{{ err }}</Message>

    <div v-else-if="detail">
        <PageHeader
            :title="`Shipment #${detail.id}`"
            :subtitle="detail.reference ? `Reference: ${detail.reference}` : undefined"
        >
            <template #actions>
                <router-link to="/dashboard/shipments">
                    <Button label="Back to list" severity="secondary" text />
                </router-link>
                <Button
                    v-if="detail.status === 'purchased' && auth.can('labels.print')"
                    label="Mark packed"
                    icon="pi pi-check"
                    :loading="busy"
                    @click="doPack"
                />
                <Button
                    v-if="['purchased', 'packed'].includes(detail.status) && auth.can('shipments.void')"
                    label="Void"
                    severity="danger"
                    outlined
                    :loading="busy"
                    @click="doVoid"
                />
            </template>
        </PageHeader>

        <div v-if="isPackMode && detail.status === 'purchased'" class="mb-6 p-8 bg-primary-50 border border-primary-200 rounded-xl text-center">
            <i class="pi pi-box text-5xl text-primary-500"></i>
            <div class="mt-4 text-xl font-semibold">Pack mode</div>
            <p class="text-surface-600 mt-2">Confirm you've packed this shipment, then hit the big button.</p>
            <Button
                class="mt-6"
                size="large"
                label="Mark packed"
                icon="pi pi-check"
                :loading="busy"
                @click="doPack"
            />
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-surface-200 p-4">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Status</div>
                <div class="mt-2"><StatusPill :status="detail.status" /></div>
            </div>
            <div class="bg-white rounded-xl border border-surface-200 p-4">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Cost</div>
                <div class="mt-2 text-2xl font-bold text-surface-900">{{ money(detail.cost_cents) }}</div>
                <div class="text-xs text-surface-500">{{ detail.carrier }} · {{ detail.service ?? '—' }}</div>
            </div>
            <div class="bg-white rounded-xl border border-surface-200 p-4">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Tracking</div>
                <div class="mt-2 font-mono text-sm">{{ detail.tracking_code ?? '—' }}</div>
            </div>
        </div>

        <Tabs value="overview">
            <TabList>
                <Tab value="overview">Overview</Tab>
                <Tab value="rates">Rates</Tab>
                <Tab value="label">Label</Tab>
                <Tab value="timeline">Timeline</Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="overview">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl border border-surface-200 p-5">
                            <h3 class="font-semibold text-surface-900">To</h3>
                            <div class="mt-2 text-sm text-surface-700 space-y-0.5">
                                <div>{{ detail.to_address?.name ?? '—' }}</div>
                                <div>{{ detail.to_address?.street1 }}</div>
                                <div v-if="detail.to_address?.street2">{{ detail.to_address?.street2 }}</div>
                                <div>
                                    {{ [detail.to_address?.city, detail.to_address?.state, detail.to_address?.zip].filter(Boolean).join(', ') }}
                                </div>
                                <div>{{ detail.to_address?.country }}</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-surface-200 p-5">
                            <h3 class="font-semibold text-surface-900">From</h3>
                            <div class="mt-2 text-sm text-surface-700 space-y-0.5">
                                <div>{{ detail.from_address?.name ?? '—' }}</div>
                                <div>{{ detail.from_address?.street1 }}</div>
                                <div>
                                    {{ [detail.from_address?.city, detail.from_address?.state, detail.from_address?.zip].filter(Boolean).join(', ') }}
                                </div>
                                <div>{{ detail.from_address?.country }}</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-surface-200 p-5 md:col-span-2">
                            <h3 class="font-semibold text-surface-900">Parcel</h3>
                            <div class="mt-2 text-sm text-surface-700">
                                {{ detail.parcel?.weight_oz ?? '—' }} oz
                                <span v-if="detail.parcel?.length_in">
                                    · {{ detail.parcel.length_in }} × {{ detail.parcel.width_in }} × {{ detail.parcel.height_in }} in
                                </span>
                                <span v-if="detail.parcel?.predefined_package"> · {{ detail.parcel.predefined_package }}</span>
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <TabPanel value="rates">
                    <div v-if="detail.rates.length === 0" class="bg-white rounded-xl border border-surface-200 p-6 text-surface-500 text-sm">
                        No rates on this shipment. Rate-shop happens at creation.
                    </div>
                    <div v-else class="bg-white rounded-xl border border-surface-200 divide-y divide-surface-100">
                        <div
                            v-for="rate in detail.rates"
                            :key="rate.id"
                            class="p-4 flex items-center justify-between gap-4"
                        >
                            <div>
                                <div class="font-semibold text-surface-900">{{ rate.carrier }} · {{ rate.service }}</div>
                                <div class="text-xs text-surface-500">
                                    {{ rate.delivery_days ? `${rate.delivery_days} business days` : 'transit time varies' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold text-surface-900">
                                    ${{ typeof rate.rate === 'number' ? rate.rate.toFixed(2) : rate.rate }}
                                </div>
                                <div v-if="rate.retail_rate" class="text-xs line-through text-surface-400">
                                    ${{ typeof rate.retail_rate === 'number' ? rate.retail_rate.toFixed(2) : rate.retail_rate }}
                                </div>
                            </div>
                            <Button
                                v-if="['rated', 'rate_failed'].includes(detail.status) && auth.can('shipments.buy')"
                                label="Buy"
                                :loading="busy"
                                @click="buy(rate.id)"
                            />
                        </div>
                    </div>
                </TabPanel>

                <TabPanel value="label">
                    <div v-if="!detail.label_url" class="bg-white rounded-xl border border-surface-200 p-6 text-surface-500 text-sm">
                        Label not yet purchased.
                    </div>
                    <div v-else class="bg-white rounded-xl border border-surface-200 p-6">
                        <a :href="detail.label_url" target="_blank" class="text-primary-600 hover:underline">
                            <i class="pi pi-file-pdf"></i> Download label
                        </a>
                    </div>
                </TabPanel>

                <TabPanel value="timeline">
                    <div class="bg-white rounded-xl border border-surface-200 p-6">
                        <Timeline v-if="detail.events.length" :value="detail.events">
                            <template #content="slot">
                                <div class="pb-4">
                                    <div class="text-sm font-medium text-surface-900">{{ slot.item.type }}</div>
                                    <div class="text-xs text-surface-500">{{ new Date(slot.item.created_at).toLocaleString() }}</div>
                                    <pre v-if="slot.item.payload" class="mt-2 bg-surface-50 rounded p-2 text-xs text-surface-600 overflow-x-auto">{{ slot.item.payload }}</pre>
                                </div>
                            </template>
                        </Timeline>
                        <div v-else class="text-surface-500 text-sm">No events yet.</div>
                    </div>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>
