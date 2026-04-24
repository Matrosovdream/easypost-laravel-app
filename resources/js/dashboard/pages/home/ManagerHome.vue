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
            :title="`Good day, ${auth.user?.name?.split(' ')[0] ?? 'Manager'}`"
            subtitle="Approvals, ops throughput, and team assignments."
        >
            <template #actions>
                <router-link to="/dashboard/approvals">
                    <Button label="Review approvals" icon="pi pi-check-square" />
                </router-link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Pending approvals" :value="counts.counts.approvalsCount" icon="pi-check-square" />
            <KpiCard label="Exceptions" :value="counts.counts.exceptionsCount" icon="pi-exclamation-triangle" tone="negative" />
            <KpiCard label="Shipments today" value="—" icon="pi-box" />
            <KpiCard label="On-time rate" value="—" icon="pi-clock" />
        </div>

        <div class="mt-6 grid lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Approval queue</h3>
                <p class="text-sm text-surface-500 mt-1">Top of queue appears here (step 05b).</p>
            </div>
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Team throughput</h3>
                <p class="text-sm text-surface-500 mt-1">Bar chart by shipper, last 7 days.</p>
            </div>
        </div>
    </div>
</template>
