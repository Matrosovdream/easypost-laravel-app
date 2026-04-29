<script setup lang="ts">
import { ref } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Verify email — ShipDesk',
    description: 'Confirm your email to finish setting up your ShipDesk account.',
    noindex: true,
});

const resending = ref(false);
const resent = ref(false);
const error = ref<string | null>(null);

async function resend() {
    resending.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post('/auth/verification-notification');
        resent.value = true;
    } catch {
        error.value = 'Could not resend right now. Try again in a minute.';
    } finally {
        resending.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Verify your email</h1>
        </template>
        <template #content>
            <div class="space-y-4 text-center">
                <i class="pi pi-envelope text-5xl text-primary-500"></i>
                <p class="text-surface-600">
                    We've sent a verification link to your email. Click the link to activate your account.
                </p>

                <Message v-if="resent" severity="success" :closable="false">
                    A new verification email is on the way.
                </Message>
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

                <Button label="Resend verification" severity="secondary" outlined :loading="resending" @click="resend" />
            </div>
        </template>
        <template #footer>
            <div class="text-center text-sm">
                Wrong account?
                <router-link to="/portal/login" class="text-primary-600 hover:underline">Sign in as someone else</router-link>
            </div>
        </template>
    </Card>
</template>
