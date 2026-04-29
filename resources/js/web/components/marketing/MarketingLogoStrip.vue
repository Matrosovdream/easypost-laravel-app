<script setup lang="ts">
const carriers = [
    { name: 'USPS',   icon: 'pi-inbox' },
    { name: 'UPS',    icon: 'pi-truck' },
    { name: 'FedEx',  icon: 'pi-send' },
    { name: 'DHL',    icon: 'pi-globe' },
    { name: 'Canada Post', icon: 'pi-box' },
    { name: 'OnTrac', icon: 'pi-compass' },
];
</script>

<template>
    <section class="bg-white border-y border-ink-100">
        <div class="container mx-auto px-6 py-10">
            <p class="text-center text-xs font-semibold tracking-[0.2em] uppercase text-ink-400">
                Integrated with 100+ carriers — including
            </p>
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 items-center">
                <div
                    v-for="c in carriers"
                    :key="c.name"
                    class="flex items-center justify-center gap-2.5 h-12 rounded-xl border border-ink-100 bg-ink-50/40 text-ink-500 font-semibold hover:text-primary-700 hover:bg-white hover:border-primary-200 hover:shadow-sm transition-all"
                >
                    <i :class="`pi ${c.icon} text-primary-500`"></i>
                    <span class="text-sm">{{ c.name }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
