<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import { scanFormsApi } from '@dashboard/api/operations';
import type { ShipmentListItem } from '@dashboard/types/shipment';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();
const rows = ref<ShipmentListItem[]>([]);
const selected = ref<ShipmentListItem[]>([]);
const loading = ref(false);
const saving = ref(false);
const err = ref<string | null>(null);

async function load(): Promise<void> {
    loading.value = true;
    try {
        rows.value = (await shipmentsApi.list({ status: 'purchased', per_page: 100 })).data;
    } finally { loading.value = false; }
}

async function submit(): Promise<void> {
    err.value = null;
    saving.value = true;
    try {
        const res = await scanFormsApi.create(selected.value.map((s) => s.id));
        toast.success(`Scan form #${res.id} created`);
        router.push('/dashboard/scan-forms');
    } catch (e: unknown) {
        const r = e as { response?: { status: number; data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create scan form.';
    } finally { saving.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="New scan form" subtitle="All selected shipments must share the same carrier and origin.">
            <template #actions>
                <Button :label="`Create (${selected.length})`" :disabled="selected.length === 0" :loading="saving" @click="submit" />
            </template>
        </PageHeader>

        <Message v-if="err" severity="error" :closable="false" class="mb-4">{{ err }}</Message>

        <div class="card">
            <DataTable
                v-model:selection="selected"
                :value="rows"
                :loading="loading"
                striped-rows
                data-key="id"
            >
                <Column selection-mode="multiple" header-style="width: 3rem" />
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="tracking_code" header="Tracking" />
                <Column field="reference" header="Reference" />
                <template #empty>
                    <div class="text-center py-10 text-surface-500">
                        No purchased shipments available.
                    </div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
