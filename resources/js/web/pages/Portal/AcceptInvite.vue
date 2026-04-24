<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import Card from 'primevue/card';
import InputOtp from 'primevue/inputotp';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Accept invite — ShipDesk',
    description: 'Accept your team invitation and set your PIN.',
    noindex: true,
});

const route = useRoute();
const token = String(route.params.token ?? '');

const pin = ref('');
const confirmPin = ref('');
const step = ref<'choose' | 'confirm'>('choose');
const loading = ref(false);
const error = ref<string | null>(null);

const team = ref({ name: 'Acme Shipping', role: 'Shipper', invitedBy: 'Dana Wu' });

function next() {
    if (pin.value.length !== 4) {
        error.value = 'Pick a 4-digit PIN.';
        return;
    }
    error.value = null;
    step.value = 'confirm';
}

async function accept() {
    if (confirmPin.value !== pin.value) {
        error.value = 'PINs do not match.';
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post(`/auth/accept-invite/${token}`, { pin: pin.value });
        window.location.href = '/dashboard';
    } catch {
        error.value = 'Could not accept invite. It may have expired.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Join {{ team.name }}</h1>
        </template>
        <template #subtitle>
            <p class="text-center text-sm text-surface-500">
                You've been invited as a <strong>{{ team.role }}</strong> by {{ team.invitedBy }}
            </p>
        </template>
        <template #content>
            <div v-if="step === 'choose'" class="flex flex-col items-center gap-5">
                <p class="text-sm text-surface-600 text-center">
                    Pick a 4-digit PIN to sign in. You'll use this every time.
                </p>
                <InputOtp v-model="pin" :length="4" integer-only mask />
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <Button label="Continue" class="w-full" @click="next" />
            </div>
            <div v-else class="flex flex-col items-center gap-5">
                <p class="text-sm text-surface-600 text-center">Confirm your PIN</p>
                <InputOtp v-model="confirmPin" :length="4" integer-only mask />
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <div class="flex gap-2 w-full">
                    <Button label="Back" severity="secondary" outlined class="flex-1" @click="step = 'choose'" />
                    <Button label="Accept & sign in" class="flex-1" :loading="loading" @click="accept" />
                </div>
            </div>
        </template>
    </Card>
</template>
