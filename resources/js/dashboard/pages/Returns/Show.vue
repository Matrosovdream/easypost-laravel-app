<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { returnsApi, type ReturnDetail } from '@dashboard/api/care';
import { useAuthStore } from '@dashboard/stores/auth';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const toast = useToast();
const auth = useAuthStore();
const detail = ref<ReturnDetail | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    detail.value = await returnsApi.show(Number(route.params.id));
}

async function approve(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        await returnsApi.approve(detail.value.id);
        toast.success('Return approved & return shipment created');
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        toast.error(r.response?.data?.message ?? 'Could not approve.');
    } finally {
        busy.value = false;
    }
}

async function decline(): Promise<void> {
    if (!detail.value) return;
    const reason = window.prompt('Decline reason:') ?? undefined;
    busy.value = true;
    try {
        await returnsApi.decline(detail.value.id, reason);
        toast.success('Return declined');
        await load();
    } catch {
        toast.error('Could not decline.');
    } finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="detail">
        <PageHeader :title="`Return #${detail.id}`" :subtitle="detail.reason ?? undefined">
            <template #actions>
                <router-link to="/dashboard/returns">
                    <Button label="Back" severity="secondary" text />
                </router-link>
                <template v-if="detail.status === 'requested' && auth.can('returns.approve')">
                    <Button label="Approve" icon="pi pi-check" :loading="busy" @click="approve" />
                    <Button label="Decline" severity="danger" outlined :loading="busy" @click="decline" />
                </template>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Status</div>
                <div class="mt-2"><Tag :value="detail.status" /></div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Auto refund</div>
                <div class="mt-2 text-sm">{{ detail.auto_refund ? 'Yes' : 'No' }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Approved by</div>
                <div class="mt-2 text-sm">{{ detail.approved_by?.name ?? '—' }}</div>
                <div class="text-xs text-surface-500" v-if="detail.approved_at">{{ new Date(detail.approved_at).toLocaleString() }}</div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="card">
                <h3 class="font-semibold">Original shipment</h3>
                <div v-if="detail.original_shipment" class="mt-2 text-sm space-y-0.5">
                    <div>#{{ detail.original_shipment.id }} · {{ detail.original_shipment.reference ?? '—' }}</div>
                    <div class="text-surface-500 font-mono">{{ detail.original_shipment.tracking_code ?? '—' }}</div>
                    <router-link :to="`/dashboard/shipments/${detail.original_shipment.id}`" class="text-primary-600 hover:underline text-xs">
                        Open shipment →
                    </router-link>
                </div>
            </div>
            <div class="card">
                <h3 class="font-semibold">Return shipment</h3>
                <div v-if="detail.return_shipment" class="mt-2 text-sm space-y-0.5">
                    <div>#{{ detail.return_shipment.id }} · {{ detail.return_shipment.reference ?? '—' }}</div>
                    <div class="text-surface-500 font-mono">{{ detail.return_shipment.tracking_code ?? '—' }}</div>
                    <div><Tag :value="detail.return_shipment.status" /></div>
                    <router-link :to="`/dashboard/shipments/${detail.return_shipment.id}`" class="text-primary-600 hover:underline text-xs">
                        Open return shipment →
                    </router-link>
                </div>
                <div v-else class="mt-2 text-sm text-surface-500">Not yet created (approve to generate).</div>
            </div>
        </div>

        <div v-if="detail.items || detail.notes" class="card mt-4">
            <h3 class="font-semibold">Details</h3>
            <div v-if="detail.items" class="mt-2">
                <div class="text-xs text-surface-500 uppercase">Items</div>
                <ul class="mt-1 list-disc ml-5 text-sm">
                    <li v-for="(i, idx) in detail.items" :key="idx">{{ i }}</li>
                </ul>
            </div>
            <div v-if="detail.notes" class="mt-2">
                <div class="text-xs text-surface-500 uppercase">Notes</div>
                <p class="mt-1 text-sm text-surface-700 whitespace-pre-wrap">{{ detail.notes }}</p>
            </div>
        </div>
    </div>
</template>
