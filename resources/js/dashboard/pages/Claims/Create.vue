<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { claimsApi } from '@dashboard/api/care';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({
    shipment_id: null as number | null,
    type: 'damage' as 'damage' | 'loss' | 'missing_items',
    amount_dollars: 0,
    description: '',
});

const types = [
    { label: 'Damaged', value: 'damage' },
    { label: 'Lost in transit', value: 'loss' },
    { label: 'Missing items', value: 'missing_items' },
];

const err = ref<string | null>(null);
const saving = ref(false);

const amountCents = computed(() => Math.round(form.amount_dollars * 100));

async function submit(): Promise<void> {
    if (!form.shipment_id || amountCents.value <= 0 || form.description.length < 5) {
        err.value = 'Please fill shipment ID, amount, and a description of at least 5 characters.';
        return;
    }
    err.value = null;
    saving.value = true;
    try {
        const res = await claimsApi.create({
            shipment_id: form.shipment_id,
            type: form.type,
            amount_cents: amountCents.value,
            description: form.description,
        });
        toast.success(`Claim #${res.id} opened`);
        router.push(`/dashboard/claims/${res.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not open claim.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="Open claim" subtitle="Start a carrier claim. Submit to EasyPost once evidence is attached." />

        <div class="card max-w-2xl space-y-4">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div>
                <label class="block text-sm font-medium mb-1">Shipment ID *</label>
                <InputNumber v-model="form.shipment_id" :min="1" class="w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type *</label>
                <Select v-model="form.type" :options="types" option-label="label" option-value="value" class="w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Amount ($) *</label>
                <InputNumber v-model="form.amount_dollars" mode="currency" currency="USD" class="w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description *</label>
                <Textarea v-model="form.description" rows="4" class="w-full" placeholder="What happened? Include any carrier scan info." />
            </div>

            <div class="flex justify-end">
                <Button label="Open claim" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
