<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { addressesApi, type AddressItem } from '@dashboard/api/data';

const router = useRouter();
const rows = ref<AddressItem[]>([]);
const loading = ref(false);
const q = ref('');

async function load(): Promise<void> {
    loading.value = true;
    try { rows.value = (await addressesApi.list({ q: q.value || undefined })).data; }
    finally { loading.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Addresses" subtitle="Your address book.">
            <template #actions>
                <router-link to="/dashboard/addresses/create">
                    <Button label="New address" icon="pi pi-plus" />
                </router-link>
            </template>
        </PageHeader>

        <div class="card mb-4 flex gap-3">
            <InputText v-model="q" placeholder="Search name, company, street…" class="flex-1" @keyup.enter="load" />
            <Button label="Search" severity="secondary" @click="load" />
        </div>

        <div class="card">
            <DataTable
                :value="rows"
                :loading="loading"
                striped-rows
                selection-mode="single"
                data-key="id"
                @row-click="(e) => router.push(`/dashboard/addresses/${(e.data as AddressItem).id}`)"
            >
                <Column field="id" header="#" style="width: 5rem" />
                <Column header="Name / Company">
                    <template #body="s">
                        <div>{{ s.data.name ?? '—' }}</div>
                        <div class="text-xs text-surface-500">{{ s.data.company ?? '' }}</div>
                    </template>
                </Column>
                <Column header="Address">
                    <template #body="s">
                        <div>{{ s.data.street1 }}</div>
                        <div class="text-xs text-surface-500">
                            {{ [s.data.city, s.data.state, s.data.zip].filter(Boolean).join(', ') }}, {{ s.data.country }}
                        </div>
                    </template>
                </Column>
                <Column header="Verified">
                    <template #body="s">
                        <Tag v-if="s.data.verified" value="Verified" severity="success" />
                        <Tag v-else value="Unverified" severity="warn" />
                    </template>
                </Column>
                <Column field="phone" header="Phone" />
                <template #empty>
                    <div class="text-center py-10 text-surface-500">No addresses yet.</div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
