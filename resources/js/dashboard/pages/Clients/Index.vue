<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { clientsApi, type ClientItem } from '@dashboard/api/clients';

const router = useRouter();
const rows = ref<ClientItem[]>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await clientsApi.list()).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Clients" subtitle="3PL tenants you ship on behalf of.">
            <template #actions>
                <router-link to="/dashboard/clients/create">
                    <Button label="New client" icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="card">
            <DataTable
                :value="rows"
                :loading="loading"
                striped-rows
                selection-mode="single"
                data-key="id"
                @row-click="(e) => router.push(`/dashboard/clients/${(e.data as ClientItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="company_name" header="Company" />
                <Column header="Contact">
                    <template #body="s">
                        <div>{{ s.data.contact_name ?? '—' }}</div>
                        <div class="text-xs text-surface-500">{{ s.data.contact_email ?? '' }}</div>
                    </template>
                </Column>
                <Column header="FlexRate">
                    <template #body="s">{{ s.data.flexrate_markup_pct }}%</template>
                </Column>
                <Column header="Billing">
                    <template #body="s">{{ s.data.billing_mode }} · Net {{ s.data.credit_terms_days }}</template>
                </Column>
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No clients yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
