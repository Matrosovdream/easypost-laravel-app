<script setup lang="ts">
import { computed } from 'vue';
import Button from 'primevue/button';
import { useAuthStore } from '@dashboard/stores/auth';

const auth = useAuthStore();
const isAdmin = computed(() => auth.primaryRole === 'admin');
</script>

<template>
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
            <i class="pi pi-exclamation-triangle text-3xl"></i>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-surface-900">Your team account is locked.</h1>
        <p class="mt-2 text-surface-600">
            <template v-if="isAdmin">
                Billing needs attention. Update your payment method to unlock the dashboard.
            </template>
            <template v-else>
                Your team admin needs to resolve a billing issue. Please reach out to them.
            </template>
        </p>

        <div class="mt-8 flex items-center justify-center gap-3">
            <router-link v-if="isAdmin" to="/dashboard/settings/billing">
                <Button label="Update payment method" icon="pi pi-credit-card" />
            </router-link>
            <Button
                v-else
                label="Email your admin"
                severity="secondary"
                outlined
                icon="pi pi-envelope"
                @click="() => (window.location.href = 'mailto:')"
            />
        </div>
    </div>
</template>
