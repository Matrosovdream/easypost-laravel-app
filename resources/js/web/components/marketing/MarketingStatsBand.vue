<script setup lang="ts">
const stats = [
    { value: '100+',   label: 'Carriers supported', icon: 'pi-truck' },
    { value: '1.2M',   label: 'Labels printed / month', icon: 'pi-print' },
    { value: '99.95%', label: 'Uptime, 90-day rolling', icon: 'pi-bolt' },
    { value: '< 60ms', label: 'P50 rate-shop latency', icon: 'pi-stopwatch' },
];
</script>

<template>
    <section class="relative py-20 overflow-hidden bg-gradient-to-br from-ink-900 via-ink-800 to-primary-900 text-white">
        <div class="absolute inset-0 pointer-events-none opacity-60">
            <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-primary-600/30 blur-3xl"></div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-accent-500/20 blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase text-accent-400">By the numbers</p>
                <h2 class="mt-3 text-3xl lg:text-5xl font-bold tracking-tight">Built for the scale you ship at.</h2>
                <p class="mt-4 text-ink-200 text-lg">Running your ops on ShipDesk means the numbers hold up.</p>
            </div>

            <div class="mt-14 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="s in stats"
                    :key="s.label"
                    class="rounded-2xl p-6 bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors"
                >
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-accent-500/20 text-accent-300">
                        <i :class="`pi ${s.icon}`"></i>
                    </div>
                    <div class="mt-4 text-4xl font-bold tracking-tight">{{ s.value }}</div>
                    <div class="mt-1 text-sm text-ink-300">{{ s.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
