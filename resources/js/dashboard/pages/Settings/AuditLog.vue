<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi, type AuditEntry } from '@dashboard/api/settings';

const rows = ref<AuditEntry[]>([]);
const loading = ref(false);
const filter = ref('');

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await settingsApi.auditLog({ action: filter.value || undefined })).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Audit log" subtitle="Significant actions taken in this tenant." />

        <div class="card mb-4 flex gap-3">
            <InputText v-model="filter" placeholder="Filter by action prefix, e.g. auth." class="flex-1" @keyup.enter="load" />
            <Button label="Apply" severity="secondary" @click="load" />
        </div>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="action" header="Action" />
                <Column header="User">
                    <template #body="s">
                        <span v-if="s.data.user">{{ s.data.user.name }}</span>
                        <span v-else class="text-surface-500">—</span>
                    </template>
                </Column>
                <Column header="Subject">
                    <template #body="s">
                        <span v-if="s.data.subject_type">{{ s.data.subject_type.split('\\').pop() }} #{{ s.data.subject_id }}</span>
                    </template>
                </Column>
                <Column header="IP" field="ip" />
                <Column header="When">
                    <template #body="s">{{ new Date(s.data.created_at).toLocaleString() }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No entries.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
