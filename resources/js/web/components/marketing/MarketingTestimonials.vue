<script setup lang="ts">
import Carousel from 'primevue/carousel';

const testimonials = [
    {
        quote: 'We replaced four spreadsheets and a Slack channel with ShipDesk. Our ops team cut label time by 60% in the first month.',
        name: 'Dana Wu',
        role: 'Head of Ops',
        company: 'Northwind Goods',
        avatar: 'DW',
    },
    {
        quote: 'The Client portal was the killer feature. Our 3PL clients log in, see their shipments, and stop emailing us at 2am.',
        name: 'Miguel Santos',
        role: 'Founder',
        company: 'FastDrop 3PL',
        avatar: 'MS',
    },
    {
        quote: 'Claims Autopilot has recovered $47K in lost-package refunds this year. We do nothing; ShipDesk handles the whole dance.',
        name: 'Priya Shah',
        role: 'CFO',
        company: 'Flux Commerce',
        avatar: 'PS',
    },
    {
        quote: 'The approval queue caught a $380 ground freight we almost billed to the wrong client. Saved us a very awkward invoice.',
        name: 'Jordan Avery',
        role: 'Ops Manager',
        company: 'Globex Supply',
        avatar: 'JA',
    },
];

const responsiveOptions = [
    { breakpoint: '1024px', numVisible: 2, numScroll: 1 },
    { breakpoint: '640px', numVisible: 1, numScroll: 1 },
];
</script>

<template>
    <section class="py-24 bg-ink-50/60">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase text-accent-600">Loved by ops teams</p>
                <h2 class="mt-3 text-3xl lg:text-5xl font-bold text-ink-900 tracking-tight">
                    Shipping teams love ShipDesk.
                </h2>
                <p class="mt-4 text-lg text-ink-600">
                    From DTC brands to small 3PLs — real customers, real numbers.
                </p>

                <div class="mt-5 flex items-center justify-center gap-2 text-sm">
                    <div class="flex items-center gap-0.5">
                        <i v-for="n in 5" :key="n" class="pi pi-star-fill text-sm" style="color: #FFCB3C"></i>
                    </div>
                    <span class="font-semibold text-ink-800">4.9</span>
                    <span class="text-ink-500">average, 400+ reviews</span>
                </div>
            </div>

            <Carousel
                :value="testimonials"
                :num-visible="3"
                :num-scroll="1"
                :responsive-options="responsiveOptions"
                :circular="true"
                :autoplay-interval="6000"
                class="mt-12"
            >
                <template #item="slotProps">
                    <div class="p-3 h-full">
                        <div class="bg-white rounded-2xl border border-ink-100 p-6 h-full flex flex-col card-lift">
                            <div class="flex items-center gap-0.5">
                                <i v-for="n in 5" :key="n" class="pi pi-star-fill text-sm" style="color: #FFCB3C"></i>
                            </div>
                            <p class="mt-4 text-ink-800 leading-relaxed flex-1 text-[15px]">
                                "{{ slotProps.data.quote }}"
                            </p>
                            <div class="mt-6 flex items-center gap-3 pt-4 border-t border-ink-100">
                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white font-bold flex items-center justify-center text-sm">
                                    {{ slotProps.data.avatar }}
                                </div>
                                <div>
                                    <div class="font-semibold text-ink-900 text-sm">{{ slotProps.data.name }}</div>
                                    <div class="text-xs text-ink-500">{{ slotProps.data.role }} · {{ slotProps.data.company }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Carousel>
        </div>
    </section>
</template>
