<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import MarketingCtaBand from '@web/components/marketing/MarketingCtaBand.vue';

useSeo({
    title: 'About — ShipDesk',
    description: 'ShipDesk is a small, focused team building the shipping platform we wished existed when we ran ops at scale.',
});

const team = [
    { name: 'Stan Matrosov', role: 'Founder & CEO', initials: 'SM' },
    { name: 'Alex Kim', role: 'Head of Engineering', initials: 'AK' },
    { name: 'Priya Nair', role: 'Head of Product', initials: 'PN' },
    { name: 'Dana Okafor', role: 'Customer Success', initials: 'DO' },
];
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 max-w-3xl">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900 text-center">
                A shipping platform built by shipping ops people.
            </h1>
            <p class="mt-6 text-lg text-surface-600 leading-relaxed">
                ShipDesk started because we ran shipping at a DTC brand and couldn't find a single tool
                that worked for the whole team. Shippers wanted speed. Managers wanted approvals. Clients
                wanted visibility. We bolted together four tools and a Google Sheet, and swore we'd build
                something better.
            </p>
            <p class="mt-4 text-lg text-surface-600 leading-relaxed">
                We did. We use it every day. So do a few hundred other teams now.
            </p>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-surface-900 text-center">The team</h2>
            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="m in team"
                    :key="m.name"
                    class="text-center p-6 rounded-xl bg-surface-50 border border-surface-200"
                >
                    <div class="w-24 h-24 mx-auto rounded-full bg-primary-100 text-primary-700 text-2xl font-bold flex items-center justify-center">
                        {{ m.initials }}
                    </div>
                    <div class="mt-4 font-semibold text-surface-900">{{ m.name }}</div>
                    <div class="text-sm text-surface-500">{{ m.role }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-surface-50">
        <div class="container mx-auto px-6 max-w-3xl">
            <h2 class="text-3xl font-bold text-surface-900 text-center">Our values</h2>
            <div class="mt-12 grid md:grid-cols-3 gap-6">
                <div class="p-6 rounded-xl bg-white border border-surface-200">
                    <i class="pi pi-heart-fill text-2xl text-primary-500"></i>
                    <h3 class="mt-3 font-semibold text-surface-900">Close to the user</h3>
                    <p class="mt-2 text-sm text-surface-600">Every feature ships because a real customer asked for it. No speculation, no bloat.</p>
                </div>
                <div class="p-6 rounded-xl bg-white border border-surface-200">
                    <i class="pi pi-shield text-2xl text-primary-500"></i>
                    <h3 class="mt-3 font-semibold text-surface-900">Boring where it matters</h3>
                    <p class="mt-2 text-sm text-surface-600">Laravel, Postgres, Redis. Proven stack, no surprises at 3am.</p>
                </div>
                <div class="p-6 rounded-xl bg-white border border-surface-200">
                    <i class="pi pi-lock text-2xl text-primary-500"></i>
                    <h3 class="mt-3 font-semibold text-surface-900">Tenant isolation is sacred</h3>
                    <p class="mt-2 text-sm text-surface-600">Every record carries a team_id. Cross-tenant queries are impossible by construction.</p>
                </div>
            </div>
        </div>
    </section>

    <MarketingCtaBand />
</template>
