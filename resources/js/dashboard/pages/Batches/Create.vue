<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import { batchesApi } from '@dashboard/api/operations';
import type { ShipmentListItem } from '@dashboard/types/shipment';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();
const rows = ref<ShipmentListItem[]>([]);
const selected = ref<ShipmentListItem[]>([]);
const reference = ref('');
const loading = ref(false);
const saving = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try {
        rows.value = (await shipmentsApi.list({ status: 'purchased', per_page: 100 })).data;
    } finally {
        loading.value = false;
    }
}

async function submit(): Promise<void> {
    if (selected.value.length === 0) {
        toast.warn('Pick shipments first');
        return;
    }
    saving.value = true;
    try {
        const res = await batchesApi.create({
            shipment_ids: selected.value.map((s) => s.id),
            reference: reference.value || undefined,
        });
        toast.success(`Batch #${res.id} created`);
        router.push(`/dashboard/batches/${res.id}`);
    } catch {
        toast.error('Could not create batch.');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="New batch" subtitle="Select purchased shipments to include.">
            <template #actions>
                <Button
                    :label="`Create batch (${selected.length})`"
                    :loading="saving"
                    :disabled="selected.length === 0"
                    @click="submit"
                />
            </template>
        </PageHeader>

        <div class="card mb-4">
            <label class="block text-sm font-medium mb-1">Reference (optional)</label>
            <InputText v-model="reference" placeholder="e.g. 2026-04-24 wave" class="w-full max-w-md" />
        </div>

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
                <Column field="reference" header="Reference" />
                <Column header="To">
                    <template #body="s">
                        <span v-if="s.data.to_address">
                            {{ [s.data.to_address.city, s.data.to_address.state].filter(Boolean).join(', ') }}
                        </span>
                    </template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="tracking_code" header="Tracking" />
                <template #empty>
                    <div class="text-center py-10 text-surface-500">
                        No purchased shipments available to batch.
                    </div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
