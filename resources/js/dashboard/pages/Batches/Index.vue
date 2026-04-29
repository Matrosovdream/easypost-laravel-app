<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { batchesApi, type BatchListItem } from '@dashboard/api/operations';
import { useRouter } from 'vue-router';

const router = useRouter();
const rows = ref<BatchListItem[]>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try {
        rows.value = (await batchesApi.list()).data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Batches" subtitle="Build daily waves, buy in bulk, merge to one PDF.">
            <template #actions>
                <router-link to="/dashboard/batches/create">
                    <Button label="New batch" icon="pi pi-plus" />
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
                @row-click="(e) => router.push(`/dashboard/batches/${(e.data as BatchListItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="State">
                    <template #body="s"><Tag :value="s.data.state" /></template>
                </Column>
                <Column field="reference" header="Reference" />
                <Column field="num_shipments" header="Shipments" />
                <Column header="Creator">
                    <template #body="s">{{ s.data.created_by?.name ?? '—' }}</template>
                </Column>
                <Column header="Created">
                    <template #body="s">{{ new Date(s.data.created_at).toLocaleString() }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No batches yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
