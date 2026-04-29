<script setup lang="ts">
import Button from 'primevue/button';
</script>

<template>
    <section class="relative py-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-700 via-primary-800 to-ink-900"></div>
        <div class="absolute inset-0 opacity-40">
            <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full bg-accent-500/30 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 rounded-full bg-primary-400/30 blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative text-center text-white">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-full bg-white/10 backdrop-blur border border-white/20">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-400 animate-pulse"></span>
                14-day free trial
            </span>
            <h2 class="mt-5 text-3xl lg:text-5xl font-bold tracking-tight">
                Start shipping smarter today.
            </h2>
            <p class="mt-4 text-lg text-primary-100 max-w-2xl mx-auto">
                No credit card · Full-feature access · Cancel anytime.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                <router-link to="/portal/register">
                    <Button
                        label="Start free trial"
                        icon="pi pi-arrow-right"
                        icon-pos="right"
                        size="large"
                        rounded
                        class="!bg-accent-500 !border-accent-500 hover:!bg-accent-600 hover:!border-accent-600 !font-semibold !px-8 shadow-xl shadow-accent-500/40"
                    />
                </router-link>
                <router-link to="/contact">
                    <Button
                        label="Talk to sales"
                        severity="secondary"
                        outlined
                        rounded
                        size="large"
                        class="!text-white !border-white/40 hover:!bg-white/10 !px-7"
                    />
                </router-link>
            </div>
        </div>
    </section>
</template>
