<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { addressesApi } from '@dashboard/api/data';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const form = reactive({
    name: '',
    company: '',
    street1: '',
    street2: '',
    city: '',
    state: '',
    zip: '',
    country: 'US',
    phone: '',
    email: '',
    verify: true,
});

const saving = ref(false);
const err = ref<string | null>(null);

async function submit(): Promise<void> {
    err.value = null;
    saving.value = true;
    try {
        const a = await addressesApi.create({
            name: form.name || undefined,
            company: form.company || undefined,
            street1: form.street1,
            street2: form.street2 || undefined,
            city: form.city || undefined,
            state: form.state || undefined,
            zip: form.zip || undefined,
            country: form.country,
            phone: form.phone || undefined,
            email: form.email || undefined,
            verify: form.verify,
        });
        toast.success(`Address #${a.id} created${a.verified ? ' (verified)' : ''}`);
        router.push(`/dashboard/addresses/${a.id}`);
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not create address.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="New address" subtitle="Create an address and optionally verify via carrier AVS." />

        <div class="card max-w-2xl space-y-3">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <InputText v-model="form.name" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Company</label>
                    <InputText v-model="form.company" class="w-full" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Street 1 *</label>
                <InputText v-model="form.street1" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Street 2</label>
                <InputText v-model="form.street2" class="w-full" />
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <InputText v-model="form.city" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">State</label>
                    <InputText v-model="form.state" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ZIP</label>
                    <InputText v-model="form.zip" class="w-full" />
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Country *</label>
                    <InputText v-model="form.country" maxlength="2" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <InputText v-model="form.phone" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <InputText v-model="form.email" type="email" class="w-full" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <Checkbox v-model="form.verify" binary />
                Verify via carrier AVS
            </label>

            <div class="flex justify-end">
                <Button label="Save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>
