<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';

type Tier = {
    name: string;
    monthly: number | null;
    yearly: number | null;
    tagline: string;
    shipments: string;
    users: string;
    highlight?: boolean;
    cta: string;
    features: string[];
};

const cadence = ref<'monthly' | 'yearly'>('monthly');

const tiers: Tier[] = [
    {
        name: 'Starter',
        monthly: 49,
        yearly: 39,
        tagline: 'Solo or tiny team, just getting started.',
        shipments: '100 shipments / mo',
        users: '3 users',
        cta: 'Start free trial',
        features: [
            'Up to 3 carriers',
            'Rate-shopping + label purchase',
            'Branded tracking page',
            'Email support',
        ],
    },
    {
        name: 'Team',
        monthly: 199,
        yearly: 159,
        tagline: 'E-commerce brand with an ops team.',
        shipments: '1,000 shipments / mo',
        users: '10 users',
        cta: 'Start free trial',
        features: [
            'All Starter features',
            'Role-based workflows (5 roles)',
            'Approval queues',
            'Batches + ScanForms + Pickups',
            'Priority support',
        ],
    },
    {
        name: 'Business',
        monthly: 499,
        yearly: 399,
        tagline: 'Multi-warehouse ops or 3PL-lite.',
        shipments: '5,000 shipments / mo',
        users: 'Unlimited users',
        highlight: true,
        cta: 'Start free trial',
        features: [
            'All Team features',
            'Client portal (multi-tenant)',
            'Claims Autopilot',
            'Advanced analytics',
            'SSO (Google)',
            'Slack + webhook integrations',
        ],
    },
    {
        name: '3PL',
        monthly: 999,
        yearly: 799,
        tagline: 'Small 3PLs with per-client billing.',
        shipments: '15,000 shipments / mo',
        users: 'Unlimited users',
        cta: 'Talk to sales',
        features: [
            'All Business features',
            'FlexRate markup per client',
            'Per-client invoicing',
            'SLA management',
            'Dedicated success manager',
        ],
    },
];

function price(tier: Tier): number | string {
    const value = cadence.value === 'monthly' ? tier.monthly : tier.yearly;
    return value ?? 'Custom';
}
</script>

<template>
    <section id="pricing" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase text-accent-600">Pricing</p>
                <h2 class="mt-3 text-3xl lg:text-5xl font-bold text-ink-900 tracking-tight">
                    Simple, transparent pricing
                </h2>
                <p class="mt-4 text-lg text-ink-600">
                    14-day free trial. No contracts. No surprise fees. Cancel anytime.
                </p>

                <div class="mt-8 inline-flex items-center p-1 rounded-full bg-ink-100 border border-ink-200">
                    <button
                        type="button"
                        class="px-5 py-2 text-sm font-semibold rounded-full transition-colors"
                        :class="cadence === 'monthly' ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500 hover:text-ink-800'"
                        @click="cadence = 'monthly'"
                    >Monthly</button>
                    <button
                        type="button"
                        class="px-5 py-2 text-sm font-semibold rounded-full transition-colors flex items-center gap-2"
                        :class="cadence === 'yearly' ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500 hover:text-ink-800'"
                        @click="cadence = 'yearly'"
                    >
                        Yearly
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent-100 text-accent-700 uppercase tracking-wider">Save 20%</span>
                    </button>
                </div>
            </div>

            <div class="mt-14 grid md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div
                    v-for="tier in tiers"
                    :key="tier.name"
                    class="relative rounded-2xl p-6 flex flex-col border transition-all"
                    :class="tier.highlight
                        ? 'bg-gradient-to-br from-ink-900 to-primary-900 text-white border-transparent card-lift-lg'
                        : 'bg-white border-ink-100 hover:border-primary-200 hover:card-lift'"
                >
                    <span
                        v-if="tier.highlight"
                        class="absolute -top-3 right-5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-accent-500 text-white shadow-md shadow-accent-500/30"
                    >
                        Most popular
                    </span>

                    <h3 class="text-lg font-bold" :class="tier.highlight ? 'text-white' : 'text-ink-900'">
                        {{ tier.name }}
                    </h3>
                    <p
                        class="mt-1 text-sm"
                        :class="tier.highlight ? 'text-ink-200' : 'text-ink-500'"
                    >{{ tier.tagline }}</p>

                    <div class="mt-6 flex items-baseline gap-1">
                        <template v-if="typeof price(tier) === 'number'">
                            <span class="text-5xl font-bold tracking-tight" :class="tier.highlight ? 'text-white' : 'text-ink-900'">
                                ${{ price(tier) }}
                            </span>
                            <span class="text-sm" :class="tier.highlight ? 'text-ink-300' : 'text-ink-500'">
                                /mo
                            </span>
                        </template>
                        <template v-else>
                            <span class="text-3xl font-bold" :class="tier.highlight ? 'text-white' : 'text-ink-900'">
                                {{ price(tier) }}
                            </span>
                        </template>
                    </div>

                    <div class="mt-2 text-sm" :class="tier.highlight ? 'text-ink-300' : 'text-ink-500'">
                        {{ tier.shipments }} · {{ tier.users }}
                    </div>

                    <router-link :to="tier.cta === 'Talk to sales' ? '/contact' : '/portal/register'" class="mt-6">
                        <Button
                            :label="tier.cta"
                            icon="pi pi-arrow-right"
                            icon-pos="right"
                            rounded
                            class="w-full !font-semibold"
                            :class="tier.highlight
                                ? '!bg-accent-500 !border-accent-500 hover:!bg-accent-600 hover:!border-accent-600'
                                : '!bg-primary-600 !border-primary-600 hover:!bg-primary-700 hover:!border-primary-700'"
                        />
                    </router-link>

                    <ul class="mt-6 space-y-3 flex-1 pt-6 border-t" :class="tier.highlight ? 'border-white/10' : 'border-ink-100'">
                        <li
                            v-for="feat in tier.features"
                            :key="feat"
                            class="flex items-start gap-2.5 text-sm"
                            :class="tier.highlight ? 'text-ink-200' : 'text-ink-700'"
                        >
                            <span
                                class="mt-0.5 inline-flex items-center justify-center w-4 h-4 rounded-full flex-shrink-0"
                                :class="tier.highlight ? 'bg-accent-500 text-white' : 'bg-primary-100 text-primary-700'"
                            >
                                <i class="pi pi-check text-[9px]"></i>
                            </span>
                            <span>{{ feat }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 text-center text-sm text-ink-500">
                Enterprise plans available — <router-link to="/contact" class="font-semibold text-primary-700 underline underline-offset-2 hover:text-primary-800">talk to sales</router-link>.
                Overage $0.05–$0.10/shipment above plan.
            </div>
        </div>
    </section>
</template>
