<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { returnsApi } from '@dashboard/api/care';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({
    original_shipment_id: null as number | null,
    reason: 'other' as string,
    notes: '',
    items: '',
    auto_refund: false,
});

const reasons = [
    { label: 'Wrong item', value: 'wrong_item' },
    { label: 'Damaged in transit', value: 'damaged' },
    { label: 'Does not fit', value: 'sizing' },
    { label: 'Changed mind', value: 'buyer_remorse' },
    { label: 'Other', value: 'other' },
];

const err = ref<string | null>(null);
const saving = ref(false);

async function submit(): Promise<void> {
    if (!form.original_shipment_id) { err.value = 'Original shipment ID is required.'; return; }
    err.value = null;
    saving.value = true;
    try {
        const res = await returnsApi.create({
            original_shipment_id: form.original_shipment_id,
            reason: form.reason,
            notes: form.notes || undefined,
            items: form.items ? form.items.split('\n').map((i) => i.trim()).filter(Boolean) : undefined,
            auto_refund: form.auto_refund || undefined,
        });
        toast.success(`Return request #${res.id} submitted`);
        router.push(`/dashboard/returns/${res.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create return.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="Request a return" subtitle="A manager will review and approve if eligible." />

        <div class="card max-w-2xl space-y-4">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div>
                <label class="block text-sm font-medium mb-1">Original shipment ID *</label>
                <InputNumber v-model="form.original_shipment_id" :min="1" class="w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Reason</label>
                <Select
                    v-model="form.reason"
                    :options="reasons"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Items (one per line)</label>
                <Textarea v-model="form.items" rows="3" class="w-full" placeholder="SKU-001 × 1"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <Textarea v-model="form.notes" rows="3" class="w-full" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <Checkbox v-model="form.auto_refund" binary />
                Auto-refund once delivered
            </label>

            <div class="flex justify-end">
                <Button label="Submit request" icon="pi pi-check" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
