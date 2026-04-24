<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { billingApi, type PlanResp } from '@dashboard/api/billing';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const plan = ref<PlanResp | null>(null);
const busy = ref(false);

const PLAN_META: Record<string, { label: string; price: string; blurb: string }> = {
    starter:    { label: 'Starter',  price: '$49/mo',  blurb: 'Solo + tiny team' },
    team:       { label: 'Team',     price: '$199/mo', blurb: 'E-com brand with ops' },
    business:   { label: 'Business', price: '$499/mo', blurb: 'Multi-warehouse / 3PL-lite' },
    '3pl':      { label: '3PL',      price: '$999/mo', blurb: 'Small 3PLs w/ per-client billing' },
    enterprise: { label: 'Enterprise', price: 'Custom', blurb: 'Dedicated support' },
};

const usagePct = computed(() => {
    const u = plan.value?.usage;
    if (!u || u.cap == null) return 0;
    return Math.min(100, Math.round(100 * u.used / u.cap));
});

const atCap = computed(() => {
    const u = plan.value?.usage;
    return u?.cap != null && u.used >= u.cap;
});

async function load(): Promise<void> {
    plan.value = await billingApi.plan();
}

async function upgrade(target: string): Promise<void> {
    busy.value = true;
    try {
        const res = await billingApi.checkout(target);
        if (res.simulated) {
            toast.warn('Simulated checkout', 'BILLING_PRICE_* env vars not set. Real Stripe checkout lands when you wire prices.');
            window.location.href = res.url;
            return;
        }
        window.location.href = res.url;
    } catch { toast.error('Could not start checkout.'); }
    finally { busy.value = false; }
}

async function openPortal(): Promise<void> {
    busy.value = true;
    try {
        const res = await billingApi.portal();
        window.location.href = res.url;
    } catch { toast.error('Could not open billing portal.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="plan">
        <PageHeader title="Billing" subtitle="Your plan, usage, and payment method." />

        <Message v-if="atCap" severity="warn" :closable="false" class="mb-4">
            You've hit your monthly shipment cap. Upgrade to keep buying labels this month.
        </Message>

        <div class="grid lg:grid-cols-3 gap-4 mb-6">
            <div class="card lg:col-span-1">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Current plan</div>
                <div class="mt-2 text-2xl font-bold">{{ PLAN_META[plan.plan]?.label ?? plan.plan }}</div>
                <div class="text-sm text-surface-500">{{ PLAN_META[plan.plan]?.blurb ?? '' }}</div>
                <div class="mt-3"><Tag :value="plan.status" :severity="plan.status === 'active' ? 'success' : 'warn'" /></div>
                <div v-if="plan.trial_ends_at" class="text-xs text-surface-500 mt-3">
                    Trial ends {{ new Date(plan.trial_ends_at).toLocaleDateString() }}
                </div>
                <Button label="Open billing portal" icon="pi pi-external-link" severity="secondary" outlined class="mt-4 w-full" :loading="busy" @click="openPortal" />
            </div>

            <div class="card lg:col-span-2">
                <div class="text-xs text-surface-500 uppercase tracking-wider">Shipments this month</div>
                <div class="mt-2 text-3xl font-bold">
                    {{ plan.usage.used }}
                    <span class="text-surface-500 text-base font-normal">
                        / {{ plan.usage.cap ?? '∞' }}
                    </span>
                </div>
                <ProgressBar v-if="plan.usage.cap != null" :value="usagePct" class="mt-3" />
                <div class="text-xs text-surface-500 mt-2">
                    Resets {{ new Date(plan.usage.reset_at).toLocaleString() }}
                    <template v-if="plan.usage.remaining != null">
                        · {{ plan.usage.remaining }} remaining
                    </template>
                </div>
            </div>
        </div>

        <h3 class="font-semibold mb-3 text-surface-900">Change plan</h3>
        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div
                v-for="key in plan.available_plans"
                :key="key"
                class="card"
                :class="plan.plan === key ? 'ring-2 ring-primary-500' : ''"
            >
                <div class="font-semibold">{{ PLAN_META[key]?.label ?? key }}</div>
                <div class="text-2xl font-bold mt-1">{{ PLAN_META[key]?.price ?? '' }}</div>
                <div class="text-xs text-surface-500 mt-1">{{ PLAN_META[key]?.blurb ?? '' }}</div>
                <Button
                    v-if="plan.plan !== key"
                    label="Upgrade"
                    icon="pi pi-arrow-right"
                    class="mt-4 w-full"
                    :loading="busy"
                    @click="upgrade(key)"
                />
                <Tag v-else value="Current plan" severity="success" class="mt-4 w-full justify-center" />
            </div>
        </div>
    </div>
    <div v-else class="card text-surface-500">Loading billing…</div>
</template>
