<script setup lang="ts">
import { ref } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputOtp from 'primevue/inputotp';
import Message from 'primevue/message';
import client, { ensureCsrf } from '@shared/api/client';

type PinLoginResponse = {
    redirect: string;
    user: {
        id: number;
        email: string;
        name: string;
        roles: Array<{ id: number; slug: string; name: string }>;
        permissions: string[];
        current_team: { id: number; name: string; plan: string; mode: string; status: string } | null;
    };
};

const pin = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (pin.value.length < 4) { error.value = 'Enter your PIN'; return; }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        const res = await client.post<PinLoginResponse>('/auth/pin-login', { pin: pin.value });

        // Seed the dashboard auth cache so it doesn't race the server session
        // cookie propagation after the full-page redirect.
        try {
            sessionStorage.setItem('shipdesk.me', JSON.stringify(res.data.user));
        } catch {
            // sessionStorage unavailable (private mode, etc.) — dashboard will fall
            // back to /api/auth/me.
        }

        window.location.href = res.data.redirect ?? '/dashboard';
    } catch (e: unknown) {
        const err = e as { response?: { status: number; data: { message?: string } } };
        if (err.response?.status === 422) error.value = 'Invalid PIN.';
        else if (err.response?.status === 429) error.value = 'Too many attempts. Try again later.';
        else error.value = 'Something went wrong. Please retry.';
        pin.value = '';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Welcome back</h1>
        </template>
        <template #subtitle>
            <p class="text-center text-sm text-surface-500">Enter your PIN to continue</p>
        </template>
        <template #content>
            <form @submit.prevent="submit" class="flex flex-col items-center gap-4">
                <InputOtp v-model="pin" :length="4" integer-only :disabled="loading" />
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <Button type="submit" label="Sign in" :loading="loading" class="w-full" />
            </form>
        </template>
    </Card>
</template>
