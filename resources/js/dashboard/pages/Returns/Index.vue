<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { returnsApi, type ReturnItem } from '@dashboard/api/care';

const router = useRouter();
const rows = ref<ReturnItem[]>([]);
const loading = ref(false);

function statusSeverity(s: string): 'info' | 'success' | 'warn' | 'danger' | 'secondary' {
    if (s === 'approved') return 'success';
    if (s === 'declined') return 'danger';
    if (s === 'requested') return 'warn';
    return 'secondary';
}

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await returnsApi.list()).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Returns" subtitle="Return requests, approvals, return labels, and refunds.">
            <template #actions>
                <router-link to="/dashboard/returns/create">
                    <Button label="Request return" icon="pi pi-plus" />
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
                @row-click="(e) => router.push(`/dashboard/returns/${(e.data as ReturnItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" :severity="statusSeverity(s.data.status)" /></template>
                </Column>
                <Column field="reason" header="Reason" />
                <Column field="original_shipment_id" header="Original shipment" />
                <Column field="return_shipment_id" header="Return shipment" />
                <Column header="Requested by">
                    <template #body="s">{{ s.data.created_by?.name ?? '—' }}</template>
                </Column>
                <Column header="Created">
                    <template #body="s">{{ new Date(s.data.created_at).toLocaleString() }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No returns yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
