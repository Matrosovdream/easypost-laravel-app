<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const router = useRouter();
const trackCode = ref('');

function onTrack() {
    const code = trackCode.value.trim();
    if (!code) return;
    router.push(`/track/${encodeURIComponent(code)}`);
}
</script>

<template>
    <section class="relative overflow-hidden bg-hero-halo">
        <div class="absolute inset-0 bg-nav-grid opacity-40 pointer-events-none"></div>

        <div class="container mx-auto px-6 pt-20 pb-24 relative">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-full bg-white text-primary-700 border border-primary-100 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-500"></span>
                        NEW · FlexRate markup for 3PLs
                    </span>
                    <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-ink-900 leading-[1.05]">
                        Ship smarter across
                        <span class="relative inline-block">
                            <span class="relative z-10">every role</span>
                            <span class="absolute left-0 right-0 bottom-1 h-3 bg-accent-200/70 rounded -z-0"></span>
                        </span>
                        on your team.
                    </h1>
                    <p class="mt-5 text-lg text-ink-600 leading-relaxed max-w-xl">
                        One shipping platform for Admins, Managers, Shippers, CS agents, and Clients.
                        100+ carriers, role-based workflows, and branded tracking — powered by EasyPost.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <router-link to="/portal/register">
                            <Button
                                label="Start 14-day free trial"
                                icon="pi pi-arrow-right"
                                icon-pos="right"
                                size="large"
                                rounded
                                class="!bg-accent-500 !border-accent-500 hover:!bg-accent-600 hover:!border-accent-600 !font-semibold !px-7 shadow-lg shadow-accent-500/30"
                            />
                        </router-link>
                        <router-link to="/features">
                            <Button label="See how it works" severity="secondary" outlined rounded size="large" class="!border-ink-200 !text-ink-800 hover:!bg-ink-50 !px-6" />
                        </router-link>
                    </div>

                    <div class="mt-8 flex items-center gap-6 text-sm text-ink-500">
                        <div class="flex items-center gap-1.5">
                            <div class="flex items-center gap-0.5">
                                <i v-for="n in 5" :key="n" class="pi pi-star-fill text-[var(--brand-yellow)] text-sm" style="color: #FFCB3C"></i>
                            </div>
                            <span class="font-semibold text-ink-800 ml-1">4.9</span>
                            <span>· 1,200+ shipping teams</span>
                        </div>
                        <div class="hidden sm:flex items-center gap-1.5">
                            <i class="pi pi-check-circle text-primary-600"></i>
                            No credit card
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 relative">
                    <div class="absolute -top-6 -left-6 w-24 h-24 rounded-full bg-accent-100 blur-2xl opacity-70"></div>
                    <div class="absolute -bottom-8 -right-4 w-32 h-32 rounded-full bg-primary-200 blur-2xl opacity-70"></div>

                    <div class="relative bg-white rounded-2xl card-lift-lg border border-ink-100 p-6 sm:p-7">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-700">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary-50">
                                <i class="pi pi-map-marker text-primary-600 text-xs"></i>
                            </span>
                            Track a shipment
                        </div>
                        <h3 class="mt-3 text-xl font-bold text-ink-900">Where's my package?</h3>
                        <p class="mt-1 text-sm text-ink-500">Enter any ShipDesk tracking code — no login needed.</p>

                        <form class="mt-5 space-y-3" @submit.prevent="onTrack">
                            <div class="relative">
                                <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-ink-400 text-sm"></i>
                                <InputText
                                    v-model="trackCode"
                                    placeholder="e.g. EZ2000000002"
                                    class="w-full !pl-11 !py-3.5 !rounded-xl !border-ink-200 !bg-ink-50/50 focus:!bg-white focus:!border-primary-400 focus:!ring-4 focus:!ring-primary-100"
                                />
                            </div>
                            <Button
                                type="submit"
                                label="Track now"
                                icon="pi pi-arrow-right"
                                icon-pos="right"
                                rounded
                                class="w-full !bg-primary-600 !border-primary-600 hover:!bg-primary-700 hover:!border-primary-700 !py-3.5 !font-semibold"
                            />
                        </form>

                        <div class="mt-5 pt-5 border-t border-ink-100 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 text-ink-500">
                                <i class="pi pi-shield text-primary-500"></i>
                                <span>Secure &amp; anonymous</span>
                            </div>
                            <router-link to="/track/EZ2000000002" class="font-semibold text-primary-700 hover:text-primary-800">
                                Try a demo →
                            </router-link>
                        </div>
                    </div>

                    <div class="relative mt-4 flex items-center gap-2 px-4 py-3 rounded-xl bg-white/70 backdrop-blur border border-ink-100">
                        <div class="flex -space-x-2">
                            <div v-for="(l, i) in ['DW','MS','PS','JA']" :key="i" class="w-7 h-7 rounded-full bg-primary-100 ring-2 ring-white text-[10px] font-bold text-primary-700 flex items-center justify-center">{{ l }}</div>
                        </div>
                        <span class="text-xs text-ink-600">
                            <strong class="text-ink-900">+200 ops leaders</strong> joined this month
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
