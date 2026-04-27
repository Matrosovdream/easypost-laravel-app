<script setup lang="ts">
import { onMounted } from 'vue';
import axios from 'axios';
import { useSeo } from '@web/composables/useSeo';
import MarketingFeatureGrid from '@web/components/marketing/MarketingFeatureGrid.vue';
import MarketingSplitFeature from '@web/components/marketing/MarketingSplitFeature.vue';
import MarketingStatsBand from '@web/components/marketing/MarketingStatsBand.vue';
import MarketingCtaBand from '@web/components/marketing/MarketingCtaBand.vue';

useSeo({
    title: 'Features — ShipDesk',
    description: 'Role-based workflows, rate shopping across 100+ carriers, branded tracking, Claims Autopilot, FlexRate markup, and real-time webhooks.',
});

// Reverb demo: ping the backend so it broadcasts on the public `demo.features`
// channel. Any admin watching the dashboard will see a notification appear.
onMounted(() => {
    axios.post('/api/demo/features-visited').catch(() => {
        // Demo only — silent fail if backend or Reverb is down.
    });
});
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 text-center max-w-3xl">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900">
                Built for the real work — not a generic label printer.
            </h1>
            <p class="mt-4 text-lg text-surface-600">
                Every feature shipped because a real team asked for it. Here's what's inside.
            </p>
        </div>
    </section>

    <MarketingFeatureGrid />

    <MarketingSplitFeature
        eyebrow="Shipments + Approvals"
        title="Approval queues for the ones that matter."
        body="Configure thresholds — dollar amount, carrier, country, client. Shipments above the line land in the Manager's queue. Everything else ships instantly."
        :bullets="[
            'Per-rule approval triggers (amount, country, carrier)',
            'Full audit log: who approved, when, why',
            'Reject + send back with a comment',
            'Auto-approve for trusted Shippers',
        ]"
    />

    <MarketingSplitFeature
        eyebrow="Batches + ScanForms + Pickups"
        :reverse="true"
        title="Warehouse throughput, not spreadsheets."
        body="Print 500 labels at once. Build a single ScanForm for the driver. Schedule the pickup. Done."
        :bullets="[
            'Batch creation with rate-shop before purchase',
            'ScanForm generation per carrier',
            'Pickup scheduling with time windows',
            'Print queue + label templates (4×6, letter)',
        ]"
    />

    <MarketingSplitFeature
        eyebrow="Branded tracking"
        title="Your brand on the last mile."
        body="Customers follow their package on your domain, in your colors. No more carrier-website confusion, no more support tickets asking 'where's my order?'."
        :bullets="[
            'Custom domain + logo + colors per tenant',
            'Real-time events via webhook',
            'Email + SMS notifications (on Business+)',
            'Review request at delivery',
        ]"
    />

    <MarketingStatsBand />
    <MarketingCtaBand />
</template>
