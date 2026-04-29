<script setup lang="ts">
import { ref, onMounted } from 'vue';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi, type PeopleResponse } from '@dashboard/api/settings';

const counts = ref<Record<string, number>>({});
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try {
        const roles = ['manager', 'shipper', 'cs_agent', 'client', 'viewer'] as const;
        const results = await Promise.all(roles.map((r) => settingsApi.peopleByRole(r).catch(() => null)));
        results.forEach((res, i) => {
            const role = roles[i];
            counts.value[role] = (res as PeopleResponse | null)?.data.length ?? 0;
        });
    } finally { loading.value = false; }
}

onMounted(load);

const tiles = [
    { role: 'manager',  label: 'Managers',  to: '/dashboard/admin/people/manager',  icon: 'pi-id-card', color: 'primary' },
    { role: 'shipper',  label: 'Shippers',  to: '/dashboard/admin/people/shipper',  icon: 'pi-box',     color: 'green' },
    { role: 'cs_agent', label: 'CS Agents', to: '/dashboard/admin/people/cs_agent', icon: 'pi-shield',  color: 'orange' },
    { role: 'client',   label: 'Clients',   to: '/dashboard/admin/people/client',   icon: 'pi-building',color: 'cyan' },
    { role: 'viewer',   label: 'Viewers',   to: '/dashboard/admin/people/viewer',   icon: 'pi-eye',     color: 'gray' },
];
</script>

<template>
    <div>
        <PageHeader title="Tenant overview" subtitle="Cross-role governance dashboard for tenant administrators." />

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <router-link
                v-for="t in tiles"
                :key="t.role"
                :to="t.to"
                class="card hover:shadow-md transition-shadow no-underline text-inherit"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full"
                        :style="{ background: `var(--p-${t.color}-100)`, color: `var(--p-${t.color}-700)` }"
                    >
                        <i :class="['pi', t.icon, 'text-xl']"></i>
                    </span>
                    <div>
                        <div class="text-xs text-surface-500">{{ t.label }}</div>
                        <div class="text-2xl font-bold">{{ loading ? '—' : (counts[t.role] ?? 0) }}</div>
                    </div>
                </div>
            </router-link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card">
                <h3 class="text-lg font-semibold mb-2">Quick actions</h3>
                <ul class="space-y-2 text-sm">
                    <li><router-link to="/dashboard/settings/invitations" class="text-primary-600">Pending invitations →</router-link></li>
                    <li><router-link to="/dashboard/settings/audit-log" class="text-primary-600">Recent audit log →</router-link></li>
                    <li><router-link to="/dashboard/settings/policies" class="text-primary-600">Approval policies →</router-link></li>
                    <li><router-link to="/dashboard/settings/billing" class="text-primary-600">Billing snapshot →</router-link></li>
                </ul>
            </div>
            <div class="card">
                <h3 class="text-lg font-semibold mb-2">Tenant configuration</h3>
                <ul class="space-y-2 text-sm">
                    <li><router-link to="/dashboard/settings/carriers" class="text-primary-600">Carrier accounts →</router-link></li>
                    <li><router-link to="/dashboard/settings/webhooks" class="text-primary-600">Webhooks →</router-link></li>
                    <li><router-link to="/dashboard/settings/api-keys" class="text-primary-600">API keys →</router-link></li>
                    <li><router-link to="/dashboard/settings/branding" class="text-primary-600">Branding →</router-link></li>
                </ul>
            </div>
        </div>
    </div>
</template>
