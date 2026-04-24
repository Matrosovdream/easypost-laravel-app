<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { batchesApi, scanFormsApi, type BatchDetail } from '@dashboard/api/operations';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const toast = useToast();
const detail = ref<BatchDetail | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    detail.value = await batchesApi.show(Number(route.params.id));
}

async function doBuy(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        await batchesApi.buy(detail.value.id);
        toast.success('Batch purchase requested');
        await load();
    } catch {
        toast.error('Could not buy batch.');
    } finally { busy.value = false; }
}

async function doLabels(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        await batchesApi.labels(detail.value.id);
        toast.success('Labels requested');
        await load();
    } catch {
        toast.error('Could not request labels.');
    } finally { busy.value = false; }
}

async function doScanForm(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try {
        const res = await scanFormsApi.create(detail.value.shipments.map((s) => s.id));
        toast.success(`Scan form #${res.id} created`);
    } catch (e: unknown) {
        const err = e as { response?: { data?: { message?: string } } };
        toast.error(err.response?.data?.message ?? 'Could not create scan form.');
    } finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="detail">
        <PageHeader :title="`Batch #${detail.id}`" :subtitle="detail.reference ?? undefined">
            <template #actions>
                <router-link to="/dashboard/batches">
                    <Button label="Back to list" severity="secondary" text />
                </router-link>
                <Button label="Buy batch" icon="pi pi-shopping-cart" :loading="busy" @click="doBuy" />
                <Button label="Generate labels" icon="pi pi-file-pdf" severity="secondary" outlined :loading="busy" @click="doLabels" />
                <Button label="Scan form" icon="pi pi-file" severity="secondary" outlined :loading="busy" @click="doScanForm" />
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">State</div>
                <div class="mt-2"><Tag :value="detail.state" /></div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Shipments</div>
                <div class="mt-2 text-2xl font-bold">{{ detail.num_shipments }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Label PDF</div>
                <a v-if="detail.label_url" :href="detail.label_url" target="_blank" class="mt-2 inline-flex items-center gap-2 text-primary-600 hover:underline">
                    <i class="pi pi-file-pdf"></i> Download
                </a>
                <div v-else class="text-sm text-surface-500 mt-2">Not generated yet</div>
            </div>
        </div>

        <div class="card">
            <DataTable :value="detail.shipments" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="tracking_code" header="Tracking" />
                <Column header="Batch status">
                    <template #body="s">
                        <Tag
                            v-if="s.data.batch_status"
                            :value="s.data.batch_status"
                            :severity="s.data.batch_status === 'postage_purchased' ? 'success' : 'info'"
                        />
                    </template>
                </Column>
                <Column header="Note">
                    <template #body="s">{{ s.data.batch_message ?? '' }}</template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
