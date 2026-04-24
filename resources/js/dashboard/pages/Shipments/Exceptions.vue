<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
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
        // Combine rate_failed + voided as "exceptions" for now; dedicated endpoint lands in step 05e
        const [failed, voided] = await Promise.all([
            shipmentsApi.list({ status: 'rate_failed' }),
            shipmentsApi.list({ status: 'voided' }),
        ]);
        rows.value = [...failed.data, ...voided.data];
    } finally {
        loading.value = false;
    }
}

function goTo(row: ShipmentListItem): void {
    router.push(`/dashboard/shipments/${row.id}`);
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Exceptions" subtitle="Failed purchases and voided labels needing attention." />

        <div class="bg-white rounded-xl border border-surface-200 overflow-hidden">
            <DataTable
                :value="rows"
                :loading="loading"
                striped-rows
                data-key="id"
                selection-mode="single"
                @row-click="(e) => goTo(e.data as ShipmentListItem)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="status" header="Status">
                    <template #body="s"><StatusPill :status="s.data.status" /></template>
                </Column>
                <Column field="reference" header="Reference" />
                <Column header="To">
                    <template #body="s">
                        <span v-if="s.data.to_address">
                            {{ [s.data.to_address.city, s.data.to_address.state, s.data.to_address.country].filter(Boolean).join(', ') }}
                        </span>
                    </template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No exceptions. Nice.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
