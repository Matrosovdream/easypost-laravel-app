<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { pickupsApi, type PickupItem } from '@dashboard/api/operations';
import { useRouter } from 'vue-router';

const router = useRouter();
const rows = ref<PickupItem[]>([]);
const loading = ref(false);

function money(c: number | null): string {
    return c == null ? '—' : `$${(c / 100).toFixed(2)}`;
}

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await pickupsApi.list()).data; }
    finally { loading.value = false; }
}

function statusSeverity(status: string): 'info' | 'success' | 'warn' | 'danger' {
    if (status === 'scheduled') return 'success';
    if (status === 'cancelled') return 'danger';
    if (status === 'creating') return 'warn';
    return 'info';
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Pickups" subtitle="Schedule carrier pickups, track confirmations.">
            <template #actions>
                <router-link to="/dashboard/pickups/schedule">
                    <Button label="Schedule pickup" icon="pi pi-calendar-plus" />
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
                @row-click="(e) => router.push(`/dashboard/pickups/${(e.data as PickupItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" :severity="statusSeverity(s.data.status)" /></template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="confirmation" header="Confirmation" />
                <Column header="Window">
                    <template #body="s">
                        {{ new Date(s.data.min_datetime).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) }}
                        –
                        {{ new Date(s.data.max_datetime).toLocaleString([], { hour: 'numeric', minute: '2-digit' }) }}
                    </template>
                </Column>
                <Column header="Address">
                    <template #body="s">
                        <span v-if="s.data.address">{{ s.data.address.name }} · {{ [s.data.address.city, s.data.address.state].filter(Boolean).join(', ') }}</span>
                    </template>
                </Column>
                <Column header="Cost">
                    <template #body="s">{{ money(s.data.cost_cents) }}</template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No pickups yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
