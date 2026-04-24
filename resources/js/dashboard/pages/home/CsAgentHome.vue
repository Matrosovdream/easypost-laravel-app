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
            :title="`Good day, ${auth.user?.name?.split(' ')[0] ?? 'Support'}`"
            subtitle="Exceptions, returns, and claims needing your attention."
        >
            <template #actions>
                <router-link to="/dashboard/exceptions">
                    <Button label="Open exceptions" icon="pi pi-exclamation-triangle" severity="warn" />
                </router-link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Open exceptions" :value="counts.counts.exceptionsCount" icon="pi-exclamation-triangle" tone="negative" />
            <KpiCard label="Returns pending" :value="counts.counts.returnsCount" icon="pi-reply" />
            <KpiCard label="Claims in review" :value="counts.counts.claimsCount" icon="pi-shield" />
            <KpiCard label="Avg resolution" value="—" icon="pi-clock" />
        </div>

        <div class="mt-6 grid lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Top exceptions</h3>
                <p class="text-sm text-surface-500 mt-1">Stuck-in-transit, lost, damaged (step 05d).</p>
            </div>
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Claims Autopilot feed</h3>
                <p class="text-sm text-surface-500 mt-1">Flags awaiting confirm/dismiss (step 05d).</p>
            </div>
        </div>
    </div>
</template>
