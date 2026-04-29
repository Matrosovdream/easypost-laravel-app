<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { trackersApi } from '@dashboard/api/data';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({ tracking_code: '', carrier: 'USPS' });
const saving = ref(false);
const err = ref<string | null>(null);

async function submit(): Promise<void> {
    if (!form.tracking_code) { err.value = 'Tracking code is required.'; return; }
    err.value = null;
    saving.value = true;
    try {
        const t = await trackersApi.create(form.tracking_code, form.carrier);
        toast.success(`Tracker #${t.id} created`);
        router.push(`/dashboard/trackers/${t.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create tracker.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="New tracker" subtitle="Create a standalone tracker for a shipment handled elsewhere." />

        <div class="card max-w-md space-y-3">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div>
                <label class="block text-sm font-medium mb-1">Tracking code *</label>
                <InputText v-model="form.tracking_code" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Carrier *</label>
                <InputText v-model="form.carrier" class="w-full" />
            </div>
            <div class="flex justify-end">
                <Button label="Create" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
