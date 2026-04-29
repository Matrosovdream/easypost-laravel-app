<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { pickupsApi, type PickupDetail } from '@dashboard/api/operations';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const toast = useToast();
const detail = ref<PickupDetail | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    detail.value = await pickupsApi.show(Number(route.params.id));
}

function money(c: number | null | undefined): string {
    return c == null ? '—' : `$${(c / 100).toFixed(2)}`;
}

async function buy(carrier: string, service: string): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        await pickupsApi.buy(detail.value.id, carrier, service);
        toast.success('Pickup confirmed');
        await load();
    } catch {
        toast.error('Could not buy pickup.');
    } finally { busy.value = false; }
}

async function cancel(): Promise<void> {
    if (!detail.value) return;
    if (!confirm('Cancel this pickup?')) return;
    busy.value = true;
    try {
        await pickupsApi.cancel(detail.value.id);
        toast.success('Pickup cancelled');
        await load();
    } catch {
        toast.error('Could not cancel.');
    } finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="detail">
        <PageHeader :title="`Pickup #${detail.id}`" :subtitle="detail.reference ?? undefined">
            <template #actions>
                <router-link to="/dashboard/pickups">
                    <Button label="Back to list" severity="secondary" text />
                </router-link>
                <Button
                    v-if="detail.status !== 'cancelled'"
                    label="Cancel"
                    severity="danger"
                    outlined
                    :loading="busy"
                    @click="cancel"
                />
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Status</div>
                <div class="mt-2"><Tag :value="detail.status" /></div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Confirmation</div>
                <div class="mt-2 font-mono">{{ detail.confirmation ?? '—' }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Cost</div>
                <div class="mt-2 text-2xl font-bold">{{ money(detail.cost_cents) }}</div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="card">
                <h3 class="font-semibold mb-2">Window</h3>
                <div class="text-sm">
                    {{ new Date(detail.min_datetime).toLocaleString() }} – {{ new Date(detail.max_datetime).toLocaleString() }}
                </div>
                <p v-if="detail.instructions" class="mt-2 text-sm text-surface-600">{{ detail.instructions }}</p>
            </div>
            <div class="card">
                <h3 class="font-semibold mb-2">Address</h3>
                <div v-if="detail.address" class="text-sm space-y-0.5">
                    <div>{{ detail.address.name }}</div>
                    <div>{{ detail.address.street1 }}</div>
                    <div>{{ [detail.address.city, detail.address.state, detail.address.zip].filter(Boolean).join(', ') }}</div>
                    <div>{{ detail.address.country }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold mb-3">Pickup rates</h3>
            <Message v-if="!detail.rates || detail.rates.length === 0" severity="info" :closable="false">
                No rates returned yet. Try again in a moment.
            </Message>
            <div v-else class="divide-y divide-surface-200">
                <div
                    v-for="rate in detail.rates"
                    :key="(rate.id ?? '') + rate.carrier + rate.service"
                    class="py-3 flex items-center justify-between gap-4"
                >
                    <div>
                        <div class="font-semibold">{{ rate.carrier }} · {{ rate.service }}</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-lg font-bold">${{ typeof rate.rate === 'number' ? rate.rate.toFixed(2) : rate.rate }}</div>
                        <Button
                            v-if="detail.status !== 'scheduled' && detail.status !== 'cancelled'"
                            label="Buy"
                            :loading="busy"
                            @click="buy(rate.carrier, rate.service)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
