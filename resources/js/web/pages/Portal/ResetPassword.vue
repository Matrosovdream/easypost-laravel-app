<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import Card from 'primevue/card';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Set new password — ShipDesk',
    description: 'Set a new password for your ShipDesk account.',
    noindex: true,
});

const route = useRoute();
const token = String(route.params.token ?? '');
const email = String(route.query.email ?? '');

const password = ref('');
const confirmPassword = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (password.value !== confirmPassword.value) {
        error.value = 'Passwords do not match.';
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post('/auth/reset-password', {
            token,
            email,
            password: password.value,
            password_confirmation: confirmPassword.value,
        });
        window.location.href = '/portal/login';
    } catch {
        error.value = 'Could not reset password. Your link may have expired.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Set a new password</h1>
        </template>
        <template #content>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">New password</label>
                    <Password v-model="password" toggle-mask class="w-full" input-class="w-full" required />
                </div>
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">Confirm new password</label>
                    <Password v-model="confirmPassword" :feedback="false" toggle-mask class="w-full" input-class="w-full" required />
                </div>
                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                <Button type="submit" label="Save & sign in" class="w-full" :loading="loading" />
            </form>
        </template>
    </Card>
</template>
