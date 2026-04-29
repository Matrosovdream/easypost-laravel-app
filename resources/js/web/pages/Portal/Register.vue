<script setup lang="ts">
import { ref } from 'vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useSeo } from '@web/composables/useSeo';
import client, { ensureCsrf } from '@shared/api/client';

useSeo({
    title: 'Create account — ShipDesk',
    description: 'Start a 14-day free trial. Create your team, invite your people, ship smarter.',
});

const form = ref({ name: '', team: '', email: '', password: '' });
const loading = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (!form.value.name || !form.value.team || !form.value.email || !form.value.password) {
        error.value = 'Please fill out all fields.';
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        await ensureCsrf();
        await client.post('/auth/register', form.value);
        window.location.href = '/dashboard';
    } catch (e: unknown) {
        const err = e as { response?: { status: number; data?: { message?: string } } };
        error.value = err.response?.data?.message ?? 'Could not create account.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Card>
        <template #title>
            <h1 class="text-xl font-semibold text-center">Create your team</h1>
        </template>
        <template #subtitle>
            <p class="text-center text-sm text-surface-500">14-day free trial · No card required</p>
        </template>
        <template #content>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">Your name</label>
                    <InputText v-model="form.name" class="w-full" autocomplete="name" required />
                </div>
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">Team / company name</label>
                    <InputText v-model="form.team" class="w-full" required />
                </div>
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">Work email</label>
                    <InputText v-model="form.email" type="email" class="w-full" autocomplete="email" required />
                </div>
                <div>
                    <label class="text-sm font-medium text-surface-700 mb-1 block">Password</label>
                    <Password v-model="form.password" toggle-mask class="w-full" input-class="w-full" autocomplete="new-password" required />
                </div>

                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

                <Button type="submit" label="Create account" class="w-full" :loading="loading" />

                <p class="text-center text-xs text-surface-500">
                    By creating an account you agree to our
                    <router-link to="/portal/terms" class="underline">Terms</router-link>
                    and
                    <router-link to="/portal/privacy" class="underline">Privacy Policy</router-link>.
                </p>
            </form>
        </template>
        <template #footer>
            <div class="text-center text-sm">
                Already have an account?
                <router-link to="/portal/login" class="text-primary-600 hover:underline">Sign in</router-link>
            </div>
        </template>
    </Card>
</template>
