<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi, type TeamUser } from '@dashboard/api/settings';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const rows = ref<TeamUser[]>([]);
const loading = ref(false);
const showInvite = ref(false);
const lastPin = ref<string | null>(null);
const lastInviteToken = ref<string | null>(null);

const roles = [
    { label: 'Admin', value: 'admin' },
    { label: 'Manager', value: 'manager' },
    { label: 'Shipper', value: 'shipper' },
    { label: 'CS Agent', value: 'cs_agent' },
    { label: 'Client', value: 'client' },
    { label: 'Viewer', value: 'viewer' },
];

const inviteForm = reactive({ email: '', role_slug: 'shipper' });
const inviteErr = ref<string | null>(null);
const inviting = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await settingsApi.users()).data; }
    finally { loading.value = false; }
}

async function invite(): Promise<void> {
    inviteErr.value = null;
    inviting.value = true;
    try {
        const res = await settingsApi.inviteUser({ email: inviteForm.email, role_slug: inviteForm.role_slug });
        lastInviteToken.value = res.token;
        toast.success(`Invite sent to ${inviteForm.email}`);
        inviteForm.email = '';
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        inviteErr.value = r.response?.data?.message ?? 'Could not invite.';
    } finally { inviting.value = false; }
}

async function changeRole(u: TeamUser, role: string): Promise<void> {
    try {
        await settingsApi.changeRole(u.id, role);
        toast.success(`${u.name} is now ${role}`);
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        toast.error(r.response?.data?.message ?? 'Could not change role.');
    }
}

async function toggleActive(u: TeamUser): Promise<void> {
    try {
        if (u.is_active) await settingsApi.disableUser(u.id);
        else await settingsApi.enableUser(u.id);
        toast.success(`${u.name} ${u.is_active ? 'disabled' : 'enabled'}`);
        await load();
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        toast.error(r.response?.data?.message ?? 'Could not change status.');
    }
}

async function regen(u: TeamUser): Promise<void> {
    if (!confirm(`Regenerate PIN for ${u.name}? Their current PIN will stop working.`)) return;
    try {
        const res = await settingsApi.regeneratePin(u.id);
        lastPin.value = `${u.name}: ${res.pin}`;
        toast.success(`New PIN for ${u.name}: ${res.pin}`);
    } catch { toast.error('Could not regenerate PIN.'); }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Users & roles" subtitle="Invite, assign roles, rotate PINs.">
            <template #actions>
                <Button label="Invite user" icon="pi pi-user-plus" @click="showInvite = true" />
            </template>
        </PageHeader>

        <Message v-if="lastPin" severity="warn" :closable="true" @close="lastPin = null" class="mb-4">
            <div class="font-mono font-bold">{{ lastPin }}</div>
            <div class="text-xs">Write this down now — it won't be shown again.</div>
        </Message>

        <Message v-if="lastInviteToken" severity="info" :closable="true" @close="lastInviteToken = null" class="mb-4">
            Accept link: <code class="font-mono">/portal/accept-invite/{{ lastInviteToken }}</code>
        </Message>

        <div class="card">
            <DataTable :value="rows" :loading="loading" striped-rows data-key="id">
                <Column header="User">
                    <template #body="s">
                        <div>{{ s.data.name }}</div>
                        <div class="text-xs text-surface-500">{{ s.data.email }}</div>
                    </template>
                </Column>
                <Column header="Role" style="width: 10rem">
                    <template #body="s">
                        <Select
                            :model-value="s.data.role_slug"
                            :options="roles"
                            option-label="label"
                            option-value="value"
                            @update:model-value="(v) => changeRole(s.data, v)"
                        />
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="s">
                        <Tag v-if="s.data.is_active" value="Active" severity="success" />
                        <Tag v-else value="Disabled" severity="danger" />
                    </template>
                </Column>
                <Column header="Last login">
                    <template #body="s">{{ s.data.last_login_at ? new Date(s.data.last_login_at).toLocaleString() : '—' }}</template>
                </Column>
                <Column header="Actions" style="width: 18rem">
                    <template #body="s">
                        <div class="flex gap-2">
                            <Button size="small" :label="s.data.is_active ? 'Disable' : 'Enable'" severity="secondary" outlined @click="toggleActive(s.data)" />
                            <Button size="small" label="New PIN" severity="warn" outlined @click="regen(s.data)" />
                        </div>
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No users.</div>
                </template>
            </DataTable>
        </div>

        <Dialog v-model:visible="showInvite" header="Invite user" modal class="w-full max-w-md">
            <div class="space-y-4">
                <Message v-if="inviteErr" severity="error" :closable="false">{{ inviteErr }}</Message>
                <div>
                    <label class="block text-sm font-medium mb-1">Email *</label>
                    <InputText v-model="inviteForm.email" type="email" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <Select v-model="inviteForm.role_slug" :options="roles" option-label="label" option-value="value" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text @click="showInvite = false" />
                <Button label="Send invite" :loading="inviting" @click="invite" />
            </template>
        </Dialog>
    </div>
</template>
