<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import KpiCard from '@dashboard/components/home/KpiCard.vue';
import { insightsApi, type OverviewResp } from '@dashboard/api/insights';

const data = ref<OverviewResp | null>(null);
const loading = ref(false);

function money(c: number): string {
    return `$${(c / 100).toFixed(2)}`;
}

async function load(): Promise<void> {
    loading.value = true;
    try { data.value = await insightsApi.overview(); }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Analytics" subtitle="Shipment volume, spend, and trends." />

        <div v-if="data" class="grid md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <KpiCard label="Total shipments" :value="data.total_shipments" icon="pi-box" />
            <KpiCard label="Total spend" :value="money(data.total_cost_cents)" icon="pi-wallet" />
            <KpiCard label="Carriers used" :value="data.by_carrier.length" icon="pi-truck" />
            <KpiCard label="Statuses" :value="data.by_status.length" icon="pi-chart-bar" />
        </div>

        <div v-if="data" class="grid lg:grid-cols-2 gap-4">
            <div class="card">
                <h3 class="font-semibold mb-3">By carrier</h3>
                <DataTable :value="data.by_carrier" striped-rows>
                    <Column field="carrier" header="Carrier" />
                    <Column field="count" header="Count" />
                    <Column header="Spend">
                        <template #body="s">{{ money(s.data.cost_cents) }}</template>
                    </Column>
                </DataTable>
            </div>
            <div class="card">
                <h3 class="font-semibold mb-3">By status</h3>
                <DataTable :value="data.by_status" striped-rows>
                    <Column field="status" header="Status" />
                    <Column field="count" header="Count" />
                    <Column header="Spend">
                        <template #body="s">{{ money(s.data.cost_cents) }}</template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <div v-if="data" class="card mt-4">
            <h3 class="font-semibold mb-3">Daily (last 30 days)</h3>
            <DataTable :value="data.daily_30d" striped-rows>
                <Column field="date" header="Date" />
                <Column field="count" header="Count" />
                <Column header="Spend">
                    <template #body="s">{{ money(s.data.cost_cents) }}</template>
                </Column>
            </DataTable>
        </div>

        <div v-else class="card text-surface-500">Loading analytics…</div>
    </div>
</template>
