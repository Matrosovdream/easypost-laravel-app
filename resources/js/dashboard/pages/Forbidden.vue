<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useAuthStore } from '@dashboard/stores/auth';
import client from '@shared/api/client';

const auth = useAuthStore();
const route = useRoute();

const role = computed(() => auth.user?.roles[0]?.name ?? 'Viewer');

const requesting = ref(false);
const requested = ref(false);
const error = ref<string | null>(null);

async function requestAccess() {
    requesting.value = true;
    error.value = null;
    try {
        await client.post('/access-requests', {
            requested_permission: route.query.right ?? 'unknown',
            target_url: route.query.from ?? null,
        });
        requested.value = true;
    } catch {
        error.value = 'Could not submit access request.';
    } finally {
        requesting.value = false;
    }
}
</script>

<template>
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-surface-100 text-surface-400 flex items-center justify-center mx-auto">
            <i class="pi pi-lock text-3xl"></i>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-surface-900">You don't have access to this page.</h1>
        <p class="mt-2 text-surface-600">
            You're signed in as <strong>{{ role }}</strong>. This page requires permissions you don't have.
        </p>

        <Message v-if="requested" severity="success" :closable="false" class="mt-6">
            We notified your team admins. They'll follow up soon.
        </Message>
        <Message v-else-if="error" severity="error" :closable="false" class="mt-6">{{ error }}</Message>

        <div class="mt-8 flex items-center justify-center gap-3">
            <router-link to="/dashboard">
                <Button label="Back to dashboard" severity="secondary" outlined />
            </router-link>
            <Button
                v-if="!requested"
                label="Request access"
                icon="pi pi-envelope"
                :loading="requesting"
                @click="requestAccess"
            />
        </div>
    </div>
</template>
