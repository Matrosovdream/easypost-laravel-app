<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { scanFormsApi, type ScanFormItem } from '@dashboard/api/operations';

const rows = ref<ScanFormItem[]>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try {
        rows.value = (await scanFormsApi.list()).data;
    } finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Scan forms" subtitle="One form per carrier + origin. Hand it to the driver at pickup.">
            <template #actions>
                <router-link to="/dashboard/scan-forms/create">
                    <Button label="New scan form" icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="carrier" header="Carrier" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <Column header="Shipments">
                    <template #body="s">{{ s.data.tracking_codes?.length ?? 0 }}</template>
                </Column>
                <Column header="Form PDF">
                    <template #body="s">
                        <a v-if="s.data.form_url" :href="s.data.form_url" target="_blank" class="text-primary-600 hover:underline">
                            <i class="pi pi-file-pdf"></i> Download
                        </a>
                        <span v-else class="text-surface-500 text-sm">—</span>
                    </template>
                </Column>
                <Column header="Created">
                    <template #body="s">{{ new Date(s.data.created_at).toLocaleString() }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No scan forms yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
