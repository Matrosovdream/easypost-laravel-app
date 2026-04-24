<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { claimsApi, type ClaimDetail } from '@dashboard/api/care';
import { useAuthStore } from '@dashboard/stores/auth';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const auth = useAuthStore();
const toast = useToast();

const detail = ref<ClaimDetail | null>(null);
const busy = ref(false);

async function load(): Promise<void> {
    detail.value = await claimsApi.show(Number(route.params.id));
}

function money(c: number | null): string {
    return c == null ? '—' : `$${(c / 100).toFixed(2)}`;
}

async function doSubmit(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try { await claimsApi.submit(detail.value.id); toast.success('Submitted to carrier'); await load(); }
    catch { toast.error('Could not submit claim.'); }
    finally { busy.value = false; }
}

async function doApprove(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try { await claimsApi.approve(detail.value.id); toast.success('Claim approved'); await load(); }
    catch { toast.error('Could not approve claim.'); }
    finally { busy.value = false; }
}

async function doPay(): Promise<void> {
    if (!detail.value) return;
    busy.value = true;
    try { await claimsApi.pay(detail.value.id); toast.success('Marked paid'); await load(); }
    catch { toast.error('Could not mark paid.'); }
    finally { busy.value = false; }
}

async function doClose(): Promise<void> {
    if (!detail.value) return;
    const reason = window.prompt('Close reason:') ?? undefined;
    busy.value = true;
    try { await claimsApi.close(detail.value.id, reason); toast.success('Closed'); await load(); }
    catch { toast.error('Could not close.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="detail">
        <PageHeader :title="`Claim #${detail.id}`" :subtitle="`${detail.type} · ${money(detail.amount_cents)}`">
            <template #actions>
                <router-link to="/dashboard/claims">
                    <Button label="Back" severity="secondary" text />
                </router-link>
                <Button v-if="detail.state === 'open'" label="Submit to carrier" icon="pi pi-send" :loading="busy" @click="doSubmit" />
                <template v-if="auth.can('claims.approve')">
                    <Button v-if="['submitted', 'open'].includes(detail.state)" label="Approve" icon="pi pi-check" :loading="busy" @click="doApprove" />
                    <Button v-if="['approved', 'submitted'].includes(detail.state)" label="Mark paid" icon="pi pi-dollar" severity="secondary" outlined :loading="busy" @click="doPay" />
                </template>
                <Button v-if="detail.state !== 'closed'" label="Close" severity="danger" outlined :loading="busy" @click="doClose" />
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">State</div>
                <div class="mt-2"><Tag :value="detail.state" /></div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Amount</div>
                <div class="mt-2 text-2xl font-bold">{{ money(detail.amount_cents) }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Recovered</div>
                <div class="mt-2 text-2xl font-bold">{{ money(detail.recovered_cents) }}</div>
            </div>
            <div class="card">
                <div class="text-xs text-surface-500 uppercase tracking-wider">EP claim ID</div>
                <div class="mt-2 font-mono text-sm">{{ detail.ep_claim_id ?? '—' }}</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="card">
                <h3 class="font-semibold">Description</h3>
                <p class="mt-2 text-sm whitespace-pre-wrap">{{ detail.description }}</p>
            </div>
            <div class="card">
                <h3 class="font-semibold">Timeline</h3>
                <Timeline v-if="detail.timeline?.length" :value="detail.timeline" class="mt-4">
                    <template #content="slot">
                        <div class="pb-4">
                            <div class="text-sm font-medium">{{ slot.item.event }}</div>
                            <div class="text-xs text-surface-500">{{ new Date(slot.item.at).toLocaleString() }}</div>
                        </div>
                    </template>
                </Timeline>
                <p v-else class="text-sm text-surface-500 mt-2">No events yet.</p>
            </div>
        </div>
    </div>
</template>
