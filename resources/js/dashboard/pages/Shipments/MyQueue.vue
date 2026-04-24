<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import StatusPill from '@dashboard/components/shipments/StatusPill.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import type { ShipmentListItem } from '@dashboard/types/shipment';
import { useRouter } from 'vue-router';

const router = useRouter();
const rows = ref<ShipmentListItem[]>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try {
        const res = await shipmentsApi.myQueue();
        rows.value = res.data;
    } finally {
        loading.value = false;
    }
}

function pack(row: ShipmentListItem): void {
    router.push(`/dashboard/shipments/${row.id}?pack=1`);
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="My queue" subtitle="Shipments assigned to you that need packing." />

        <div class="bg-white rounded-xl border border-surface-200 overflow-hidden">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="status" header="Status">
                    <template #body="s"><StatusPill :status="s.data.status" /></template>
                </Column>
                <Column header="To">
                    <template #body="s">
                        <span v-if="s.data.to_address">
                            {{ s.data.to_address.name }} — {{ [s.data.to_address.city, s.data.to_address.state].filter(Boolean).join(', ') }}
                        </span>
                    </template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="reference" header="Reference" />
                <Column header="Actions" style="width: 10rem">
                    <template #body="s">
                        <Button v-if="s.data.status === 'purchased'" size="small" label="Pack" icon="pi pi-box" @click="pack(s.data)" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">Nothing in your queue.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
