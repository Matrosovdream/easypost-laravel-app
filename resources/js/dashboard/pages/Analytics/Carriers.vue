<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { insightsApi, type CarriersResp } from '@dashboard/api/insights';

const data = ref<CarriersResp | null>(null);

function money(c: number): string {
    return `$${(c / 100).toFixed(2)}`;
}

async function load(): Promise<void> {
    data.value = await insightsApi.carriers();
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Carrier performance" subtitle="Delivery rates and cost per shipment by carrier." />

        <div class="card">
            <DataTable v-if="data" :value="data.carriers" striped-rows>
                <Column field="carrier" header="Carrier" />
                <Column field="total" header="Total" />
                <Column field="delivered" header="Delivered" />
                <Column field="voided" header="Voided" />
                <Column header="Delivery rate">
                    <template #body="s">{{ s.data.delivery_rate_pct }}%</template>
                </Column>
                <Column header="Spend">
                    <template #body="s">{{ money(s.data.cost_cents) }}</template>
                </Column>
                <Column header="Avg / shipment">
                    <template #body="s">{{ money(s.data.avg_cost_cents) }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No carrier data yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
