<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { trackersApi, type TrackerItem } from '@dashboard/api/data';

const router = useRouter();
const rows = ref<TrackerItem[]>([]);
const loading = ref(false);

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await trackersApi.list()).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Trackers" subtitle="Standalone and shipment trackers.">
            <template #actions>
                <router-link to="/dashboard/trackers/create">
                    <Button label="New tracker" icon="pi pi-plus" />
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
                @row-click="(e) => router.push(`/dashboard/trackers/${(e.data as TrackerItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Tracking">
                    <template #body="s"><span class="font-mono">{{ s.data.tracking_code }}</span></template>
                </Column>
                <Column field="carrier" header="Carrier" />
                <Column header="Status">
                    <template #body="s"><Tag :value="s.data.status" /></template>
                </Column>
                <Column header="Last event">
                    <template #body="s">{{ s.data.last_event_at ? new Date(s.data.last_event_at).toLocaleString() : '—' }}</template>
                </Column>
                <Column header="Public URL">
                    <template #body="s">
                        <a v-if="s.data.public_url" :href="s.data.public_url" target="_blank" class="text-primary-600 hover:underline text-xs">
                            Open
                        </a>
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No trackers yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
