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
            :title="`Good day, ${auth.user?.name?.split(' ')[0] ?? 'Admin'}`"
            subtitle="System health, team usage, and spend at a glance."
        >
            <template #actions>
                <router-link to="/dashboard/settings/users">
                    <Button label="Invite user" icon="pi pi-user-plus" />
                </router-link>
                <router-link to="/dashboard/settings/billing">
                    <Button label="Billing" severity="secondary" outlined icon="pi pi-credit-card" />
                </router-link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <KpiCard label="Shipments this month" value="—" icon="pi-box" trend="placeholder" />
            <KpiCard label="Spend this month" value="$—" icon="pi-wallet" trend="placeholder" />
            <KpiCard label="Users" value="—" icon="pi-users" />
            <KpiCard label="Uptime (90d)" value="99.95%" icon="pi-check-circle" tone="positive" />
        </div>

        <div class="mt-6 grid lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Spend by carrier</h3>
                <p class="text-sm text-surface-500 mt-1">Widget lands with reports module.</p>
                <div class="mt-4 h-48 flex items-center justify-center text-surface-300">
                    <i class="pi pi-chart-pie text-5xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-surface-200 p-5">
                <h3 class="font-semibold text-surface-900">Recent audit events</h3>
                <p class="text-sm text-surface-500 mt-1">Wires to audit_logs in step 05h.</p>
                <div class="mt-4 space-y-2 text-sm text-surface-500">
                    <div class="flex items-center gap-2"><i class="pi pi-circle-fill text-xs"></i> Placeholder entry</div>
                    <div class="flex items-center gap-2"><i class="pi pi-circle-fill text-xs"></i> Placeholder entry</div>
                </div>
            </div>
        </div>
    </div>
</template>
