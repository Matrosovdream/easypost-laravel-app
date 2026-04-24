<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { clientsApi } from '@dashboard/api/clients';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({
    company_name: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    flexrate_markup_pct: 0,
    credit_terms_days: 30,
    notes: '',
});

const saving = ref(false);
const err = ref<string | null>(null);

async function submit(): Promise<void> {
    if (!form.company_name) { err.value = 'Company name required.'; return; }
    err.value = null;
    saving.value = true;
    try {
        const c = await clientsApi.create({
            company_name: form.company_name,
            contact_name: form.contact_name || undefined,
            contact_email: form.contact_email || undefined,
            contact_phone: form.contact_phone || undefined,
            flexrate_markup_pct: form.flexrate_markup_pct,
            credit_terms_days: form.credit_terms_days,
            notes: form.notes || undefined,
        });
        toast.success(`Client #${c.id} created`);
        router.push(`/dashboard/clients/${c.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="New client" subtitle="Add a 3PL tenant." />

        <div class="card max-w-2xl space-y-3">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div>
                <label class="block text-sm font-medium mb-1">Company *</label>
                <InputText v-model="form.company_name" class="w-full" />
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Contact name</label>
                    <InputText v-model="form.contact_name" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Contact email</label>
                    <InputText v-model="form.contact_email" type="email" class="w-full" />
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <InputText v-model="form.contact_phone" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">FlexRate markup (%)</label>
                    <InputNumber v-model="form.flexrate_markup_pct" :min="0" :max="100" :max-fraction-digits="2" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Net terms (days)</label>
                    <InputNumber v-model="form.credit_terms_days" :min="0" :max="180" class="w-full" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <Textarea v-model="form.notes" rows="3" class="w-full" />
            </div>
            <div class="flex justify-end">
                <Button label="Create client" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
