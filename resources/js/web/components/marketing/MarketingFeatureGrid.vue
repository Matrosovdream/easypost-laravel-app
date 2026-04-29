<script setup lang="ts">
type Tone = 'primary' | 'accent' | 'yellow' | 'ink';

const features: { icon: string; title: string; body: string; tone: Tone }[] = [
    { tone: 'primary', icon: 'pi-users',        title: 'Role-based workflows', body: 'Admins, Managers, Shippers, CS agents, Clients — each with scoped views and permissions out of the box.' },
    { tone: 'accent',  icon: 'pi-truck',        title: '100+ carriers',        body: 'USPS, UPS, FedEx, DHL and 100+ more via EasyPost. Rate-shop in one click; label purchase with spending caps.' },
    { tone: 'yellow',  icon: 'pi-check-circle', title: 'Approval queues',      body: 'High-value shipments route to managers automatically. Audit everything. Ship with confidence.' },
    { tone: 'primary', icon: 'pi-clone',        title: 'Batches & ScanForms',  body: 'Print 500 labels, build one ScanForm, schedule the pickup. Warehouse throughput, not spreadsheets.' },
    { tone: 'accent',  icon: 'pi-chart-line',   title: 'Analytics that matter',body: 'Cost per shipment, carrier performance, SLA breaches, claim recovery rate. Drill down by warehouse, client, or person.' },
    { tone: 'primary', icon: 'pi-palette',      title: 'Branded tracking',     body: 'Host a tracking page on your domain, in your colors. Customers see your brand — not a carrier website.' },
    { tone: 'accent',  icon: 'pi-shield',       title: 'Claims Autopilot',     body: 'Lost package? We file the carrier claim, collect the refund, and credit the client. 20% success fee, $0 otherwise.' },
    { tone: 'yellow',  icon: 'pi-wallet',       title: 'FlexRate markup',      body: 'For 3PLs: mark up carrier rates per client, invoice on consumption, keep the margin.' },
    { tone: 'primary', icon: 'pi-bolt',         title: 'Webhook-native',       body: 'Real-time tracker updates. Fire internal automations on any carrier event. No polling, no stale state.' },
];

const tones: Record<Tone, { bg: string; text: string; ring: string }> = {
    primary: { bg: 'bg-primary-50',   text: 'text-primary-600', ring: 'group-hover:ring-primary-200' },
    accent:  { bg: 'bg-accent-50',    text: 'text-accent-600',  ring: 'group-hover:ring-accent-200' },
    yellow:  { bg: 'bg-amber-50',     text: 'text-amber-600',   ring: 'group-hover:ring-amber-200' },
    ink:     { bg: 'bg-ink-50',       text: 'text-ink-700',     ring: 'group-hover:ring-ink-200' },
};
</script>

<template>
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase text-accent-600">Features</p>
                <h2 class="mt-3 text-3xl lg:text-5xl font-bold text-ink-900 tracking-tight">
                    Everything shipping teams need.<br class="hidden sm:block" />
                    <span class="text-primary-700">Nothing they don't.</span>
                </h2>
                <p class="mt-5 text-lg text-ink-600">
                    Designed around the real work — from quoting to claims — not a generic label printer.
                </p>
            </div>

            <div class="mt-16 grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="f in features"
                    :key="f.title"
                    class="group p-6 rounded-2xl border border-ink-100 bg-white hover:border-primary-200 hover:card-lift transition-all"
                >
                    <div
                        :class="[
                            'w-12 h-12 rounded-xl flex items-center justify-center ring-4 ring-transparent transition-all',
                            tones[f.tone].bg,
                            tones[f.tone].text,
                            tones[f.tone].ring,
                        ]"
                    >
                        <i :class="`pi ${f.icon} text-xl`"></i>
                    </div>
                    <h3 class="mt-5 font-semibold text-lg text-ink-900">{{ f.title }}</h3>
                    <p class="mt-2 text-ink-600 text-sm leading-relaxed">{{ f.body }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
