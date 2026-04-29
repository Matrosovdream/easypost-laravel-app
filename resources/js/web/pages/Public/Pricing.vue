<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import MarketingPricingTable from '@web/components/marketing/MarketingPricingTable.vue';
import MarketingFaq from '@web/components/marketing/MarketingFaq.vue';
import MarketingCtaBand from '@web/components/marketing/MarketingCtaBand.vue';

useSeo({
    title: 'Pricing — ShipDesk',
    description: 'Simple, transparent pricing. Starter $49, Team $199, Business $499, 3PL $999. 14-day free trial. No contracts.',
});
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 text-center max-w-2xl">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900">Pricing built to scale with you.</h1>
            <p class="mt-4 text-lg text-surface-600">
                Every tier includes the full feature set. Upgrade when you need more volume.
            </p>
        </div>
    </section>

    <MarketingPricingTable />
    <MarketingFaq />
    <MarketingCtaBand />
</template>
