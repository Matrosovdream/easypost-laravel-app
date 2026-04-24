<script setup lang="ts">
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Contact — ShipDesk',
    description: 'Talk to sales, ask a question, or request a demo. We respond within one business day.',
});

const form = ref({
    name: '',
    email: '',
    company: '',
    topic: 'sales',
    message: '',
});

const topics = [
    { label: 'Sales & pricing', value: 'sales' },
    { label: 'Demo request', value: 'demo' },
    { label: 'Partnerships', value: 'partnerships' },
    { label: 'Support', value: 'support' },
    { label: 'Other', value: 'other' },
];

const status = ref<'idle' | 'sending' | 'sent' | 'error'>('idle');
const errorMsg = ref<string | null>(null);

async function submit() {
    if (!form.value.name || !form.value.email || !form.value.message) {
        errorMsg.value = 'Please fill out all required fields.';
        status.value = 'error';
        return;
    }
    status.value = 'sending';
    errorMsg.value = null;
    try {
        await ensureCsrf();
        await client.post('/rest/public/contact', form.value, { baseURL: '' });
        status.value = 'sent';
        form.value = { name: '', email: '', company: '', topic: 'sales', message: '' };
    } catch (e: unknown) {
        const err = e as { response?: { status: number } };
        errorMsg.value = err.response?.status === 429
            ? 'Too many submissions. Please wait a few minutes.'
            : 'Could not send. Please try again.';
        status.value = 'error';
    }
}
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 text-center max-w-2xl">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900">Get in touch.</h1>
            <p class="mt-4 text-lg text-surface-600">
                We respond within one business day. For urgent support, email
                <a href="mailto:help@shipdesk.local" class="text-primary-600 hover:underline">help@shipdesk.local</a>.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto grid md:grid-cols-5 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="flex gap-3">
                        <i class="pi pi-envelope text-primary-500 text-xl mt-1"></i>
                        <div>
                            <div class="font-semibold text-surface-900">Email</div>
                            <div class="text-sm text-surface-600">sales@shipdesk.local</div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <i class="pi pi-phone text-primary-500 text-xl mt-1"></i>
                        <div>
                            <div class="font-semibold text-surface-900">Phone</div>
                            <div class="text-sm text-surface-600">+1 (415) 555-0102</div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <i class="pi pi-map-marker text-primary-500 text-xl mt-1"></i>
                        <div>
                            <div class="font-semibold text-surface-900">Office</div>
                            <div class="text-sm text-surface-600">San Francisco, CA</div>
                        </div>
                    </div>
                </div>

                <form class="md:col-span-3 space-y-4" @submit.prevent="submit">
                    <div v-if="status === 'sent'">
                        <Message severity="success" :closable="false">
                            Thanks — we got your message and will reply within one business day.
                        </Message>
                    </div>
                    <div v-else-if="status === 'error' && errorMsg">
                        <Message severity="error" :closable="false">{{ errorMsg }}</Message>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-surface-700 mb-1 block">Name *</label>
                            <InputText v-model="form.name" class="w-full" required />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-surface-700 mb-1 block">Email *</label>
                            <InputText v-model="form.email" type="email" class="w-full" required />
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-surface-700 mb-1 block">Company</label>
                            <InputText v-model="form.company" class="w-full" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-surface-700 mb-1 block">Topic</label>
                            <Select v-model="form.topic" :options="topics" option-label="label" option-value="value" class="w-full" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-surface-700 mb-1 block">Message *</label>
                        <Textarea v-model="form.message" rows="5" class="w-full" required />
                    </div>

                    <Button type="submit" label="Send message" :loading="status === 'sending'" />
                </form>
            </div>
        </div>
    </section>
</template>
