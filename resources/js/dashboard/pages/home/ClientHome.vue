<script setup lang="ts">
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import KpiCard from '@dashboard/components/home/KpiCard.vue';
import { useAuthStore } from '@dashboard/stores/auth';

const auth = useAuthStore();
</script>

<template>
    <div>
        <PageHeader
            :title="`Welcome, ${auth.user?.name?.split(' ')[0] ?? 'there'}`"
            subtitle="Your shipments with us."
        >
            <template #actions>
                <router-link to="/dashboard/shipments">
                    <Button label="View all shipments" icon="pi pi-box" />
                </router-link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Shipments this month" value="—" icon="pi-box" />
            <KpiCard label="Delivered" value="—" icon="pi-check-circle" tone="positive" />
            <KpiCard label="In transit" value="—" icon="pi-truck" />
            <KpiCard label="Exceptions" value="—" icon="pi-exclamation-triangle" tone="negative" />
        </div>

        <div class="mt-6 bg-white rounded-xl border border-surface-200 p-5">
            <h3 class="font-semibold text-surface-900">Recent activity</h3>
            <p class="text-sm text-surface-500 mt-1">Your most recent shipments and their status.</p>
        </div>
    </div>
</template>
