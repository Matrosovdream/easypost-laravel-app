<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '@dashboard/layouts/DashboardLayout.vue';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useAuthStore } from '@dashboard/stores/auth';
import { useCountsStore } from '@dashboard/stores/counts';

const auth = useAuthStore();
const counts = useCountsStore();

onMounted(async () => {
    if (!auth.loaded) await auth.fetchMe();
    if (auth.isAuthenticated) await counts.fetch();
});
</script>

<template>
    <DashboardLayout>
        <router-view />
    </DashboardLayout>
    <Toast position="bottom-right" />
    <ConfirmDialog />
</template>
