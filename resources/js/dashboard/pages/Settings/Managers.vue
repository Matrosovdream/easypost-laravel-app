<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi, type ManagerRow } from '@dashboard/api/settings';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const rows = ref<ManagerRow[]>([]);
const loading = ref(false);
const busyId = ref<number | null>(null);
const loadErr = ref<string | null>(null);

async function load(): Promise<void> {
    loading.value = true;
    loadErr.value = null;
    try {
        rows.value = (await settingsApi.managers()).data;
    } catch (e: unknown) {
        const r = e as { response?: { status?: number; data?: { message?: string } } };
        loadErr.value = r.response?.data?.message
            ?? `Could not load managers (HTTP ${r.response?.status ?? '?'}).`;
    } finally { loading.value = false; }
}

async function toggleActive(m: ManagerRow): Promise<void> {
    busyId.value = m.id;
    try {
        if (m.is_active) await settingsApi.disableUser(m.id);
        else await settingsApi.enableUser(m.id);
        toast.success(`${m.name} ${m.is_active ? 'disabled' : 'enabled'}`);
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        toast.error(r.response?.data?.message ?? 'Could not change status.');
    } finally { busyId.value = null; }
}

function fmtDate(v: string | null): string {
    return v ? new Date(v).toLocaleString() : '—';
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Managers" subtitle="Performance and status for every Manager on the team." />

        <Message v-if="loadErr" severity="error" :closable="false" class="mb-4">{{ loadErr }}</Message>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column header="Manager">
                    <template #body="s">
                        <div class="font-medium">{{ s.data.name }}</div>
                        <div class="text-xs text-surface-500">{{ s.data.email }}</div>
                    </template>
                </Column>
                <Column header="Status" style="width: 8rem">
                    <template #body="s">
                        <Tag v-if="s.data.is_active" value="Active" severity="success" />
                        <Tag v-else value="Disabled" severity="danger" />
                    </template>
                </Column>
                <Column header="Assigned" style="width: 7rem">
                    <template #body="s">{{ s.data.shipments_assigned }}</template>
                </Column>
                <Column header="Approved (30d)" style="width: 9rem">
                    <template #body="s">{{ s.data.shipments_approved_30d }}</template>
                </Column>
                <Column header="Approved (all)" style="width: 9rem">
                    <template #body="s">{{ s.data.shipments_approved }}</template>
                </Column>
                <Column header="Pending approvals" style="width: 11rem">
                    <template #body="s">
                        <Tag
                            :value="String(s.data.approvals_pending)"
                            :severity="s.data.approvals_pending > 0 ? 'warn' : 'secondary'"
                        />
                    </template>
                </Column>
                <Column header="Last login" style="width: 12rem">
                    <template #body="s">{{ fmtDate(s.data.last_login_at) }}</template>
                </Column>
                <Column header="Joined" style="width: 12rem">
                    <template #body="s">{{ fmtDate(s.data.joined_at ?? s.data.created_at) }}</template>
                </Column>
                <Column header="Actions" style="width: 9rem">
                    <template #body="s">
                        <Button
                            size="small"
                            :label="s.data.is_active ? 'Disable' : 'Enable'"
                            :severity="s.data.is_active ? 'danger' : 'success'"
                            outlined
                            :loading="busyId === s.data.id"
                            @click="toggleActive(s.data)"
                        />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No managers yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
