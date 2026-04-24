<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import MarketingHero from '@web/components/marketing/MarketingHero.vue';
import MarketingLogoStrip from '@web/components/marketing/MarketingLogoStrip.vue';
import MarketingFeatureGrid from '@web/components/marketing/MarketingFeatureGrid.vue';
import MarketingSplitFeature from '@web/components/marketing/MarketingSplitFeature.vue';
import MarketingPricingTable from '@web/components/marketing/MarketingPricingTable.vue';
import MarketingStatsBand from '@web/components/marketing/MarketingStatsBand.vue';
import MarketingTestimonials from '@web/components/marketing/MarketingTestimonials.vue';
import MarketingFaq from '@web/components/marketing/MarketingFaq.vue';
import MarketingCtaBand from '@web/components/marketing/MarketingCtaBand.vue';

useSeo({
    title: 'ShipDesk — Role-based shipping for real teams',
    description: '100+ carriers, role-based team workflows, branded tracking, and Claims Autopilot. Built on EasyPost. 14-day free trial.',
});
</script>

<template>
    <MarketingHero />
    <MarketingLogoStrip />
    <MarketingFeatureGrid />

    <MarketingSplitFeature
        eyebrow="Role-based workflows"
        title="Every person sees exactly what they need."
        body="Shippers print labels. Managers approve what needs approval. CS agents handle exceptions. Clients see only their own shipments. All in one app."
        :bullets="[
            '5 built-in roles: Admin, Manager, Shipper, CS Agent, Client',
            'Right-level permissions on every action (62 distinct rights)',
            'Team-scoped data isolation — global query scopes',
            'Custom roles + rights for Enterprise',
        ]"
    />

    <MarketingSplitFeature
        eyebrow="Rate shopping"
        :reverse="true"
        title="Every shipment, cheapest route."
        body="One rate-shop call returns prices across every carrier you have wired. Pick a service level rule once; we pick the carrier that hits it cheapest, every time."
        :bullets="[
            'Compare 100+ carriers in one request',
            'SLA-aware routing (priority, ground, economy)',
            'Negotiated-rates pass-through — bring your own UPS/FedEx accounts',
            'FlexRate markup: 3PLs charge clients on consumption',
        ]"
    />

    <MarketingStatsBand />
    <MarketingTestimonials />
    <MarketingPricingTable />
    <MarketingFaq />
    <MarketingCtaBand />
</template>
