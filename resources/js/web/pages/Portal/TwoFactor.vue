<script setup lang="ts">
import { ref } from 'vue';
import Card from 'primevue/card';
import InputOtp from 'primevue/inputotp';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Two-factor auth — ShipDesk',
    description: 'Enter your 6-digit authenticator code.',
    noindex: true,
});

const code = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (code.value.length !== 6) {
        error.value = 'Enter your 6-digit code.';
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post('/auth/two-factor-challenge', { code: code.value });
        window.location.href = '/dashboard';
    } catch {
        error.value = 'Invalid code. Try again.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Two-factor auth</h1>
        </template>
        <template #subtitle>
            <p class="text-center text-sm text-surface-500">
                Enter the 6-digit code from your authenticator app.
            </p>
        </template>
        <template #content>
            <form class="flex flex-col items-center gap-4" @submit.prevent="submit">
                <InputOtp v-model="code" :length="6" integer-only />
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <Button type="submit" label="Verify" class="w-full" :loading="loading" />
                <router-link to="/portal/login" class="text-xs text-surface-500 hover:underline">
                    Use a backup code instead
                </router-link>
            </form>
        </template>
    </Card>
</template>
