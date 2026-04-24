<script setup lang="ts">
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

const faqs = [
    {
        q: 'Do I need my own EasyPost account?',
        a: 'No. ShipDesk is built on EasyPost, but your team uses our interface. You can optionally bring your own carrier accounts (UPS/FedEx negotiated rates) through our carrier setup flow.',
    },
    {
        q: 'How many carriers do you support?',
        a: 'Over 100, via EasyPost. USPS is enabled by default. UPS, FedEx, DHL, Canada Post and others activate when you add your accounts.',
    },
    {
        q: 'Is there a free trial?',
        a: '14 days, no credit card required. You get full access to every feature on every tier during the trial.',
    },
    {
        q: 'Can I invite clients to see their own shipments?',
        a: 'Yes, on Business tier and above. Clients log in, see only their scoped data, and can request returns — without accessing your internal ops.',
    },
    {
        q: 'Do you handle claims and refunds?',
        a: 'Claims Autopilot files the carrier claim, tracks it to resolution, and credits the refund. It is success-fee only (20% of recovered funds).',
    },
    {
        q: 'Can I cancel at any time?',
        a: 'Yes. No contracts, no termination fees. Data export is always available (CSV + API).',
    },
    {
        q: 'How do you handle data isolation between tenants?',
        a: 'Every record carries a team_id. Global Eloquent scopes enforce isolation at the ORM layer. Cross-tenant queries are not possible through the product UI.',
    },
    {
        q: 'Do you support SSO?',
        a: 'Google SSO on Business+ at launch. Microsoft SSO and SAML on Enterprise.',
    },
];
</script>

<template>
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-12 gap-12">
                <div class="lg:col-span-4">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-accent-600">FAQ</p>
                    <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-ink-900 tracking-tight">
                        Got questions? <br />We've got answers.
                    </h2>
                    <p class="mt-4 text-ink-600 leading-relaxed">
                        Can't find what you're looking for? <router-link to="/contact" class="font-semibold text-primary-700 hover:text-primary-800 underline underline-offset-2">Ask us directly</router-link> — we reply within one business day.
                    </p>

                    <div class="hidden lg:block mt-8 p-5 rounded-2xl bg-ink-50/60 border border-ink-100">
                        <div class="flex items-center gap-2 text-sm font-semibold text-ink-900">
                            <i class="pi pi-comments text-primary-600"></i>
                            Live chat
                        </div>
                        <p class="mt-2 text-sm text-ink-600">Mon–Fri, 9am–6pm ET. Average reply under 2 minutes.</p>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <Accordion :multiple="false" class="faq-accordion">
                        <AccordionPanel v-for="(f, i) in faqs" :key="i" :value="i">
                            <AccordionHeader>{{ f.q }}</AccordionHeader>
                            <AccordionContent>
                                <p class="text-ink-600 leading-relaxed">{{ f.a }}</p>
                            </AccordionContent>
                        </AccordionPanel>
                    </Accordion>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.faq-accordion :deep(.p-accordionpanel) {
    border: 1px solid var(--color-ink-100);
    border-radius: 12px;
    margin-bottom: 8px;
    overflow: hidden;
}
.faq-accordion :deep(.p-accordionheader) {
    font-weight: 600;
    color: var(--color-ink-900);
}
</style>
