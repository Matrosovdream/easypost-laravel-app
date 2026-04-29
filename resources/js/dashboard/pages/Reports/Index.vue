<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { insightsApi, type ReportItem } from '@dashboard/api/insights';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const rows = ref<ReportItem[]>([]);
const loading = ref(false);
const saving = ref(false);
const err = ref<string | null>(null);

const form = reactive({
    type: 'shipment' as string,
    start: null as Date | null,
    end: null as Date | null,
});

const types = [
    { label: 'Shipments', value: 'shipment' },
    { label: 'Trackers', value: 'tracker' },
    { label: 'Payment log', value: 'payment_log' },
    { label: 'Refunds', value: 'refund' },
];

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await insightsApi.reports()).data; }
    finally { loading.value = false; }
}

async function generate(): Promise<void> {
    if (!form.start || !form.end) { err.value = 'Pick a date range'; return; }
    err.value = null;
    saving.value = true;
    try {
        await insightsApi.createReport({
            type: form.type,
            start_date: form.start.toISOString().slice(0, 10),
            end_date: form.end.toISOString().slice(0, 10),
        });
        toast.success('Report queued — will email when ready');
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not queue.';
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Reports" subtitle="Generate CSV exports and browse history." />

        <div class="card mb-6">
            <h3 class="font-semibold mb-3">Generate report</h3>
            <Message v-if="err" severity="error" :closable="false" class="mb-3">{{ err }}</Message>
            <div class="grid md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <Select v-model="form.type" :options="types" option-label="label" option-value="value" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Start</label>
                    <DatePicker v-model="form.start" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">End</label>
                    <DatePicker v-model="form.end" class="w-full" />
                </div>
                <Button label="Generate" :loading="saving" icon="pi pi-download" @click="generate" />
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold mb-3">History</h3>
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="type" header="Type" />
                <Column header="Range">
                    <template #body="s">{{ s.data.start_date }} — {{ s.data.end_date }}</template>
                </Column>
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <Column header="File">
                    <template #body="s">
                        <a v-if="s.data.s3_key" :href="s.data.s3_key" target="_blank" class="text-primary-600 hover:underline">
                            <i class="pi pi-file-pdf"></i> Download
                        </a>
                        <span v-else class="text-surface-500 text-xs">—</span>
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No reports yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
