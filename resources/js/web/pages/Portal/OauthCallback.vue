<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import Button from 'primevue/button';
import { useSeo } from '@web/composables/useSeo';

useSeo({
    title: 'Signing in — ShipDesk',
    description: 'Finishing single sign-on authentication.',
    noindex: true,
});

const route = useRoute();
const status = ref<'pending' | 'done' | 'error'>('pending');

onMounted(async () => {
    const provider = String(route.params.provider ?? '');
    const code = String(route.query.code ?? '');
    if (!provider || !code) {
        status.value = 'error';
        return;
    }
    setTimeout(() => {
        status.value = 'done';
        window.location.href = '/dashboard';
    }, 800);
});
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col items-center gap-4 text-center py-4">
                <template v-if="status === 'pending'">
                    <ProgressSpinner style="width: 48px; height: 48px" stroke-width="4" />
                    <p class="text-surface-600">Completing single sign-on…</p>
                </template>
                <template v-else-if="status === 'done'">
                    <i class="pi pi-check-circle text-5xl text-green-500"></i>
                    <p class="text-surface-600">Signed in. Redirecting…</p>
                </template>
                <template v-else>
                    <Message severity="error" :closable="false">Single sign-on failed. Please try again.</Message>
                    <router-link to="/portal/login">
                        <Button label="Back to sign in" />
                    </router-link>
                </template>
            </div>
        </template>
    </Card>
</template>
