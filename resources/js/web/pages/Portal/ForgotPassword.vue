<script setup lang="ts">
import { ref } from 'vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Forgot password — ShipDesk',
    description: 'Reset your founder password for ShipDesk.',
    noindex: true,
});

const email = ref('');
const sent = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (!email.value) { error.value = 'Enter your email.'; return; }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post('/auth/forgot-password', { email: email.value });
        sent.value = true;
    } catch {
        error.value = 'Could not send reset link.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Reset your password</h1>
        </template>
        <template #subtitle>
            <p class="text-center text-sm text-surface-500">
                Enter your email. We'll send you a reset link.
            </p>
        </template>
        <template #content>
            <div v-if="sent">
                <Message severity="success" :closable="false">
                    If an account exists for that email, a reset link is on the way. Check your inbox.
                </Message>
            </div>
            <form v-else class="space-y-4" @submit.prevent="submit">
                <InputText v-model="email" type="email" placeholder="you@company.com" class="w-full" required />
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <Button type="submit" label="Send reset link" class="w-full" :loading="loading" />
            </form>
        </template>
        <template #footer>
            <div class="text-center text-sm">
                Back to
                <router-link to="/portal/login" class="text-primary-600 hover:underline">sign in</router-link>
            </div>
        </template>
    </Card>
</template>
