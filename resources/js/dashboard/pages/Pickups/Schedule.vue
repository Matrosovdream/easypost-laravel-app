<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { pickupsApi } from '@dashboard/api/operations';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({
    address_id: null as number | null,
    min_datetime: null as Date | null,
    max_datetime: null as Date | null,
    instructions: '',
    reference: '',
});

const saving = ref(false);
const err = ref<string | null>(null);

async function submit(): Promise<void> {
    if (!form.address_id || !form.min_datetime || !form.max_datetime) {
        err.value = 'Please fill all required fields.';
        return;
    }
    err.value = null;
    saving.value = true;
    try {
        const res = await pickupsApi.schedule({
            address_id: form.address_id,
            min_datetime: form.min_datetime.toISOString(),
            max_datetime: form.max_datetime.toISOString(),
            instructions: form.instructions || undefined,
            reference: form.reference || undefined,
        });
        toast.success(`Pickup #${res.id} scheduled`);
        router.push(`/dashboard/pickups/${res.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not schedule pickup.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="Schedule pickup" subtitle="Pick an address, a window, and the driver will come." />

        <div class="card max-w-2xl space-y-4">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div>
                <label class="block text-sm font-medium mb-1">Pickup address ID *</label>
                <InputNumber v-model="form.address_id" :min="1" class="w-full" />
                <p class="text-xs text-surface-500 mt-1">
                    Enter the ID from your address book. (Full picker lands with step 05e.)
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Window start *</label>
                    <DatePicker v-model="form.min_datetime" show-time hour-format="24" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Window end *</label>
                    <DatePicker v-model="form.max_datetime" show-time hour-format="24" class="w-full" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Driver instructions</label>
                <Textarea v-model="form.instructions" rows="3" class="w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Reference</label>
                <InputText v-model="form.reference" class="w-full" />
            </div>

            <div class="flex justify-end">
                <Button label="Request pickup rates" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
