<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import type { ApprovalItem } from '@dashboard/types/shipment';
import { useToast } from '@dashboard/composables/useToast';

const rows = ref<ApprovalItem[]>([]);
const loading = ref(false);
const toast = useToast();

async function load(): Promise<void> {
    loading.value = true;
    try {
        const res = await shipmentsApi.approvals('pending');
        rows.value = res.data;
    } finally {
        loading.value = false;
    }
}

function money(cents: number | null): string {
    if (cents == null) return '—';
    return `$${(cents / 100).toFixed(2)}`;
}

async function approve(a: ApprovalItem): Promise<void> {
    try {
        await shipmentsApi.approve(a.id);
        toast.success('Approved', 'Label purchased.');
        await load();
    } catch {
        toast.error('Could not approve.');
    }
}

async function decline(a: ApprovalItem): Promise<void> {
    const reason = window.prompt('Decline reason:') ?? undefined;
    try {
        await shipmentsApi.decline(a.id, reason);
        toast.success('Declined');
        await load();
    } catch {
        toast.error('Could not decline.');
    }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Approvals" subtitle="Shipments waiting for a manager to sign off." />

        <div class="bg-white rounded-xl border border-surface-200 overflow-hidden">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Shipment">
                    <template #body="s">
                        <router-link :to="`/dashboard/shipments/${s.data.shipment_id}`" class="text-primary-600 hover:underline">
                            #{{ s.data.shipment_id }}
                        </router-link>
                        <div v-if="s.data.shipment?.reference" class="text-xs text-surface-500">{{ s.data.shipment.reference }}</div>
                    </template>
                </Column>
                <Column header="Cost">
                    <template #body="s">{{ money(s.data.cost_cents) }}</template>
                </Column>
                <Column field="reason" header="Reason" />
                <Column header="Requested by">
                    <template #body="s">{{ s.data.requested_by?.name ?? '—' }}</template>
                </Column>
                <Column header="Destination">
                    <template #body="s">
                        <span v-if="s.data.shipment?.to_address">
                            {{ [s.data.shipment.to_address.city, s.data.shipment.to_address.state].filter(Boolean).join(', ') }}
                        </span>
                        <span v-else>—</span>
                    </template>
                </Column>
                <Column header="Actions" style="width: 14rem">
                    <template #body="s">
                        <div class="flex gap-2">
                            <Button size="small" label="Approve" icon="pi pi-check" @click="approve(s.data)" />
                            <Button size="small" label="Decline" severity="danger" outlined @click="decline(s.data)" />
                        </div>
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No pending approvals.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
