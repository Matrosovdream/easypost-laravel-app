<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import MarketingTestimonials from '@web/components/marketing/MarketingTestimonials.vue';
import MarketingCtaBand from '@web/components/marketing/MarketingCtaBand.vue';

useSeo({
    title: 'Customers — ShipDesk',
    description: 'DTC brands, multi-warehouse ops teams, and small 3PLs use ShipDesk to ship smarter.',
});

const stories = [
    {
        brand: 'Northwind Goods',
        industry: 'DTC home goods',
        result: '60% faster label time',
        body: 'Moved off spreadsheets + three separate carrier portals. Unified under ShipDesk in 2 weeks.',
    },
    {
        brand: 'FastDrop 3PL',
        industry: '3PL, 4 warehouses',
        result: '$84k FlexRate revenue / yr',
        body: 'Now invoices 22 clients automatically, with margin baked into every label.',
    },
    {
        brand: 'Flux Commerce',
        industry: 'DTC apparel',
        result: '$47k claims recovered',
        body: 'Claims Autopilot took a whole compliance function off their plate.',
    },
];
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 text-center max-w-2xl">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900">
                Real teams, real numbers.
            </h1>
            <p class="mt-4 text-lg text-surface-600">
                Shipping teams from DTC brands to 3PLs choose ShipDesk to run their ops.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-6">
                <div
                    v-for="story in stories"
                    :key="story.brand"
                    class="rounded-xl border border-surface-200 bg-white p-6"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                            {{ story.brand.slice(0, 1) }}
                        </div>
                        <div>
                            <div class="font-semibold text-surface-900">{{ story.brand }}</div>
                            <div class="text-sm text-surface-500">{{ story.industry }}</div>
                        </div>
                    </div>
                    <div class="mt-6 text-3xl font-bold text-primary-600">{{ story.result }}</div>
                    <p class="mt-3 text-surface-600 text-sm leading-relaxed">{{ story.body }}</p>
                </div>
            </div>
        </div>
    </section>

    <MarketingTestimonials />
    <MarketingCtaBand />
</template>
