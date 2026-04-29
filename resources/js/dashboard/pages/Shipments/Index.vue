<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import StatusPill from '@dashboard/components/shipments/StatusPill.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import type { ShipmentListItem } from '@dashboard/types/shipment';
import { useRouter } from 'vue-router';

const router = useRouter();
const rows = ref<ShipmentListItem[]>([]);
const total = ref(0);
const page = ref(1);
const loading = ref(false);

const q = ref('');
const status = ref<string | null>(null);
const statusOptions = [
    { label: 'All', value: null },
    { label: 'Rated', value: 'rated' },
    { label: 'Pending approval', value: 'pending_approval' },
    { label: 'Purchased', value: 'purchased' },
    { label: 'Packed', value: 'packed' },
    { label: 'Delivered', value: 'delivered' },
    { label: 'Voided', value: 'voided' },
];

async function load(p = 1): Promise<void> {
    loading.value = true;
    try {
        const res = await shipmentsApi.list({
            page: p,
            per_page: 25,
            q: q.value || undefined,
            status: status.value ?? undefined,
        });
        rows.value = res.data;
        total.value = res.meta.total;
        page.value = res.meta.current_page;
    } finally {
        loading.value = false;
    }
}

function money(cents: number | null): string {
    if (cents == null) return '—';
    return `$${(cents / 100).toFixed(2)}`;
}

function goTo(row: ShipmentListItem): void {
    router.push(`/dashboard/shipments/${row.id}`);
}

onMounted(() => load());
</script>

<template>
    <div>
        <PageHeader title="Shipments" subtitle="Browse, filter, and drill into shipments across the team.">
            <template #actions>
                <router-link to="/dashboard/shipments/create">
                    <Button label="New shipment" icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-surface-200 p-4 mb-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <InputText v-model="q" placeholder="Search by reference or tracking…" class="w-full" @keyup.enter="load(1)" />
            </div>
            <Select
                v-model="status"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                placeholder="Status"
                class="w-48"
            />
            <Button label="Apply filters" severity="secondary" @click="load(1)" />
        </div>

        <div class="bg-white rounded-xl border border-surface-200 overflow-hidden">
            <DataTable
                :value="rows"
                :loading="loading"
                striped-rows
                @row-click="(e) => goTo(e.data as ShipmentListItem)"
                selection-mode="single"
                data-key="id"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column field="status" header="Status">
                    <template #body="s">
                        <StatusPill :status="s.data.status" />
                    </template>
                </Column>
                <Column header="To">
                    <template #body="s">
                        <div v-if="s.data.to_address" class="text-sm">
                            <div>{{ s.data.to_address.name ?? '—' }}</div>
                            <div class="text-surface-500">
                                {{ [s.data.to_address.city, s.data.to_address.state, s.data.to_address.country].filter(Boolean).join(', ') }}
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column field="service" header="Service" />
                <Column field="tracking_code" header="Tracking" />
                <Column header="Cost">
                    <template #body="s">{{ money(s.data.cost_cents) }}</template>
                </Column>
                <Column field="reference" header="Reference" />
                <template #empty>
                    <div class="text-center py-10 text-surface-500">
                        No shipments match these filters.
                    </div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
