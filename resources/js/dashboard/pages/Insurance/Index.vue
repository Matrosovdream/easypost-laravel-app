<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { insuranceApi, type InsuranceItem } from '@dashboard/api/care';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const rows = ref<InsuranceItem[]>([]);
const loading = ref(false);
const showModal = ref(false);

const form = reactive({
    tracking_code: '',
    carrier: 'USPS',
    amount_dollars: 100,
    reference: '',
});
const saving = ref(false);
const err = ref<string | null>(null);

function money(c: number | null): string {
    return c == null ? '—' : `$${(c / 100).toFixed(2)}`;
}

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await insuranceApi.list()).data; }
    finally { loading.value = false; }
}

async function submit(): Promise<void> {
    err.value = null;
    saving.value = true;
    try {
        const res = await insuranceApi.create({
            tracking_code: form.tracking_code,
            carrier: form.carrier,
            amount_cents: Math.round(form.amount_dollars * 100),
            reference: form.reference || undefined,
        });
        if (res.status === 'failed' && res.messages?.error) {
            toast.warn('Insurance saved, but EP rejected', res.messages.error);
        } else {
            toast.success(`Insurance #${res.id} created`);
        }
        showModal.value = false;
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create.';
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Insurance" subtitle="Standalone policies on shipments handled elsewhere.">
            <template #actions>
                <Button label="Add insurance" icon="pi pi-plus" @click="showModal = true" />
            </template>
        </PageHeader>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Tracking">
                    <template #body="s"><span class="font-mono">{{ s.data.tracking_code }}</span></template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column header="Amount">
                    <template #body="s">{{ money(s.data.amount_cents) }}</template>
                </Column>
                <Column header="Fee">
                    <template #body="s">{{ money(s.data.fee_cents) }}</template>
                </Column>
                <Column field="provider" header="Provider" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <Column header="Created">
                    <template #body="s">{{ new Date(s.data.created_at).toLocaleString() }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No policies yet.</div>
                </template>
            </DataTable>
        </div>

        <Dialog v-model:visible="showModal" header="Add standalone insurance" modal class="w-full max-w-md">
            <div class="space-y-4">
                <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

                <div>
                    <label class="block text-sm font-medium mb-1">Tracking code *</label>
                    <InputText v-model="form.tracking_code" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Carrier *</label>
                    <InputText v-model="form.carrier" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Insured amount ($) *</label>
                    <InputNumber v-model="form.amount_dollars" mode="currency" currency="USD" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reference</label>
                    <InputText v-model="form.reference" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text @click="showModal = false" />
                <Button label="Create" :loading="saving" @click="submit" />
            </template>
        </Dialog>
    </div>
</template>
