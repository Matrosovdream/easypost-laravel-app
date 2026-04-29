<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi, type PeopleResponse, type PersonRow } from '@dashboard/api/settings';
import { useToast } from '@dashboard/composables/useToast';

const ROLE_TITLES: Record<string, { title: string; subtitle: string }> = {
    manager:  { title: 'Managers',  subtitle: 'Operations leads — approvals, oversight, hiring.' },
    shipper:  { title: 'Shippers',  subtitle: 'Warehouse staff — picking, packing, label printing.' },
    cs_agent: { title: 'CS Agents', subtitle: 'Customer support — returns, claims, comms.' },
    client:   { title: 'Clients',   subtitle: 'External merchants with portal access.' },
    viewer:   { title: 'Viewers',   subtitle: 'Read-only — accountants, executives.' },
};

const route = useRoute();
const toast = useToast();
const role = computed(() => String(route.params.role ?? 'manager'));
const titleInfo = computed(() => ROLE_TITLES[role.value] ?? { title: 'People', subtitle: '' });

const payload = ref<PeopleResponse | null>(null);
const loading = ref(false);
const busyId = ref<number | null>(null);
const loadErr = ref<string | null>(null);

async function load(): Promise<void> {
    loading.value = true;
    loadErr.value = null;
    try {
        payload.value = await settingsApi.peopleByRole(role.value);
    } catch (e: unknown) {
        const r = e as { response?: { status?: number; data?: { message?: string } } };
        loadErr.value = r.response?.data?.message
            ?? `Could not load ${titleInfo.value.title.toLowerCase()} (HTTP ${r.response?.status ?? '?'}).`;
        payload.value = null;
    } finally {
        loading.value = false;
    }
}

async function toggleActive(p: PersonRow): Promise<void> {
    busyId.value = p.id;
    try {
        if (p.is_active) await settingsApi.disableUser(p.id);
        else await settingsApi.enableUser(p.id);
        toast.success(`${p.name} ${p.is_active ? 'disabled' : 'enabled'}`);
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        toast.error(r.response?.data?.message ?? 'Could not change status.');
    } finally { busyId.value = null; }
}

function fmtDate(v: string | null): string {
    return v ? new Date(v).toLocaleString() : '—';
}

function statValue(p: PersonRow, key: string): number {
    const v = p[key];
    return typeof v === 'number' ? v : 0;
}

watch(role, () => { void load(); });
onMounted(load);
</script>

<template>
    <div>
        <PageHeader :title="titleInfo.title" :subtitle="titleInfo.subtitle" />

        <Message v-if="loadErr" severity="error" :closable="false" class="mb-4">{{ loadErr }}</Message>

        <div class="card">
            <DataTable
                :value="payload?.data ?? []"
                :loading="loading"
                striped-rows
                data-key="id"
            >
                <Column header="Person">
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
                <Column
                    v-for="col in payload?.columns ?? []"
                    :key="col.key"
                    :header="col.label"
                    style="width: 9rem"
                >
                    <template #body="s">
                        <Tag
                            v-if="col.severity_when_gt0 && statValue(s.data, col.key) > 0"
                            :value="String(statValue(s.data, col.key))"
                            :severity="col.severity_when_gt0"
                        />
                        <span v-else>{{ statValue(s.data, col.key) }}</span>
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
                    <div class="text-center py-10 text-surface-500">No {{ titleInfo.title.toLowerCase() }} yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
