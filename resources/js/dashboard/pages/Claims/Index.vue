<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { claimsApi, type ClaimItem } from '@dashboard/api/care';

const router = useRouter();
const rows = ref<ClaimItem[]>([]);
const loading = ref(false);

function money(c: number | null): string {
    return c == null ? '—' : `$${(c / 100).toFixed(2)}`;
}
function stateSeverity(s: string): 'info' | 'success' | 'warn' | 'danger' | 'secondary' {
    if (s === 'paid') return 'success';
    if (s === 'approved') return 'info';
    if (s === 'closed') return 'secondary';
    if (s === 'submitted') return 'warn';
    return 'warn';
}

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await claimsApi.list()).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Claims" subtitle="Track lost/damaged package claims through to recovery.">
            <template #actions>
                <router-link to="/dashboard/claims/create">
                    <Button label="Open claim" icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="card">
            <DataTable
                :value="rows"
                :loading="loading"
                striped-rows
                selection-mode="single"
                data-key="id"
                @row-click="(e) => router.push(`/dashboard/claims/${(e.data as ClaimItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="State">
                    <template #body="s"><Tag :value="s.data.state" :severity="stateSeverity(s.data.state)" /></template>
                </Column>
                <Column field="type" header="Type" />
                <Column header="Amount">
                    <template #body="s">{{ money(s.data.amount_cents) }}</template>
                </Column>
                <Column header="Recovered">
                    <template #body="s">{{ money(s.data.recovered_cents) }}</template>
                </Column>
                <Column header="Shipment">
                    <template #body="s">
                        <span v-if="s.data.shipment">#{{ s.data.shipment.id }} {{ s.data.shipment.tracking_code ?? '' }}</span>
                    </template>
                </Column>
                <Column header="Assigned">
                    <template #body="s">{{ s.data.assignee?.name ?? '—' }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No claims yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
