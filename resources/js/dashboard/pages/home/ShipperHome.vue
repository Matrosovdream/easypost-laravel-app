<script setup lang="ts">
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import KpiCard from '@dashboard/components/home/KpiCard.vue';
import { useAuthStore } from '@dashboard/stores/auth';
import { useCountsStore } from '@dashboard/stores/counts';

const auth = useAuthStore();
const counts = useCountsStore();
</script>

<template>
    <div>
        <PageHeader
            :title="`Ready to ship, ${auth.user?.name?.split(' ')[0] ?? 'Shipper'}?`"
            subtitle="Print queue, pickups, and your shipments for today."
        >
            <template #actions>
                <router-link to="/dashboard/print">
                    <Button label="Open print queue" icon="pi pi-print" />
                </router-link>
                <router-link to="/dashboard/shipments/new">
                    <Button label="New shipment" severity="secondary" outlined icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Ready to print" :value="counts.counts.printReady" icon="pi-print" />
            <KpiCard label="Assigned to me" value="—" icon="pi-box" />
            <KpiCard label="Pickups today" value="—" icon="pi-calendar" />
            <KpiCard label="Shipped today" value="—" icon="pi-send" tone="positive" />
        </div>

        <div class="mt-6 bg-white rounded-xl border border-surface-200 p-5">
            <h3 class="font-semibold text-surface-900">My queue</h3>
            <p class="text-sm text-surface-500 mt-1">
                List of shipments assigned to you (step 05b).
            </p>
        </div>
    </div>
</template>
