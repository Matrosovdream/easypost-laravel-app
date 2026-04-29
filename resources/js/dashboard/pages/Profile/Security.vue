<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { profileApi } from '@dashboard/api/profile';

const rows = ref<Array<{ id: string; user_agent: string; ip: string; last_activity: string; current: boolean }>>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await profileApi.sessions()).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Security" subtitle="Active sessions and two-factor auth." />

        <div class="card mb-6">
            <h3 class="font-semibold mb-2">Two-factor authentication</h3>
            <p class="text-sm text-surface-600 mb-4">
                2FA is optional but recommended. Full enrollment flow lands with P1.
            </p>
            <Button label="Enable 2FA" severity="secondary" icon="pi pi-shield" disabled />
        </div>

        <div class="card">
            <h3 class="font-semibold mb-3">Active sessions</h3>
            <DataTable :value="rows" :loading="loading" striped-rows>
                <Column header="Device">
                    <template #body="s">
                        <div class="text-sm">{{ s.data.user_agent ?? 'Unknown' }}</div>
                    </template>
                </Column>
                <Column field="ip" header="IP" />
                <Column header="Last active">
                    <template #body="s">{{ new Date(s.data.last_activity).toLocaleString() }}</template>
                </Column>
                <Column header="Status">
                    <template #body="s">
                        <Tag v-if="s.data.current" value="This session" severity="success" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No sessions.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
