<script setup lang="ts">
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import KpiCard from '@dashboard/components/home/KpiCard.vue';
import { useAuthStore } from '@dashboard/stores/auth';

const auth = useAuthStore();
</script>

<template>
    <div>
        <PageHeader
            :title="`Hi, ${auth.user?.name?.split(' ')[0] ?? 'there'}`"
            subtitle="Read-only view. You can browse, but cannot edit."
        />

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Shipments" value="—" icon="pi-box" />
            <KpiCard label="Spend (mo)" value="$—" icon="pi-wallet" />
            <KpiCard label="On-time" value="—" icon="pi-clock" />
            <KpiCard label="Exceptions" value="—" icon="pi-exclamation-triangle" />
        </div>

        <div class="mt-6 bg-white rounded-xl border border-surface-200 p-5">
            <h3 class="font-semibold text-surface-900">Reports</h3>
            <p class="text-sm text-surface-500 mt-1">Exports and saved views (read-only).</p>
        </div>
    </div>
</template>
