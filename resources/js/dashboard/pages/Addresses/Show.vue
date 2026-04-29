<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { addressesApi, type AddressItem } from '@dashboard/api/data';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const a = ref<AddressItem | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    a.value = await addressesApi.show(Number(route.params.id));
}

async function verify(): Promise<void> {
    if (!a.value) return;
    busy.value = true;
    try {
        a.value = await addressesApi.verify(a.value.id);
        toast.success(a.value.verified ? 'Verified' : 'Could not verify');
    } catch { toast.error('Verification failed.'); }
    finally { busy.value = false; }
}

async function remove(): Promise<void> {
    if (!a.value) return;
    if (!confirm('Delete this address?')) return;
    busy.value = true;
    try {
        await addressesApi.delete(a.value.id);
        toast.success('Deleted');
        router.push('/dashboard/addresses');
    } catch { toast.error('Delete failed.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="a">
        <PageHeader :title="a.name ?? `Address #${a.id}`" :subtitle="a.company ?? undefined">
            <template #actions>
                <router-link to="/dashboard/addresses">
                    <Button label="Back" severity="secondary" text />
                </router-link>
                <Button v-if="a.ep_address_id" label="Re-verify" icon="pi pi-check" :loading="busy" @click="verify" />
                <Button label="Delete" severity="danger" outlined :loading="busy" @click="remove" />
            </template>
        </PageHeader>

        <div class="card">
            <div class="flex items-center gap-3 mb-4">
                <Tag v-if="a.verified" value="Verified" severity="success" />
                <Tag v-else value="Unverified" severity="warn" />
                <span v-if="a.verified_at" class="text-xs text-surface-500">{{ new Date(a.verified_at).toLocaleString() }}</span>
            </div>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-surface-500 uppercase">Address</div>
                    <div class="mt-1">{{ a.street1 }}</div>
                    <div v-if="a.street2">{{ a.street2 }}</div>
                    <div>{{ [a.city, a.state, a.zip].filter(Boolean).join(', ') }}</div>
                    <div>{{ a.country }}</div>
                </div>
                <div>
                    <div class="text-xs text-surface-500 uppercase">Contact</div>
                    <div class="mt-1">{{ a.phone ?? '—' }}</div>
                    <div>{{ a.email ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
