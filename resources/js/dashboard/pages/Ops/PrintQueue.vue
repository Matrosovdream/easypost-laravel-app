<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { insightsApi, type PrintQueueItem } from '@dashboard/api/insights';
import { shipmentsApi } from '@dashboard/api/shipments';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const rows = ref<PrintQueueItem[]>([]);
const loading = ref(false);
const busy = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await insightsApi.printQueue()).data; }
    finally { loading.value = false; }
}

async function pack(row: PrintQueueItem): Promise<void> {
    busy.value = true;
    try { await shipmentsApi.pack(row.id); toast.success(`Packed #${row.id}`); await load(); }
    catch { toast.error('Could not mark packed.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Print queue" subtitle="Purchased labels ready to print & pack.">
            <template #actions>
                <Button label="Refresh" icon="pi pi-refresh" severity="secondary" :loading="loading" @click="load" />
            </template>
        </PageHeader>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="reference" header="Reference" />
                <Column header="To">
                    <template #body="s">
                        <span v-if="s.data.to_address">{{ s.data.to_address.name }} — {{ [s.data.to_address.city, s.data.to_address.state].filter(Boolean).join(', ') }}</span>
                    </template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="tracking_code" header="Tracking" />
                <Column header="Label">
                    <template #body="s">
                        <a v-if="s.data.label_url" :href="s.data.label_url" target="_blank" class="text-primary-600 hover:underline">
                            <i class="pi pi-file-pdf"></i> Print
                        </a>
                    </template>
                </Column>
                <Column header="Actions" style="width: 10rem">
                    <template #body="s">
                        <Button size="small" label="Packed" icon="pi pi-check" :loading="busy" @click="pack(s.data)" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">Queue is empty. 🎉</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
